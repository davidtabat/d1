<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @category    Mage
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    /**
     * Retrieve feed url
     * @return string
     */
    public function getUpdateFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://') . 'www.geissweb.de/feeds/extensionupdates.php'.$this->getExtraParams();
        }
        return $this->_feedUrl;
    }

    /**
     * Check feed for modification
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function checkUpdate()
    {
        if ((($this->getFrequency()*15) + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();
        $feedXml = $this->getUpdateFeedData();

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int)$item->severity,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }

            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement
     */
    public function getUpdateFeedData()
    {
        try {

            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(array(
                'timeout'   => 2
            ));
            $curl->write(Zend_Http_Client::GET, $this->getUpdateFeedUrl(), '1.0');
            $data = $curl->read();
            if ($data === false) {
                return false;
            }
            $data = preg_split('/^\r?$/m', $data, 2);
            $data = trim($data[1]);
            $curl->close();
            $xml  = new SimpleXMLElement($data);

        } catch (Exception $e) {
            return false;
        }
        return $xml;
    }

	/**
	 * Used for support and licensing
	 * Please do not remove this function, we will only use this for internal statistics and we use it strictly
     * confidential.
	 *
	 * We thank you for your support!
	 */
    public function getExtraParams()
    {
		$params = "?magev=".base64_encode(Mage::getVersion());
		$params .= "&modv=".base64_encode(Mage::getConfig()->getNode('modules/Geissweb_Euvatgrouper')->version);
		$params .= "&lic=".base64_encode(Mage::getStoreConfig('euvatgrouper/extension_info/license_key', Mage::app()->getStore()->getId()));
		$params .= "&srv=".base64_encode($_SERVER['SERVER_NAME']);
		$params .= "&docroot=".base64_encode($_SERVER['DOCUMENT_ROOT']);
		return $params;
    }

}