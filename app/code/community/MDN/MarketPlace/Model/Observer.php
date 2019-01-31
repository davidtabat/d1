<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2012 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_Observer {

    /**
     * Get marketplaces orders
     *
     */
    public function getOrders() {

        Mage::helper('MarketPlace/Main')->cronImportOrders();
        Mage::helper('MarketPlace/Main')->cronSendTracking();

   }

    public function pruneFeeds()
    {
        $count = Mage::helper('MarketPlace/Feed')->prune();
    }

    /**
     * Update marketplaces stocks
     *
     */
    public function updateStocks() {

        Mage::helper('MarketPlace/Main')->cronUpdateStocksAndPrices();

    }

    /**
     * Check Product creation
     *
     */
    public function checkProductCreation(){

        Mage::helper('MarketPlace/Main')->cronCheckProductCreation();

    }
    
    /**
     * Auto submit 
     */
    public function autoSubmit(){
        
        Mage::Helper('MarketPlace/Main')->cronAutoSubmit();
        
    }
    
    /**
     * Update updated at product field after order importation
     * 
     * @param Varien_Observer $observer 
     */
    public function UpdateUpdatedAtProductFieldAfterOrderImportation(Varien_Event_Observer $observer){

        $sql = '';
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $obj = $observer->getdata_object();
        $prefix = Mage::getConfig()->getTablePrefix();
        
        $productId = $obj->getproduct_id();
        $date = date('Y-m-d H:i:s');
        
        $sql = 'UPDATE '.$prefix.'catalog_product_entity
                SET updated_at = "'.$date.'"
                WHERE entity_id = '.$productId;
        
        $write->query($sql);                        
        
    }

}
