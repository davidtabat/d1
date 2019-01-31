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

class MDN_MarketPlace_Model_Accounts extends Mage_Core_Model_Abstract {    
    
    const kParamMWSMerchantID = 'mws_merchantid';
    const kParamMWSMarketPlaceID = 'mws_marketplaceid';
    const kParamMWSAccessKeyID = 'mws_accesskeyid';
    const kParamMWSSecretKey = 'mws_secretkey';
    const kParamIsAdultProduct = 'isadultproduct';
    const kParamSoumissionAutoMarginOperator = 'so_marginfilteroperator';
    const kParamSoumissionAutoMarginValue = 'so_marginfilter';
    const kParamSoumissionAutoAttrSetOperator = 'so_attrsetoperator';
    const kParamSoumissionAutoAttrSetValues = 'so_attrset';
    const kParamSoumissionAutoVisibilityOperator = 'so_visibilityoperator';
    const kParamSoumissionAutoVisibilityValues = 'so_visibility';
    const kParamSoumissionAutoBrandOperator = 'so_brandoperator';
    const kParamSoumissionAutoBrandValues = 'so_brand';
    const kParamSoumissionAutoActive = 'so_active';
    const kParamBarcodeAttr = 'barcode_attr';
    const kParamBarcodeType = 'barcode_type';
    const kParamMarginWarning = 'margin_warning';
    const kName = 'mpa_name';
    const kMarketPlace = 'mpa_mp';
    
    /**
     * Construtor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Accounts');
    }
    
    /**
     * Before delete
     * 
     * @return type
     * 
     */
    public function _beforeDelete(){
        
        $collection = mage::getModel('MarketPlace/Countries')->getCollection()
                        ->addFieldToFilter('mpac_account_id', $this->getId());
        
        foreach($collection as $item){
            
            $item->delete();
            
        }
        
        return parent::_beforeDelete();
        
        
    }
    
    /**
     * Before save
     * 
     * @return type 
     */
    public function _beforeSave(){                       
        
        $this->setmpa_params(serialize($this->getmpa_params()));
        
        return parent::_beforeSave();
        
    }
    
    /**
     * After save
     * 
     * @return type 
     */
    public function _afterSave(){
        
        Mage::dispatchEvent('marketplace_account_after_save', array('account'=>$this));
        
        return parent::_afterSave();
        
    }
    
    /**
     * Get country
     * 
     * @param int $accountId
     * @param string $countryCode
     * @return \Varien_Object 
     */
    public function getCountry($accountId, $countryCode){
        
        $retour = new Varien_Object();
        $retour->setmpa_id($accountId);
        
        $account = $this->load($accountId);
        
        $params = unserialize($account->getmpa_params());       
        
        foreach($params['marketplaces'] as $country){           
                
            if($country['countryCode'] == $countryCode){

                foreach($country as $k => $v){
                
                    if(is_array($v)){

                        $object = new Varien_Object();
                        foreach($v as $config => $value){
                            $method = 'set'.$config;
                            $object->$method($value);
                        }

                    }else{

                        $method = 'set'.$k;
                        $retour->$method($v);

                    }
                }

            }  
            
        }
        
        return $retour;
        
    }
    
    /**
     * get params
     * 
     * @param string $label
     * @return mixed 
     */
    public function getParam($label){
        
        $retour = '';
        
        $params = unserialize($this->getmpa_params());
        
        if(is_array($params)){
            if(array_key_exists($label, $params))
                $retour = $params[$label];        
        }
        
        return $retour;        
        
    }
    
    /**
     * Get actives countries object (for cron)
     * 
     * @param string $mp
     * @return array $retour 
     */
    public function getActivesCountriesObject($mp){
        
        $retour = array();
        
        $collection = $this->getCollection()->addFieldToFilter('mpa_mp', strtolower($mp));
        
        foreach($collection as $item){
            
            if($item->getParam('is_active') == 1){                
                
                $retour[$item->getmpa_id()] = array();
                
                $countries = Mage::getModel('MarketPlace/Countries')->getCollection()->addFieldToFilter('mpac_account_id', $item->getmpa_id());
                
                foreach($countries as $country){
                    
                    if($country->getParam('active') == 1){
                        
                        $retour[$item->getmpa_id()][$country->getId()] = $country;
                        
                    }
                    
                }
                
            }
            
        }
        
        return $retour;
        
    }
    
    /**
     * Get active countries
     * 
     * @param string $mp
     * @return array $retour 
     */
    public function getActiveCountries($mp){
        
        $retour = array();
        
        $collection = $this->getCollection()->addFieldToFilter('mpa_mp', strtolower($mp));
        
        foreach($collection as $item){
            
            if($item->getParam('is_active') == 1){
                
                $retour[$item->getmpa_id()] = array(
                    'label' => $item->getmpa_name(),
                    'countries' => array()
                );
                
                $countries = Mage::getModel('MarketPlace/Countries')->getCollection()->addFieldToFilter('mpac_account_id', $item->getmpa_id());
                
                foreach($countries as $country){
                    
                    if($country->getParam('active') == 1){
                        
                        $retour[$item->getmpa_id()]['countries'][$country->getmpac_id()] = $country->getParam('name');
                        
                    }
                    
                }
                
            }
            
        }        
        
        return $retour;
        
    }
    
    /**
     * get country config
     * 
     * @param string $label
     * @return mixed 
     */
    public function getCountryConfig($label){
        
        $country = Mage::getModel('MarketPlace/Countries')->getCurrentCountry();
        
        $account = $this->load($country->getmpac_account_id());
        
        return $account->getParam($label);        
        
    }
    
}
