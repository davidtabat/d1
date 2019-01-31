<?php

class IWD_OrderManager_Model_Observer_Sales
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function initQuoteAddress(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('iwd_ordermanager')->isCustomCreationProcess()) {
            return;
        }

        /**
         * @var $sessionQuote Mage_Adminhtml_Model_Session_Quote
         */
        $sessionQuote = $observer->getEvent()->getData("session_quote");
        $quote = $sessionQuote->getQuote();
        $customer = $sessionQuote->getCustomer(true);
        $customerId = $customer->getId();

        if ($customerId) {
            $quoteAddress = $quote->getShippingAddress();
            if($customerId != $quoteAddress->getCustomerId()) {
                $defaultAddress = $customer->getDefaultShippingAddress();
                $this->updateQuoteAddress($quoteAddress, $defaultAddress);
            }

            $quoteAddress = $quote->getBillingAddress();
            if($customerId != $quoteAddress->getCustomerId()) {
                $defaultAddress = $customer->getDefaultBillingAddress();
                $this->updateQuoteAddress($quoteAddress, $defaultAddress);
            }

            $quote->setCustomer($customer)->save();
        }
    }

    protected function updateQuoteAddress($quoteAddress, $defaultAddress)
    {
        if ($quoteAddress && $quoteAddress->getId() && $defaultAddress && $defaultAddress->getId()) {
            $customerId = $quoteAddress->getCustomerId();
            $customerAddressId = $quoteAddress->getCustomerAddressId();
            if(empty($customerAddressId) || $customerAddressId != $defaultAddress->getId()){
                $quoteAddress->setCustomerAddressId($defaultAddress->getId())->setCustomerId($customerId);
                $quoteAddress->addData($defaultAddress->getData());
                $quoteAddress->save();
            }
        }
    }

    public function beforeOrderCreateLoadBlock()
    {
        if (!Mage::helper('iwd_ordermanager')->isCustomCreationProcess()) {
            return;
        }

        $this->recollectShipping();
        $this->selectDefaultShippingMethod();
    }

    protected function recollectShipping()
    {
        /**
         * @var $quote Mage_Sales_Model_Quote
         */
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $request = Mage::app()->getRequest();

        $request->setPost('reset_shipping', 0);
        $quote->getShippingAddress()->setCollectShippingRates(true)->save();
    }

    protected function selectDefaultShippingMethod()
    {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $request = Mage::app()->getRequest();

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $order = Mage::app()->getRequest()->getParam('order', array());

        if (empty($shippingMethod) && !isset($order['shipping_method'])) {
            $defaultShippingMethod = Mage::getStoreConfig('iwd_ordermanager/crate_process/default_shipping');
            $order = $request->getPost('order');
            $order['shipping_method'] = $defaultShippingMethod;
            $request->setPost('order', $order);
        }
    }
}