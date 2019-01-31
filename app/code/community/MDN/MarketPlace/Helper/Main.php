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
class MDN_MarketPlace_Helper_Main extends Mage_Core_Helper_Abstract {

    /**
     * Retrieve marketplaces names
     *
     * @return string
     */
    public function getMarketPlaces() {

        return Mage::helper('MarketPlace')->getMarketplacesName();
    }

    /**
     * check if cron is activated
     *
     * @param string $mp (marketplace name)
     * @deprecated since version 2.1
     */
    public function isCronActivate($mp) {
        throw new Exception(Mage::Helper('MarketPlace')->__('Deprecated method in %s',__METHOD__));
    }

    /**
     * Import orders for one marketplace
     *
     * @param string $mp (marketplace name)
     * @return string
     */
    public function importOrders($mp) {
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error =  MDN_MarketPlace_Model_Logs::kNoError;
        $retour = '';
        
        $start = microtime(true);
        
        try {
            
            $orders = mage::helper(ucfirst($mp))->getMarketplaceOrders();

            if (count($orders) > 0) {

                $retour = Mage::helper(ucfirst($mp))->importMarketPlaceOrders($orders);                              

            } else {

                $retour = Mage::Helper('MarketPlace')->__('No order to import');                
            }
            
        } catch (Exception $e) {

            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $retour = $e->getMessage();
 
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
                Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                $error, 
                $retour, 
                MDN_MarketPlace_Model_Logs::kScopeOrders, 
                array('fileName' => NULL), 
                $executionTime
        );
        
        return $retour;
        
    }

    /**
     * Update stocks and prices for one marketplace
     *
     * @param string $mp (marketplace name)
     * @return int $nbr
     *
     */
    public function updateStocksAndPrices($mp) {

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        $message = '';
        $nbr = 0;
        
        $start = microtime(true);
        
        try {
            
            $nbr = mage::helper(ucfirst($mp))->updateStocksAndPrices();
            $message = Mage::Helper('MarketPlace')->__('Prices & stocks exported (%s)', $nbr);            
           
        } catch (Exception $e) {

            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = $e->getMessage();
            
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
                Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                $error, 
                $message, 
                MDN_MarketPlace_Model_Logs::kScopeUpdate, 
                array('fileName' => NULL),
                $executionTime                   
        );
        
        return $nbr;
        
    }

