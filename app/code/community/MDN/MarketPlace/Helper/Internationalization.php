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
abstract class MDN_MarketPlace_Helper_Internationalization extends Mage_Core_Helper_Abstract {

    /* @var array */
    private $_cache = array();

    /**
     * Get countries which are selected in current marketplace configuration
     *
     * @param sting $mp
     */
    public function getSelectedCountriesAsArray($mp){

        $mp = strtolower($mp);
        return explode(",", Mage::getStoreConfig('marketplace/'.$mp.'_general/countries'));

    }

    /**
     * Get countries which are available on current marketplace
     */
    abstract function getAvailableCountriesAsArray();

    /**
     * Get exchange rate
     *
     * @param string $mainCurrency
     * @param string $targetCountry
     *
     * @return float $exchangeRate
     */
    public function getExchangeRate($mainCurrency, $targetCountry){

        $targetCurrency = $this->getCurrencyForCountry($targetCountry);
        
        //echo $mainCurrency.' -> '.$targetCurrency;die();

        if(!array_key_exists($targetCurrency, $this->_cache))
            $this->_cache[$targetCurrency] = ($mainCurrency == $targetCurrency) ? 1 : $this->calculExchangeRate($mainCurrency, $targetCurrency);

        //echo '<pre>';var_dump($this->_cache);die('</pre>');
        
        return $this->_cache[$targetCurrency];
        
    }

    /**
     * Get currency for country
     *
     * @params string $country
     */
    protected function getCurrencyForCountry($country){
        
        $retour = null;

        $xml = new DomDocument();
        $xml->load(Mage::app()->getConfig()->getOptions()->getLibDir().'/MDN/Marketplace/ExchangeRate/Currencies.xml');
        foreach($xml->getElementsByTagName('item') as $item){
            if($item->getElementsByTagName('country')->item(0)->nodeValue == $country){
                $retour = $item->getElementsByTagName('currency')->item(0)->nodeValue;
                break;
            }
        }

        return $retour;
    }

    /**
     * Calcul exchange rate
     * 
     * @param type $from
     * @param type $to
     * @return type 
     */
    protected function calculExchangeRate($from, $to){

        $moneyConverter = new MoneyConverter($from, $to);
        return $moneyConverter->getExchangeRate();

    }

    /**
     * Get language dor country
     * 
     * @param type $country
     * @return type 
     */
    public function getLanguageForCountry($country){

        $retour = array();

        $xml = new DomDocument();
        $xml->load(Mage::app()->getConfig()->getOptions()->getLibDir().'/MDN/Marketplace/i18n/countries.xml');

        foreach($xml->getElementsByTagName('item') as $item){

            if($item->getElementsByTagName('name')->item(0)->nodeValue == $country){
                $languages = $item->getElementsByTagName('languages')->item(0);
                foreach($languages->getElementsByTagName('language') as $language){
                    $retour[] = $language->nodeValue;
                }
                break;
            }

        }

        return $retour;

    }

    /**
     * Get country from request
     * 
     * @param type $request
     * @return type 
     */
    public function getCountryFromrequest($request){

        return $this->getCountryFromCountryCode($request->getParam('country'));

    }

    /**
     * Get language from request
     * 
     * @param type $request
     * @return type 
     */
    public function getLanguageFromrequest($request){

        return $this->getLanguageFromCountryCode($request->getParam('country'));

    }

    /**
     * Get language from country code
     * 
     * @param type $code
     * @return type 
     */
    public function getLanguageFromCountryCode($code){
        $retour = '';
        $tmp = explode("-",$code);
        $retour = $tmp[1];
        return $retour;
    }

    /**
     * Get country from country code
     * 
     * @param type $code
     * @return type 
     */
    public function  getCountryFromCountryCode($code){
        $retour = '';
        $tmp = explode("-",$code);
        $retour = $tmp[0];
        return $retour;
    }

    /**
     * Get country by code
     * 
     * @param type $countryCode
     * @return string 
     */
    public function getCountryByCode($countryCode){

        // code like FR-FR
        $retour = '';

        $xml = new DomDocument();
        $xml->load(Mage::app()->getConfig()->getOptions()->getLibDir().'/MDN/Marketplace/i18n/countries.xml');

        foreach($xml->getElementsByTagName('item') as $item){

            $code = $item->getElementsByTagName('code')->item(0)->nodeValue;

            $languages = $item->getElementsByTagName('languages')->item(0);

            foreach($languages->getElementsByTagName('language') as $language){

                $currentLanguage = $language->nodeValue;

                if($countryCode == $this->formatCountryCode($code, $currentLanguage)){
                    $retour = $item->getElementsByTagName('name')->item(0)->nodeValue.' ('.$currentLanguage.')';
                    break;
                }

            }

        }

        return $retour;
    }

    /**
     * Format country code
     * 
     * @param type $code
     * @param type $language
     * @return type 
     */
    public function formatCountryCode($code,$language){
        return $code.'-'.$language;
    }

    /**
     * Get countries with same languages
     * 
     * @param type $countryCode 
     */
    abstract function getCountriesWithSameLanguages($countryCode);

}
