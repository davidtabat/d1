<?php

/*
 * This method override the original method CaptureAction() in Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php
 * allowing invoices to be marked paid offline
 */
require_once("Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php");

class Jay_Capture_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController {

    public function captureAction() {
        if ($invoice = $this->_initInvoice()) {
            try {
                //OFFLINE CAPTURE
                if ((strpos(Mage::app()->getRequest()->getServer('HTTP_REFERER'), '/sales_order_invoice/view/invoice_id/') !== false) ||
                    (strpos(Mage::app()->getRequest()->getServer('HTTP_REFERER'), '/sales_invoice/view/invoice_id/') !== false)   ) {
                    try {
                        if (!is_null($invoice)) {
                            $order = $invoice->getOrder();
                            if (!$order->canCreditmemo()) {
                                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                                $invoice->setCanVoidFlag(false);
                                $invoice->pay(); //->save();
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                        ->addObject($invoice)
                                        ->addObject($invoice->getOrder());
                                $transactionSave->save();
                            }
                            $this->_getSession()->addSuccess($this->__('The invoice has been captured Offline.'));
                        } else {
                            $this->_getSession()->addError($this->__('Invoice IS NULL capturing error.'));
                        }
                    } catch (Exception $e) {
                        $this->_getSession()->addError($this->__('Invoice capturing error.'));
                        echo $e->getMessage();
                    }
                } else {
                    //NORMAL
                    $invoice->capture();
                }
                $this->_saveInvoice($invoice);
                $this->_getSession()->addSuccess($this->__('The invoice has been captured.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Invoice capturing error.'));
            }

            $this->_redirect('*/*/view', array('invoice_id' => $invoice->getId()));
        } else {
            $this->_forward('noRoute');
        }
    }

}
