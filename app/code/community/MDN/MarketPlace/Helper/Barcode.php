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
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Olivier ZIMMERMANN & Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Helper_Barcode extends Mage_Core_Helper_Abstract {

    /**
     * retrieve product barcode
     *
     * @param product $product
     * @return string
     */
    public function getBarcodeForProduct($product) {
        
        $retour = '';
        
        if(Mage::Helper('MarketPlace')->useErpBarcode()){
            
            $productId = Mage::getModel('catalog/product')->getIdBySku($product->getsku());
            $ean = mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
            $retour = $ean;
            
        }else{
            
            $barcodeAttributeCode = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_barcode_attribute();
            if ($barcodeAttributeCode == '')
                $retour = '';
            else
                $retour = $product->getData($barcodeAttributeCode);
            
        }

        return $retour;
    }

    /**
     * Add barcode to collection
     * 
     * @param collection $collection
     * @return collection 
     */
    public function addBarcodeAttributeToProductCollection($collection) {
        
        if(!Mage::Helper('MarketPlace')->useErpBarcode()){
            
            $barcodeAttributeCode = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_barcode_attribute();
            if ($barcodeAttributeCode)
                $collection->addAttributeToSelect($barcodeAttributeCode);
            
        }
            
        return $collection;
    }

    /**
     * Retrieve barcode attribute name
     * 
     * @return string 
     */
    public function getCollectionBarcodeIndex() {

        $retour = '';
        
        if(Mage::Helper('MarketPlace')->useErpBarcode() && Mage::getStoreConfig('advancedstock/barcode/barcode_attribute') == ''){
            $retour = 'ppb_barcode';
        }else{
            $retour = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_barcode_attribute();
        }
        
        return $retour;
    }

}
