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
abstract class MDN_MarketPlace_Helper_Abstract extends Mage_Core_Helper_Abstract {

    /**
     * Get export path name
     *
     * @return string
     */
    public function getExportPath() {
        return Mage::app()->getConfig()->getTempVarDir() . '/export/marketplace/' . $this->getMarketPlaceName() . '/';
    }

    /**
     * Get formated product list
     * 
     * @todo : useless ?
     */
    abstract function getProductFile();

    /**
     * Each marketplace must give his name
     */
    abstract function getMarketPlaceName();

    /**
     * Import orders
     * 
     * @param array $orders
     */
    abstract function importMarketPlaceOrders($orders);

    /**
     * Get orders to import
     */
    abstract function getMarketPlaceOrders();

    /**
     * Update stocks and prices
     */
    abstract function updateStocksAndPrices();

    /**
     * Send tracking
     */
    abstract function sendTracking();

    /**
     * Check product creation
     */
    abstract function checkProductCreation();

    /**
     * Define if the market place need custom association with categories
     */
    public function needCategoryAssociation() {
        return false;
    }

    /**
     * Return form to associate categories
     * 
     * @param string $name
     * @param string $value
     */
    public function getCategoryForm($name, $value) {
        throw new Exception('Not implement !');
    }

    /**
     * Return form to associate languages
     *
     * @param string $name
     * @param string $value
     */
    public function getInternationalizationForm($name, $value){
        throw new Exception('Not implemented yet !');
    }

    /**
     * Is marketplace orders appears in orders view ?
     *
     * @return boolean
     */
    public function isDisplayedInSalesOrderSummary(){
        return true;
    }

    /**
     * Is marketplace allow order importation ?
     *
     * @return boolean
     */
    public function allowImportOrders(){
        return true;
    }

    /**
     * Is marketplace allow stocks and prices update ?
     *
     * @return boolean
     */
    public function allowUpdateStocksAndPrices(){
        return true;
    }

    /**
     * Is marketplace allow trackings ?
     *
     * @return boolean
     */
    public function allowTracking(){
        return true;
    }

    /**
     * Is marketplace allow product creation ?
     *
     * @return boolean
     */
    public function allowProductCreation(){
        return true;
    }

    /**
     * Is marketplace has specifics errors ?
     *
     * @return boolean
     */
    public function hasSpecificErrors(){
        return false;
    }

    /**
     * Is marketplace allow free shipping
     *
     * @return boolean
     */
    public function allowFreeShipping(){
        return false;
    }

    /**
     * Is marketplace allow internationalization
     *
     * @return boolean false
     */
    public function allowInternationalization(){
        return false;
    }

    /**
     * Is marketplace allow configurable products
     *
     * @return boolean false
     */
    public function allowConfigurableProduct(){
        return false;
    }

    /**
     * Is marketplace allow manual update
     *
     * @return boolean
     */
    public function allowManualUpdate(){
        return false;
    }
    
    /**
     * Is marketplace allow request feed submission from grid
     * 
     * @return boolean 
     */
    public function allowRequestFeedSubmissionResultFromGrid(){
        return false;
    }
    
    /**
     * Is marketplace allow revise products
     * 
     * @return boolean 
     */
    public function allowReviseProducts(){
        return false;
    }

}
