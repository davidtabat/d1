<?php

/**
 * Authentification class
 * 
 * @author Nicolas Mugnier nicolas@maisondulogiciel.com
 */
class MDN_Cdiscount_Helper_Auth extends Mage_Core_Helper_Abstract {

    private $_sandboxUrl = 'https://sts.preprod-cdiscount.com/users/httpIssue.svc/?realm=https://wsvc.preprod-cdiscount.com/MarketplaceAPIService.svc';
    private $_productionUrl = 'https://sts.cdiscount.com/users/httpIssue.svc/?realm=https://wsvc.cdiscount.com/MarketplaceAPIService.svc';


    /**
     * Getter
     *
     * @return string
     */
    public function getSandboxUrl() {
        return $this->_sandboxUrl;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getProductionUrl() {
        return $this->_productionUrl;
    }

    /**
     * Get API url
     *
     * @return string
     */
    public function getUrl() {

        $url = '';

        $country = Mage::registry('mp_country');
        if (!$country)
            $country = Mage::helper('Cdiscount')->getDefaultCountry();
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        if ($account->getParam('use_sandbox') == 1)
            $url = $this->getSandboxUrl();
        else
            $url = $this->getProductionUrl();

        return $url;
    }

    /**
     * Retrieve token
     * <ul>
     * <li>load last saved token</li>
     * <li>request new token</li>
     * </ul>
     *
     * @return string
     */
    public function getToken() {

        $country = Mage::registry('mp_country');
        if (!$country)
            $country = Mage::helper('Cdiscount')->getDefaultCountry();

        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        $obj = Mage::getModel('MarketPlace/Token')->getCollection()
                        ->addFieldToFilter('mp_marketplace_id', Mage::helper('Cdiscount')->getMarketPlaceName())
                        ->addAttributeToSort('mp_id', 'desc')
                        ->addFieldToFilter('mp_sandbox', $account->getParam('use_sandbox'))
                        ->addFieldToFilter('mp_country', $country->getId())
                        ->getFirstItem();

        $currentDate = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp() + 2 * 60000); // 2 mins margin

        if ($currentDate > $obj->getmp_validity() || !$obj->getmp_token()) {
            return $this->authentification();
        }

        return $obj->getmp_token();
    }

    /**
     * Request new token
     *
     * @return string $token
     */
    public function authentification() {

        $client = null;
        $response = null;
        $obj = null;
        $token = null;
        $validity = null;

        $country = Mage::registry('mp_country');
        if (!$country)
            $country = Mage::helper('Cdiscount')->getDefaultCountry();
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        // delete old token
        $collection = Mage::getModel('MarketPlace/Token')->getCollection()
                ->addFieldToFilter('mp_marketplace_id', Mage::helper('Cdiscount')->getMarketPlaceName());
        
        foreach($collection as $item)
            $item->delete();

        $client = new Zend_Http_Client($this->getUrl());
        $client->setAuth($account->getParam('login'), $account->getParam('password'), Zend_Http_Client::AUTH_BASIC);

        $response = $client->request();

        if ($response->getStatus() != 200)
            throw new Exception($response->getBody());

        $xml = new DomDocument();
        $xml->loadXML($response->getBody());

        $token = $xml->getElementsByTagName('string')->item(0)->nodeValue;

        if ($token === null)
            throw new Exception('Please check login/pass configuration');

        $validity = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp() + 48 * 3600);
        
        $obj = Mage::getModel('MarketPlace/Token')
                        ->setmp_marketplace_id(Mage::helper('Cdiscount')->getMarketPlaceName())
                        ->setmp_token($token)
                        ->setmp_validity($validity)
                        ->setmp_sandbox($account->getParam('use_sandbox'))
                        ->setmp_country($country->getId())
                        ->save();

        return $token;
    }

    /**
     * Check connection to cdiscount
     */
    public function checkConnection(){

        $retour = false;

        try{
            $this->authentification();
            $retour = true;
        }catch(Exception $e){
            $retour = $e->getMessage();
        }

        return $retour;

    }

}
