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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Helper_Data extends MDN_MarketPlace_Helper_Abstract {

    /**
     * Log into magento logs...
     *
     * @param $msg
     */
    public function magentoLog($msg)
    {
        mage::log($msg, null, 'cdiscount.log');
    }

    /**
     * Get category form
     * 
     * @param string $name
     * @param string $value
     * @return string $html 
     */
    public function getCategoryForm($name, $value) {

        $html = '<div>'.Mage::helper('Cdiscount/Category')->getCatName($value).'</div>';
        $html .= '<input type="text" name="'.$name.'" id="cat_ref" value="'.$value.'"/>';
        $html .= '<div>'.Mage::helper('Cdiscount/Category')->getUniversAsCombo().'</div>';

        return $html;

    }
    
    /**
     * Is marketplace need category association
     *
     * @return boolean false
     */
    public function needCategoryAssociation(){
        return true;
    }

    /**
     * Is marketplace is displayed in sales order summary
     *
     * @return boolean
     */
    public function isDisplayedInSalesOrderSummary() {
        return true;
    }

    /**
     * Get markatplace name
     *
     * @return string
     */
    public function getMarketPlaceName(){
        return 'cdiscount';
    }

    /**
     * Get formated product list
     */
    public function getProductFile(){
         // TODO : useless ?
    }

    /**
     * Import orders
     */
    public function importMarketPlaceOrders($orders){
        return Mage::helper('Cdiscount/Orders')->importMarketPlaceOrders($orders);
    }

    /**
     * Get orders to import
     */
    public function getMarketPlaceOrders(){
        return Mage::helper('Cdiscount/Orders')->getMarketPlaceOrders();
    }

    /**
     * Update stocks and prices
     */
    public function updateStocksAndPrices(){
        return Mage::helper('Cdiscount/ProductUpdate')->update();
    }

    /**
     * Send tracking
     */
    public function sendTracking(){
        Mage::Helper('Cdiscount/Tracking')->sendTracking();
    }

    /**
     * Check product creation
     */
    public function checkProductCreation(){
        return Mage::helper('Cdiscount/ProductCreation')->importCreatedProducts();
    }

    /**
     * Is marketplace allow manual update
     *
     * @return boolean
     */
    public function allowManualUpdate(){
        return true;
    }

    /**
     * Is marketplace allow product creation ?
     *
     * @return boolean
     */
    public function allowProductCreation(){
        return (Mage::registry('mp_country')->getParam('enable_product_creation') == 1);
    }
    
    /**
     * Get product URL
     *  
     * @return string $retour
     */
    public function getProductUrl($ref, $country){

        $urlRef = $ref;
        $t = explode('-', $ref);
        if (count($t) > 1)
            $urlRef = $t[0];

        return '<a href="http://www.cdiscount.com/mp-1-'.$urlRef.'.html" target="_blanck">'.$ref.'</a>';
        
    }

    public function getDefaultCountry()
    {
        $countries = Mage::getModel('MarketPlace/Accounts')->getActivesCountriesObject(Mage::Helper('Cdiscount')->getMarketPlaceName());
        foreach($countries as $items)
        {
            foreach($items as $item)
                return $item;
        }
    }

}
