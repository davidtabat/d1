<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_MagentoLocalizedEbay
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_MagentoLocalizedEbay_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_htmlData = null;

    /**
     * @return array
     */
    public function getHtmlData()
    {
        if (is_null($this->_htmlData)) {
            $htmlData = $this->_getHtmlDataFromWeb();
            if (sizeof($htmlData)) {
                $this->_htmlData = $htmlData;
            } else {
                $this->_htmlData = $this->_getHtmlDataFromFile();
            }
        }

        return $this->_htmlData;
    }

    /**
     * @return string
     */
    public function _getHtmlDataUrl()
    {
        $url = Mage::getStoreConfig('magento_localized/iframe_url_prefix')
            . $this->_getLanguageUrlPart()
            . '/wizard.json';

        return $url;
    }

    /**
     * @return string
     */
    protected function _getLanguageUrlPart()
    {
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        if (strpos($localeCode, Mage::getStoreConfig('magento_localized/iframe_main_language_code')) === 0) {
            return Mage::getStoreConfig('magento_localized/iframe_main_language_code');
        } else {
            return Mage::getStoreConfig('magento_localized/iframe_fallback_language_code');
        }
    }

    /**
     * @return array
     */
    protected function _getHtmlDataFromWeb()
    {
        $url = $this->_getHtmlDataUrl();
        if (substr($url, 0, 2) == '//') {
            $url = 'http:' . $url;
        }

        try {
            $request = new Zend_Http_Client($url);
            $response = $request->request();
            return Zend_Json::decode($response->getBody());
        } catch (Exception $e) {
            Mage::logException($e);
            return array();
        }
    }

    /**
     * @return array
     */
    protected function _getHtmlDataFromFile()
    {
        $filename = Mage::getModuleDir('etc', 'IntegerNet_MagentoLocalizedEbay') . DS . 'htmldata.json';
        if (is_readable($filename)) {
            $fileContents = file_get_contents($filename);
            try {
                return Zend_Json::decode($fileContents);
            } catch (Exception $e) {
                Mage::logException($e);
                return array();
            }
        }
    }
}