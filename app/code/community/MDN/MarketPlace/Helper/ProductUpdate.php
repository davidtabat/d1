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
abstract class MDN_MarketPlace_Helper_ProductUpdate extends Mage_Core_Helper_Abstract {

    /* @var Mage_Catalog_Model_Product */
    protected $_lastSentProduct = null;
    /* @var collection */
    protected $_products = null;
    /* @var string */
    protected $_mp = null;
    /* @var array */
    protected $_data = array();

    /**
     * update stock and price
     */
    abstract public function update($request = null);

    /**
     * Get marketplace name
     */
    abstract function getMp();

    /**
     * Update mp_last_update field for each updated products
     *
     * @return int 0
     */
    public function updateLastUpdatedDate() {

        //$date = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()); don't add timestamp, it can be gretter than updated_at field
        $date = date('Y-m-d H:i:s');

        foreach ($this->getProducts() as $product) {

            if(array_key_exists($product->getsku(), $this->_data)){
            
                $obj = Mage::getModel('MarketPlace/Data')->load($product->getmp_id());
                $obj->setmp_last_update($date)
                        ->setmp_last_stock_sent($this->_data[$product->getsku()]['stock'])
                        ->setmp_last_delay_sent($this->_data[$product->getsku()]['delay'])
                        ->setmp_last_price_sent($this->_data[$product->getsku()]['price'])
                        ->save();
            }
        }

        return 0;
    }
    
    /**
     * Before update 
     */
    public function beforeUpdate(){
        // init last data sent
        $this->_data = array();
    }
    
    /**
     * After update 
     */
    public function afterUpdate(){
        // update last updated date
        $this->updateLastUpdatedDate();
    }

    /**
     *
     * @return collection $products
     */
    public function getProducts($request = null, $init = false) {
        
        if ($this->_products === null || $init === true) {

            if ($request !== null) {
                // Grid mode
                $this->_products = Mage::Helper('MarketPlace/Product')->getProductsFromRequest($this->getMp(), $request);
            } else {
                // CRON mode
                $this->_products = Mage::Helper('MarketPlace/Product')->getProductsToExport($this->getMp());
            }
        }

        return $this->_products;
    }

    /**
     * Retrieve products to update on marketplace
     * - simple products
     * - visibility : search, catalog, nowhere, catalog/search
     * - mp_reference not null in market_place_data table
     *
     * @return collection
     */
    public function getProductsToExport() {

        return mage::helper('MarketPlace/Product')->getProductsToExport($this->getMp());
    }

    /**
     * Get export path name
     *
     * @return string
     *
     */
    public function getExportPath() {
        return Mage::app()->getConfig()->getTempVarDir() . '/export/marketplace/' . $this->getMp() . '/';
    }

    /**
     * Reset update for current MP
     * 
     * @return type 
     */
    public function reset() {

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sql = 'UPDATE ' . Mage::getConfig()->getTablePrefix() . 'market_place_data
                SET mp_last_update = "1900-01-01"
                WHERE mp_marketplace_id = "' . strtolower($this->getMp()) . '"';

        return $write->query($sql);
    }

}
