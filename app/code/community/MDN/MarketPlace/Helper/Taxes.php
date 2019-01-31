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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Helper_Taxes extends Mage_Core_Helper_Abstract {
    
    /**
     * Get taxe rate
     * 
     * @return float $taxRate
     * @throws Exception 
     */
    public function getTaxRate(){
        
        $country = Mage::registry('mp_country');
        $taxRate = $country->getParam('taxes');
        
        if(!is_numeric($taxRate)){
            $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
            throw new Exception($this->__('Taxes rate is not defined in <a href="'.Mage::Helper('adminhtml')->getUrl('MarketPlace/Configuration/index', array('type' => 'country', 'mp' => $account->getmpa_mp(), 'country' => $country->getId())).'">'.$account->getmpa_name().' - '.$country->getmpac_country_code()).'</a>');
        }
        
        return $taxRate;
        
    }
    
}
