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
class MDN_MarketPlace_Model_Countries extends Mage_Core_Model_Abstract {
    
    /* @var MDN_MarketPlace_Model_Countries */
    protected $_currentCountry = null;
    
    /**
     * Construtor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Countries');
    }
    
    /**
     * Before save
     * 
     * @return type 
     */
    public function _beforeSave(){
        
        $this->setmpac_params(serialize($this->getmpac_params()));
        
        return parent::_beforeSave();
        
    }
    
    /**
     * get param
     * 
     * @param string $label
     * @return mixed $retour 
     */
    public function getParam($label){
        
        $retour = '';
        
        $params = unserialize($this->getmpac_params());
        
        if(is_array($params) && array_key_exists($label, $params))
                $retour = $params[$label];        
        
        return $retour;        
        
    }
    
    /**
     * get current country
     * 
     * @param int $countryId
     * @return MDN_MarketPlace_Model_Countries 
     */
    public function getCurrentCountry($countryId = null, $mp = ''){
        
        if(!$this->_currentCountry){
        
            $item = $this->load($countryId);

            if(!$item->getmpac_id()){

                $activeCountries = $this->getActiveCountries(null, $mp);                                
                ksort($activeCountries);
                $keys = array_keys($activeCountries);

                if(count($activeCountries) > 0)
                    $this->_currentCountry = $activeCountries[$keys[0]];

            }else{
                $this->_currentCountry = $item;
            }
        }        
        
        return $this->_currentCountry;
        
        
    }
    
    /**
     * Get active countries
     * 
     * @param int $accountId
     * @return array $retour 
     */
    public function getActiveCountries($accountId = null, $mp = ''){
        
        $retour = array();
        $collection = null;
        
        if($accountId !== null){
            
            $collection = $this->getCollection()->addFieldToFilter('mpac_account_id', $accountId);
            
        }else{
            
            $collection = $this->getCollection()->setOrder('mpac_id', 'DESC');
            
        }        
        
        foreach($collection as $item){
            
            $account = $this->getAccountByCountryId($item->getId());
            
            if($account->getParam('is_active') == 1 && ($mp == '' || $account->getmpa_mp() == $mp))            
                if($item->getParam('active') == 1)
                    $retour[$item->getmpac_id()] = $item;            
            
        }        
        
        return $retour;
        
    }
    
    /**
     * Get associated marketplace
     * 
     * @return string 
     */
    public function getAssociatedMarketplace(){
        
        return Mage::getModel('MarketPlace/Accounts')->load($this->getmpac_account_id())->getmpa_mp();
        
    }
    
    /**
     * Get account by country id
     * 
     * @param int $id
     * @return MDN_MarketPlace_Model_Account 
     */
    public function getAccountByCountryId($id){        
        
        return Mage::getModel('MarketPlace/Accounts')->load($this->load($id)->getmpac_account_id());
        
    }
    
    /**
     *
     * @param type $countryId
     * @return type 
     * @todo : a quoi ça sert ?
     */
    public function getAccountByCurrentCountryId($countryId){
        
        $account = null;
        
        $currentCountry = $this->getCurrentCountry($countryId);       
        
        $account = $this->getAccountByCountryId($currentCountry->getId());
        
        return $account;
        
    }
    
    /**
     * Get associated account
     * 
     * @return MDN_MarketPlace_Model_Account 
     */
    public function getAssociatedAccount(){
        
        return Mage::getModel('MarketPlace/Accounts')->load($this->getmpac_account_id());
        
    }
    
}
