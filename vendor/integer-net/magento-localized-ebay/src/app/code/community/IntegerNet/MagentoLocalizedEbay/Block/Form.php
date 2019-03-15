<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_MagentoLocalizedEbay
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

class IntegerNet_MagentoLocalizedEbay_Block_Form extends Mage_Adminhtml_Block_Widget
{
    /**
     * @param string $moduleName i.e. 'paypal'
     * @param string $areaName i.e. 'short_description'
     * @param string $sectionName i.e. 'ebay'
     * @return string
     */
    public function getAreaHtml($moduleName, $areaName, $sectionName = 'ebay')
    {
        $htmlData = Mage::helper('magento_localized_ebay')->getHtmlData();
        if (isset($htmlData[$sectionName][$moduleName][$areaName])) {
            return $htmlData[$sectionName][$moduleName][$areaName];
        }
        return '';
    }
    
    public function getSections()
    {
        return array('ebay', 'other');
    }
    
    public function getModules($sectionName)
    {
        $htmlData = Mage::helper('magento_localized_ebay')->getHtmlData();
        
        return array_keys($htmlData[$sectionName]);
    }

    /**
     * @param string $moduleName i.e. 'paypal'
     * @param string $sectionName i.e. 'ebay'
     * @return string
     */
    public function hasModuleHtml($moduleName, $sectionName = 'ebay')
    {
        $htmlData = Mage::helper('magento_localized_ebay')->getHtmlData();
        return isset($htmlData[$sectionName][$moduleName]);
    }

    public function getPayPalDirectUrl()
    {
        return 'https://www.paypal.com/webapps/mpp/referral/paypal-express-checkout?partner_id=NB9WWHYEMVUMS';
    }

    public function getPayPalPopupUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/magentolocalizedebay/paypalPopup');
    }

    public function getPayPalApiUrl()
    {
        return 'https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_profile-api-access';
    }

    public function getPayPalMerchantUrl()
    {
        return 'https://www.paypal.com/de/webapps/mpp/home-merchant';
    }

    public function getBillsafeRegisterUrl()
    {
        return 'http://www.billsafe.de/offer?utm_source=part&utm_medium=magento&utm_campaign=magentoDEEdition';
    }

    public function getEbayRegisterUrl()
    {
        return 'http://pages.ebay.de/help/account/how-to-register.html';
    }

    public function getEbayMerchantUrl()
    {
        return 'https://scgi.ebay.de/ws/eBayISAPI.dll?RegisterEnterInfo&bizflow=2';
    }

    public function getEbayMerchantDocumentationUrl()
    {
        return 'http://pages.ebay.de/gewerblich-verkaufen/';
    }

    public function getEbayStartUrl()
    {
        return 'http://success.ebay.de/ebay-start/?id=m2e';
    }

    public function getM2EUrl()
    {
        return 'http://m2epro.com/';
    }

    public function isModuleAvailable($moduleName, $sectionName = 'ebay')
    {
        if ($moduleName == 'billsafe' && !Mage::getStoreConfigFlag('magento_localized/billsafe/is_available')) {
            return false;
        }
        
        return $this->hasModuleHtml($moduleName, $sectionName);
    }
}