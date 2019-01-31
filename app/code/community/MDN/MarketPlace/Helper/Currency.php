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
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Helper_Currency extends Mage_Core_Helper_Abstract {
    
    /**
     * Get currency for current country
     * 
     * @return string $currency
     * @throws Exception 
     */
    public function getCurrencyForCurrentCountry(){
        
        $currency = '';
        $country = Mage::registry('mp_country');
        
        if($country instanceof MDN_MarketPlace_Model_Countries){
            
            switch($country->getmpac_country_code()){
                
                case 'JP':
                    $currency = 'JPY';
                    break;
                case 'US':
                    $currency = 'USD';
                    break;
                case 'CI':
                    $currency = 'CNY';
                    break;
                case 'CA':
                    $currency = 'CAD';
                    break;
                case 'GB':
                    $currency = 'GBP';
                    break;
                default:
                    $currency = 'EUR';
                    break;
                
            }
            
        }else{
            throw new Exception('Current country is not defined in '.__METHOD__);
        }
        
        if($currency == '')
            throw new Exception('Unable to retrieve currency in '.__METHOD__);
        
        return $currency;
        
    }
    
}
