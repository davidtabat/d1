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
class MDN_Cdiscount_Model_Observer {
    
    /**
     * Test connection
     * 
     * @param Varien_Event_Observer $observer 
     */
    public function testConnection(Varien_Event_Observer $observer){
        
        $account = $observer->getaccount();
        $exception = '';
        $response = null;
        $_country = null;

        if ($account instanceof MDN_MarketPlace_Model_Accounts && $account->getmpa_mp() == 'cdiscount') {

            // only for FRANCE
            $_country = Mage::getModel('MarketPlace/Countries')
                        ->setmpac_account_id($account->getId())
                        ->setmpac_country_code('FR')
                        ->setmpac_id(0);
            
            Mage::register('mp_country', $_country);
            
            $connexion = mage::helper('Cdiscount/Auth')->checkConnection();
            if($connexion === true){

                $mpac_params = array(
                    'name' => 'Cdiscount FR',
                    'countryCode' => 'FR',
                    'currencyCode' => 'EUR',
                    'languageCode' => 'FR',
                    'domain' => 'Cdiscount.fr'
                );

                $country = Mage::getModel('MarketPlace/Countries')->getCollection()
                        ->addFieldToFilter('mpac_account_id', $account->getId())
                        ->addFieldToFilter('mpac_country_code', 'FR')
                        ->getFirstItem();

                if (!$country->getmpac_id()) {

                    $country = Mage::getModel('MarketPlace/Countries')
                            ->setmpac_country_code($mpac_params['countryCode'])
                            ->setmpac_params($mpac_params)
                            ->setmpac_account_id($account->getId())
                            ->save();
                }                

            }else{

                throw new Exception('Connexion FAILED : <br/>'.$connexion);

            } 
                        
        }
        
    }
    
}