    /**
     * Send trackings for one marketplace
     *
     * @param string $mp (marketplace name)
     */
    public function sendTracking($mp) {
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try {

            mage::helper(ucfirst($mp))->sendTracking();
            $message = Mage::Helper('MarketPlace')->__('Tracking send.');
                        
        } catch (Exception $e) {

            $error = MDN_MarketPlace_Model_Logs::kNoError;
            $message = $e->getMessage();
            
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        // add log message
        mage::getModel('MarketPlace/Logs')->addLog(
                Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                $error, 
                $message, 
                MDN_MarketPlace_Model_Logs::kScopeTracking, 
                array('fileName' => NULL), 
                $executionTime
        );
        
    }

    /**
     * Check product creation for one marketplace
     *
     * @param string $mp
     */
    public function checkProductCreation($mp) {

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try {
            
            $message .= mage::helper(ucfirst($mp))->checkProductCreation();                        
                        
        } catch (Exception $e) {

            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = $e->getMessage();
                        
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;

        mage::getModel('MarketPlace/Logs')->addLog(
                Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                $error, 
                $message, 
                MDN_MarketPlace_Model_Logs::kScopeCreation, 
                array('fileName' => NULL), 
                $executionTime
        );
        
    }
    
    /**
     * Auto submit
     * 
     * @param string $mp 
     */
    public function autoSubmit($mp){
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try {

            Mage::Helper(ucfirst($mp).'/ProductCreation')->autoSubmit();
            $message = Mage::Helper('MarketPlace')->__('Submission auto done');
                        
        } catch (Exception $e) {

            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = $e->getMessage();
                        
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;

        mage::getModel('MarketPlace/Logs')->addLog(
                Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                $error, 
                $message, 
                MDN_MarketPlace_Model_Logs::kScopeCreation, 
                array('fileName' => NULL), 
                $executionTime
        );
    }

    /**
     * Import orders for all marketplaces (called by the cron)
     *
     * @return string
     */
    public function cronImportOrders() {

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error = false;
        $message = '';
        $retour = '';
        
        $start = microtime(true);
        
        try {

            foreach ($this->getMarketPlaces() as $mp) {

                $mp = strtolower($mp);

                // recuperation des comptes actifs (pays)
                $accounts = Mage::getModel('MarketPlace/Accounts')->getActivesCountriesObject($mp);
				
                // parcours des différents comptes
                foreach($accounts as $accountId => $countries){
				
                    // parcours des pays associés au compte courant
                    foreach($countries as $countryId => $country){
					
						Mage::unregister('mp_country');
                        // sauvagarde du pays courant
                        Mage::register('mp_country', $country);

                        // on verifie que le cron est activé pour ce pays
                        if($country->getParam('enable_order_importation') == 1){

                            // on peut lancer la mise à jour !
                            $this->importOrders($mp);

                        }else{
                            $error = true;
                            $account = Mage::getModel('MarketPlace/Accounts')->load($accountId);
                            $message = Mage::Helper('MarketPlace')->__('Cron is disabled for %s (%s)', $account->getmpa_name(), $country->getmpac_country_code());
                            $retour .= $message.'<br/>';
                        }

                        if ($error === true) {
                            $end = microtime(true);
                            $executionTime = $end - $start;
                            mage::getModel('MarketPlace/Logs')->addLog(
                                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                                    MDN_MarketPlace_Model_Logs::kNoError, 
                                    $message,
                                    MDN_MarketPlace_Model_Logs::kScopeOrders,
                                    array('fileName' => NULL),
                                    $executionTime
                            );
                            $error = false;
                        }                                                        
                    }                                                
                }
            }

            $retour .= Mage::Helper('MarketPlace')->__('Orders imported').'<br/>';
            
        } catch (Exception $e) {
            
            $end = microtime(true);
            $executionTime = $end - $start;
            
            mage::getModel('MarketPlace/Logs')->addLog(
                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                    MDN_MarketPlace_Model_Logs::kIsError, 
                    $e->getMessage().'<br/>'.$e->getTraceAsString(), 
                    MDN_MarketPlace_Model_Logs::kScopeOrders, 
                    array('fileName' => NULL),
                    $executionTime
           );
            
            $retour .= $e->getMessage().'<br/>';
            
        }
        
        return $retour;
        
    }

    /**
     * Update stocks and prices for all marketplaces (called by the cron)
     *
     * @return string
     */
    public function cronUpdateStocksAndPrices() {

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error = false;
        $message = '';
        $retour = '';
        
        $start = microtime(true);
        
        try {
            foreach ($this->getMarketPlaces() as $mp) {

                $mp = strtolower($mp);                

                // recuperation des comptes actifs (pays)
                $accounts = Mage::getModel('MarketPlace/Accounts')->getActivesCountriesObject($mp);

                // parcours des différents comptes
                foreach($accounts as $accountId => $countries){

                    // parcours des pays associés au compte courant
                    foreach($countries as $countryId => $country){

						Mage::unregister('mp_country');
                        // sauvagarde du pays courant
                        Mage::register('mp_country', $country);

                        // on verifie que le cron est activé pour ce pays
                        if($country->getParam('enable_product_update') == 1){

                            // on peut lancer la mise à jour !
                            $this->updateStocksAndPrices($mp);

                        }else{
                            $error = true;
                            $account = Mage::getModel('MarketPlace/Accounts')->load($accountId);
                            $message = Mage::Helper('MarketPlace')->__('Cron is disabled for %s (%s)', $account->getmpa_name(), $country->getmpac_country_code());
                            $retour .= $message.'<br/>';
                        }

                        if ($error === true) {
                            $end = microtime(true);
                            $executionTime = $end - $start;
                            mage::getModel('MarketPlace/Logs')->addLog(
                                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                                    MDN_MarketPlace_Model_Logs::kNoError, 
                                    $message,
                                    MDN_MarketPlace_Model_Logs::kScopeUpdate,
                                    array('fileName' => NULL),
                                    $executionTime
                            );
                            $error = false;
                        }                                                        
                    }                                                
                }
            }
            
            $retour .= Mage::Helper('MarketPlace')->__('Products updated').'<br/>';
            
        }catch(Exception $e){
        
            $end = microtime(true);
            $executionTime = $end - $start;
            
            mage::getModel('MarketPlace/Logs')->addLog(
                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                    MDN_MarketPlace_Model_Logs::kIsError, 
                    $e->getMessage().'<br/>'.$e->getTraceAsString(),
                    MDN_MarketPlace_Model_Logs::kScopeUpdate,
                    array('fileName' => NULL),
                    $executionTime
            );
            $retour .= $e->getMessage().'<br/>';
            
        }
        
        return $retour;
                    
    }

    /**
     * Send trackings for all marketplaces (called by the cron)
     *
     * @return string
     */
    public function cronSendTracking() {

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = false;
        $retour = '';
        
        $start = microtime(true);
        
        try {
            foreach ($this->getMarketPlaces() as $mp) {

                $mp = strtolower($mp);

                // recuperation des comptes actifs (pays)
                $accounts = Mage::getModel('MarketPlace/Accounts')->getActivesCountriesObject($mp);

                // parcours des différents comptes
                foreach($accounts as $accountId => $countries){

                    // parcours des pays associés au compte courant
                    foreach($countries as $countryId => $country){

						Mage::unregister('mp_country');
                        // sauvagarde du pays courant
                        Mage::register('mp_country', $country);

                        // on verifie que le cron est activé pour ce pays
                        if($country->getParam('enable_tracking_export') == 1){

                            // on peut lancer la mise à jour !
                            $this->sendTracking($mp);

                        }else{
                            $error = true;          
                            $account = Mage::getModel('MarketPlace/Accounts')->load($accountId);
                            $message = Mage::Helper('MarketPlace')->__('Cron is disabled for %s (%s)', $account->getmpa_name(), $country->getmpac_country_code());
                            $retour .= $message.'<br/>';
                        }

                        if ($error === true) {
                            $end = microtime(true);
                            $executionTime = $end - $start;
                            mage::getModel('MarketPlace/Logs')->addLog(
                                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                                    MDN_MarketPlace_Model_Logs::kNoError, 
                                    $message,
                                    MDN_MarketPlace_Model_Logs::kScopeTracking,
                                    array('fileName' => NULL),
                                    $executionTime
                            );
                            $error = false;
                        }                                                        
                    }                                                
                }
            }            

            $retour .= Mage::Helper('MarketPlace')->__('Tracking sent').'<br/>';
            
        } catch (Exception $e) {
            
            $end = microtime(true);
            $executionTime = $end - $start;
            
            mage::getModel('MarketPlace/Logs')->addLog(
                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                    MDN_MarketPlace_Model_Logs::kIsError, 
                    $e->getMessage().'<br/>'.$e->getTraceAsString(), 
                    MDN_MarketPlace_Model_Logs::kScopeTracking,
                    array('fileName' => NULL),
                    $executionTime
            );
            
            $retour .= $e->getMessage().'<br/>';
        }
        
        return $retour;
    }

    /**
     * Check product creation for all marketplaces (called by the cron)
     *
     * @return string
     */
    public function cronCheckProductCreation() {

        $end = 0;
        $start = 0;
        $executionTime = 0;
        $error = false;
        $message = '';
        $retour = '';
        
        $start = microtime(true);
        
        try {
            
            foreach ($this->getMarketPlaces() as $mp) {

                $mp = strtolower($mp);

                // recuperation des comptes actifs (pays)
                $accounts = Mage::getModel('MarketPlace/Accounts')->getActivesCountriesObject($mp);

                // parcours des différents comptes
                foreach($accounts as $accountId => $countries){

                    // parcours des pays associés au compte courant
                    foreach($countries as $countryId => $country){

						Mage::unregister('mp_country');
                        // sauvagarde du pays courant
                        Mage::register('mp_country', $country);

                        // on verifie que le cron est activé pour ce pays
                        if($country->getParam('enable_product_creation') == 1){

                            // on peut lancer la mise à jour !
                            $this->checkProductCreation($mp);

                        }else{
                            $error = true;
                            $account = Mage::getModel('MarketPlace/Accounts')->load($accountId);
                            $message = Mage::Helper('MarketPlace')->__('Cron is disabled for %s (%s)', $account->getmpa_name(), $country->getmpac_country_code());
                            $retour .= $message.'<br/>';
                        }

                        if ($error === true) {
                            $end = microtime(true);
                            $executionTime = $end - $start;
                            mage::getModel('MarketPlace/Logs')->addLog(
                                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                                    MDN_MarketPlace_Model_Logs::kNoError, 
                                    $message,
                                    MDN_MarketPlace_Model_Logs::kScopeCreation,
                                    array('fileName' => NULL),
                                    $executionTime
                            );
                            $error = false;
                        }                                                        
                    }                                                
                }
            }

            $retour .= Mage::Helper('MarketPlace')->__('Product creation checked').'<br/>';
            
        } catch (Exception $e) {
            $end = microtime(true);
            $executionTime = $end - $start;
            mage::getModel('MarketPlace/Logs')->addLog(
                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                    MDN_MarketPlace_Model_Logs::kIsError, 
                    $e->getMessage().'<br/>'.$e->getTraceAsString(), 
                    MDN_MarketPlace_Model_Logs::kScopeCreation,
                    array('fileName' => NULL),
                    $executionTime
            );
            
            $retour .= $e->getMessage().'<br/>';
            
        }
        
        return $retour;
        
    }
    
    /**
     * Cron auto submit
     * 
     * @return string $retour 
     */
    public function cronAutoSubmit(){
        $end = 0;
        $start = 0;
        $executionTime = 0;
        $error = false;
        $message = '';
        $retour = '';
        
        $start = microtime(true);
        
        try {
            
            foreach ($this->getMarketPlaces() as $mp) {

                $mp = strtolower($mp);

                // recuperation des comptes actifs (pays)
                $accounts = Mage::getModel('MarketPlace/Accounts')->getActivesCountriesObject($mp);

                // parcours des différents comptes
                foreach($accounts as $accountId => $countries){

                    // parcours des pays associés au compte courant
                    foreach($countries as $countryId => $country){

						Mage::unregister('mp_country');
                        // sauvagarde du pays courant
                        Mage::register('mp_country', $country);

                        // on verifie que le cron est activé pour ce pays
                        if($country->getParam('enable_product_creation') == 1){
                            // on verifie que l'auto soumission est activée !
                            if($country->getParam('so_active') == 1){

                                // lancement de la soumission auto pour ce pays
                                $this->autoSubmit($mp);

                            }else{
                                $error = true;
                                $account = Mage::getModel('MarketPlace/Accounts')->load($accountId);
                                $message = Mage::Helper('MarketPlace')->__('Auto submit is disabled for %s (%s)', $account->getmpa_name(), $country->getmpac_country_code());
                                $retour .= $message.'<br/>';
                            }
                        }else{
                            $error = true;
                            $account = Mage::getModel('MarketPlace/Accounts')->load($accountId);
                            $message = Mage::Helper('MarketPlace')->__('Cron is disabled for %s (%s)', $account->getmpa_name(), $country->getmpac_country_code());
                            $retour .= $message.'<br/>';
                        }

                        if ($error === true) {
                            $end = microtime(true);
                            $executionTime = $end - $start;
                            mage::getModel('MarketPlace/Logs')->addLog(
                                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                                    MDN_MarketPlace_Model_Logs::kNoError, 
                                    $message,
                                    MDN_MarketPlace_Model_Logs::kScopeCreation,
                                    array('fileName' => NULL),
                                    $executionTime
                            );
                            $error = false;
                        }                                                        
                    }                                                
                }
            }

            $retour .= Mage::Helper('MarketPlace')->__('Auto submission done').'<br/>';
            
        } catch (Exception $e) {
            $end = microtime(true);
            $executionTime = $end - $start;
            mage::getModel('MarketPlace/Logs')->addLog(
                    Mage::helper(ucfirst($mp))->getMarketPlaceName(), 
                    MDN_MarketPlace_Model_Logs::kIsError, 
                    $e->getMessage().'<br/>'.$e->getTraceAsString(), 
                    MDN_MarketPlace_Model_Logs::kScopeCreation,
                    array('fileName' => NULL),
                    $executionTime
            );
            
            $retour .= $e->getMessage().'<br/>';
            
        }
        
        return $retour;
    }

}
