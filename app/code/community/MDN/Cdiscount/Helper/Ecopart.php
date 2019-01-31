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
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_Cdiscount
 * @version 2.0
 */
class MDN_Cdiscount_Helper_Ecopart extends Mage_Core_Helper_Abstract {
    
    /**
     * Get eco part value
     * 
     * @param Varien_Object $product
     * @return float $retour 
     */
    public function getValueForProduct($product){
        
        $retour = 0;
        
        $country = Mage::registry('mp_country');
        
        $default = $country->getParam('eco_part_default');
        
        if($default != ''){
            
            $retour = $default;
            
        }else{
            
            if(!is_array($product) && $country->getParam('eco_part_attribute') != '' && $product->getData($country->getParam('eco_part_attribute')) != '')
                $retour = $product->getData($country->getParam('eco_part_attribute'));
            
        }
        
        return $retour;
        
    }
    
    /**
     * Get DEA value 
     * 
     * @param Varien_Object $product
     * @return float
     */
    public function getDeaValueForProduct($product){
        
        $retour = 0;
        
        $country = Mage::registry('mp_country');        
        $default = $country->getParam('dea_default');
        
        if($default != ''){
            
            $retour = $default;
            
        }else{
            
            if(!is_array($product) && $country->getParam('dea_attribute') != '' && $product->getData($country->getParam('dea_attribute')) != '')
                $retour = $product->getData($country->getParam('dea_attribute'));
            
        }
        
        return $retour;
        
    }
    
}
