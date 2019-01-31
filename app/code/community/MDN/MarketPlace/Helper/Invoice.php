<?php

/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Helper_Invoice extends Mage_Core_Helper_Abstract {

    /**
     * Create invoice for order
     *
     * @param Mage_Sales_Model_Order $order
     * @return int
     */
    public function createInvoice($order) {
        try {

            //on cree la facture
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);

            //parcourt les �l�ments de la commande
            foreach ($order->getAllItems() as $orderItem) {
                //ajout au invoice
                $InvoiceItem = $convertor->itemToInvoiceItem($orderItem);
                $InvoiceItem->setQty($orderItem->getqty_ordered());
                $invoice->addItem($InvoiceItem);
            }

            //sauvegarde la facture
            $invoice->collectTotals();
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();

            $invoice->save();

            //validate payment
            $payment = Mage::getModel('sales/order_payment');
            $payment_method = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_default_payment_method();
            if ($payment_method == "") {
                throw new Exception($this->__('Payment method attribute not set'), 15);
            }
            $payment->setMethod($payment_method);
            $payment->setOrder($order);
            $payment->pay($invoice);
            $payment->save();

            return 1;
        } catch (Exception $ex) {
            throw new Exception('Error while creating Invoice for Order ' . $order->getincrement_id() . ': ' . $ex->getMessage() . ' - ' . $ex->getTraceAsString());
        }
    }
    
}
