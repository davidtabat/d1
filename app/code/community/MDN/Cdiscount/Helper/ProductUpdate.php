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

class MDN_Cdiscount_Helper_ProductUpdate extends MDN_MarketPlace_Helper_ProductUpdate {

    /**
     * Get marketplace name
     *
     * @return string
     */
    public function getMp(){

        if($this->_mp === null){

             $this->_mp = Mage::registry('mp_country')->getId();

        }

        return $this->_mp;

    }

    /**
     * update products
     */
    public function update($request = null){

       $nbr = count($this->getProducts($request));

       Mage::Helper('Cdiscount/CheckResponse')->checkExport();
       
       if($nbr > 0){
           
            // before update
            $this->beforeUpdate();

            $res = Mage::Helper('Cdiscount/Package_Offers')->buildPackage($this->getProducts($request));
            $helper = Mage::Helper('Cdiscount/Services');
            $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeUpdateStockPrice);
            $url = Mage::Helper('Cdiscount/Url')->getOfferPackageUrl($res['id']);
            $helper->submitOfferPackage($url);
            
            $this->_data = $res['submitted'];
            
            // after update
            $this->afterUpdate();

       }

       return $nbr;

    }

    public function updateImageFromGrid($ids){

        throw new Exception('Not available for this marketplace');

    }

}
