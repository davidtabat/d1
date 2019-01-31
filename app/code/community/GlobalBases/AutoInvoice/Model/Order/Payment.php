<?php
/**
 * GlobalBases_AutoInvoice_Model_Order_Payment
 *
 * @category    GlobalBases
 * @package     GlobalBases_AutoInvoice
 * @copyright   Copyright (c) 2013 GlobalBases.com GmbH (http://www.globalbases.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     	GlobalBases.com GmbH <http://www.globalbases.com>
 */ 

class GlobalBases_AutoInvoice_Model_Order_Payment extends Mage_Sales_Model_Order_Payment 

{
    /**
     * Capture the payment online
     * Requires an invoice. If there is no invoice specified, will automatically prepare an invoice for order
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with registerCaptureNotification()
     *
     * @return Mage_Sales_Model_Order_Payment
     * @throws Mage_Core_Exception
     */
    public function capture($invoice)
    {
		$autocreate = Mage::getStoreConfig('invoiceconfig/autocreate/active');
		
        $order = $this->getOrder();
		if( $autocreate ) {
			if (is_null($invoice)) {
				$invoice = $this->_invoice();
				$this->setCreatedInvoice($invoice);
				return $this; // @see Mage_Sales_Model_Order_Invoice::capture()
			}
			$amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
			
			// prepare parent transaction and its amount
			$paidWorkaround = 0;
			if (!$invoice->wasPayCalled()) {
				$paidWorkaround = (float)$amountToCapture;
			}
			$this->_isCaptureFinal($paidWorkaround);
		} else {
			$amountToCapture = $this->_formatAmount($order->getBaseGrandTotal());
			$this->_isCaptureFinal(0);
		}

        $this->_generateTransactionId(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

		if( $autocreate ) {
	        Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));
		} else {
	        Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => NULL));
		}

        /**
         * Fetch an update about existing transaction. It can determine whether the transaction can be paid
         * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
         */
		if( $autocreate ) {
			if ($invoice->getTransactionId()) {
				$this->getMethodInstance()
					->setStore($order->getStoreId())
					->fetchTransactionInfo($this, $invoice->getTransactionId());
			}
			$status = true;
			if (!$invoice->getIsPaid() && !$this->getIsTransactionPending()) {
				// attempt to capture: this can trigger "is_transaction_pending"
				$this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);

				$transaction = $this->_addTransaction(
					Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
					$invoice,
					true
				);

				if ($this->getIsTransactionPending()) {
					$message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amountToCapture));
					if( $autocreate ) {
						$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
					} else {
						$state = Mage_Sales_Model_Order::STATE_NEW;
					}
					if ($this->getIsFraudDetected()) {
						$status = Mage_Sales_Model_Order::STATUS_FRAUD;
					}
					$invoice->setIsPaid(false);
				} else { // normal online capture: invoice is marked as "paid"
					$message = Mage::helper('sales')->__('Captured amount of %s online.', $this->_formatPrice($amountToCapture));
					if( $autocreate ) {
						$state = Mage_Sales_Model_Order::STATE_PROCESSING;
					} else {
						$state = Mage_Sales_Model_Order::STATE_NEW;
					}
					$invoice->setIsPaid(true);
					$this->_updateTotals(array('base_amount_paid_online' => $amountToCapture));
				}
				if ($order->isNominal()) {
					$message = $this->_prependMessage(Mage::helper('sales')->__('Nominal order registered.'));
				} else {
					$message = $this->_prependMessage($message);
					$message = $this->_appendTransactionToMessage($transaction, $message);
				}
				$order->setState($state, $status, $message);
				$this->getMethodInstance()->processInvoice($invoice, $this); // should be deprecated
				return $this;
			}
		} else {
			if ($order->getTransactionId()) {
				$this->getMethodInstance()
					->setStore($order->getStoreId())
					->fetchTransactionInfo($this, $order->getTransactionId());
			}
			$status = true;
			if (!$this->getIsTransactionPending()) {
				// attempt to capture: this can trigger "is_transaction_pending"
				$this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);

				$transaction = $this->_addTransaction(
					Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
					$order,
					true
				);

				if ($this->getIsTransactionPending()) {
					$message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amountToCapture));
					if( $autocreate ) {
						$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
					} else {
						$state = Mage_Sales_Model_Order::STATE_NEW;
					}
					if ($this->getIsFraudDetected()) {
						$status = Mage_Sales_Model_Order::STATUS_FRAUD;
					}
					//$invoice->setIsPaid(false);
				} else { // normal online capture: invoice is marked as "paid"
					$message = Mage::helper('sales')->__('Captured amount of %s online.', $this->_formatPrice($amountToCapture));
					$state = Mage_Sales_Model_Order::STATE_NEW;
					//$invoice->setIsPaid(true);
					//$this->_updateTotals(array('base_amount_paid_online' => $amountToCapture));
				}
				if ($order->isNominal()) {
					$message = $this->_prependMessage(Mage::helper('sales')->__('Nominal order registered.'));
				} else {
					$message = $this->_prependMessage($message);
					$message = $this->_appendTransactionToMessage($transaction, $message);
				}
				$order->setState($state, $status, $message);
				//$this->getMethodInstance()->processInvoice($invoice, $this); // should be deprecated
				return $this;
			}
			
		}
		if( $autocreate ) {
	        Mage::throwException(
				Mage::helper('sales')->__('The transaction "%s" cannot be captured yet.', $invoice->getTransactionId())
	        );
		} else {
	        Mage::throwException(
				Mage::helper('sales')->__('The transaction "%s" cannot be captured yet.', $order->getTransactionId())
	        );
				
		}
    }

	/**
     * Capture the payment online
     * Requires an invoice. If there is no invoice specified, will automatically prepare an invoice for order
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with registerCaptureNotification()
     *
     * @return Mage_Sales_Model_Order_Payment
     * @throws Mage_Core_Exception
     */
	
    public function registerCaptureNotification($amount, $skipFraudDetection = false)
    {
		$autocreate = Mage::getStoreConfig('invoiceconfig/autocreate/active');  // Configvalue to enable generating invoice
        $ordermail = Mage::getStoreConfig('invoiceconfig/autocreate/ordermail');

        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        $order   = $this->getOrder();
        $amount  = (float)$amount;
        $invoice = $this->_getInvoiceForTransactionId($this->getTransactionId());

        // register new capture
        if (!$invoice) {
            if ($this->_isCaptureFinal($amount)) {
				if( $autocreate ) { // Enable generating invoice
					$invoice = $order->prepareInvoice()->register();
					$order->addRelatedObject($invoice);
					$this->setCreatedInvoice($invoice);
				}
            } else {
                if (!$skipFraudDetection) {
					$this->setIsFraudDetected(true);
				}
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
            }
        }

        $status = true;
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
			if( $autocreate ) {
				$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;  // if invoice is autogenerated
			} else {
				$state = Mage_Sales_Model_Order::STATE_NEW;  // if invoice is not autogenerated
			}
            if ($this->getIsFraudDetected()) {
				$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                $message = Mage::helper('sales')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            $message = Mage::helper('sales')->__('Registered notification about captured amount of %s.', $this->_formatPrice($amount));
			if( $autocreate ) {
				$state = Mage_Sales_Model_Order::STATE_PROCESSING;  // if invoice is autogenerated
			} else {
				$state = Mage_Sales_Model_Order::STATE_NEW;  // if invoice is not autogenerated
			}
            if ($this->getIsFraudDetected()) {
                $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                $message = Mage::helper('sales')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
            // register capture for an existing invoice
            if ($invoice && Mage_Sales_Model_Order_Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->pay();
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
                $order->addRelatedObject($invoice);
            }
        }

        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);

		if( !$autocreate && !$this->getIsFraudDetected() ) {
			if( $ordermail ) {
				try {
                    $order->sendNewOrderEmail();
					$historyItem = Mage::getResourceModel('sales/order_status_history_collection')
						->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
					if ($historyItem) {
						$historyItem->setIsCustomerNotified(1);
						$historyItem->save();
					}
                } catch (Exception $e) {
                    Mage::logException($e);
                }
			}
		}	

        return $this;
    }
	
}