<?php

/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */
class Mageshops_Rakuten_Model_Observer
{

    /**
     * Cron job method for synchronizing data to Rakuten marketplace
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function synchronizeAll(Mage_Cron_Model_Schedule $schedule)
    {
        $helper = Mage::helper('rakuten');
        if ($helper->enableCron()) {
            $helper->batchSynchronization();
        }
    }

    /**
     * Cron job method for synchronizing stock and price data to Rakuten marketplace
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function synchronizeStockPrice(Mage_Cron_Model_Schedule $schedule)
    {
        $helper = Mage::helper('rakuten');
        if ($helper->getStockPriceCron()) {

            if ($helper->isLocked()) {
                $helper->syncLog($helper->__('Other synchronization process is running.'));
                return;
            }

            $helper->lockSync();

            try {
                Mage::getModel('rakuten/product')->getAllRakutenProducts()->syncAllStockPriceToRakuten();
                $helper->setState($helper->__('Synchronization finished successfully.'), 1);
            } catch (Exception $e) {
                $helper->syncExceptionLog($e);
                $helper->setState($helper->__('Error occurred during synchronization: %s', $e->getMessage()), 0);
            }

            $helper->unlockSync();
        }
    }

    public function setUnvailableOnZeroStock(Varien_Event_Observer $observer)
    {
        $product = $observer->getData('product');
        if ($product->getRakutenStockPolicy() === null) {
            $product->setRakutenStockPolicy(Mage::helper('rakuten')->zeroStock());
        }
    }

    public function setRakutenShipping(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();
        $rakutenShipping = $quote->getShippingAddress()->getShippingAmount();
        $store = Mage::app()->getStore($quote->getStoreId());
        $carriers = Mage::getStoreConfig('carriers', $store);
        foreach ($carriers as $carrierCode => $carrierConfig) {
            if ($carrierCode == 'nnrakuten') {
                $store->setConfig("carriers/{$carrierCode}/price", $rakutenShipping);
            }
        }
    }

    public function requestCleanup(Mage_Cron_Model_Schedule $schedule)
    {
        // Leave only unfinished requests after one day
        $requests = Mage::getModel('rakuten/rakuten_request')->getCollection()
            ->addFieldToFilter('finished', array('lt' => time() - 24 * 60 * 60))
            ->addFieldToFilter('status', array('eq' => Mageshops_Rakuten_Model_Rakuten_Request::STATUS_FINISHED));

        foreach ($requests as $request) {
            $request->delete();
        }

        // Clean all requests after fortnight
        $requests = Mage::getModel('rakuten/rakuten_request')->getCollection()
            ->addFieldToFilter('finished', array('lt' => time() - 14 * 24 * 60 * 60));

        foreach ($requests as $request) {
            $request->delete();
        }
    }

    /**
     * Cron job method for synchronizing orders data from Rakuten marketplace
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function synchronizeOrders(Mage_Cron_Model_Schedule $schedule)
    {
        $helper = Mage::helper('rakuten');
        if ($helper->enableCron() && $helper->enableOrderSync()) {
            $helper->syncLog($this->__('Orders synchronization started.'));
            try {
                Mage::getModel('rakuten/order')->syncRakutenOrders();
                $helper->syncLog($this->__('Orders were successfully fetched from your Rakuten account.'));
            } catch (Exception $e) {
                $helper->syncExceptionLog($e);
            }
        }
    }

}
