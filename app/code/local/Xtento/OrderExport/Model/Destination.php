<?php

/**
 * Product:       Xtento_OrderExport (1.8.5)
 * ID:            gynD3Fd2Yg7FQAAmb/3dfnkXJHnIofKqiiFav0FPkb8=
 * Packaged:      2015-09-09T07:08:21+00:00
 * Last Modified: 2014-11-10T23:42:06+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Destination.php
 * Copyright:     Copyright (c) 2015 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Destination extends Mage_Core_Model_Abstract
{
    /*
     * Destination model containing information about "destinations" where exported files can be saved on
     */

    /*
     * Destination Types
     */
    const TYPE_LOCAL = 'local';
    const TYPE_FTP = 'ftp';
    const TYPE_SFTP = 'sftp';
    const TYPE_HTTP = 'http';
    const TYPE_EMAIL = 'email';
    const TYPE_WEBSERVICE = 'webservice';
    const TYPE_CUSTOM = 'custom';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('xtento_orderexport/destination');
    }

    /*
     * Return destination types
     */
    public function getTypes()
    {
        $values = array();
        $values[self::TYPE_LOCAL] = Mage::helper('xtento_orderexport')->__('Local Directory');
        $values[self::TYPE_FTP] = Mage::helper('xtento_orderexport')->__('FTP Server');
        $values[self::TYPE_SFTP] = Mage::helper('xtento_orderexport')->__('SFTP Server');
        $values[self::TYPE_HTTP] = Mage::helper('xtento_orderexport')->__('HTTP Server');
        $values[self::TYPE_EMAIL] = Mage::helper('xtento_orderexport')->__('E-Mail Recipient(s)');
        $values[self::TYPE_WEBSERVICE] = Mage::helper('xtento_orderexport')->__('Webservice/API');
        $values[self::TYPE_CUSTOM] = Mage::helper('xtento_orderexport')->__('Custom Class');
        return $values;
    }

    /*
     * Set last result message for this destination
     */
    public function setLastResultMessage($message)
    {
        $this->setData('last_result_message', date('c', Mage::getModel('core/date')->timestamp(time())) . ": " . $message);
        return $this;
    }

    /*
     * Save files on destination
     */
    public function saveFiles($generatedFiles)
    {
        $destinationClass = Mage::getModel('xtento_orderexport/destination_' . $this->getData('type'), array('destination' => $this));
        if ($destinationClass !== false) {
            return $destinationClass->saveFiles($generatedFiles);
        }
    }

    /*
     * Retrieve profiles which are using this destination.
     */
    public function getProfileUsage()
    {
        $profileUsage = array();
        $profileCollection = Mage::getModel('xtento_orderexport/profile')->getCollection();
        $profileCollection->addFieldToFilter('destination_ids', array('neq' => ''));
        $profileCollection->getSelect()->order('entity ASC');
        foreach ($profileCollection as $profile) {
            $destinationIds = explode("&", $profile->getData('destination_ids'));
            if (in_array($this->getId(), $destinationIds)) {
                $profileUsage[] = $profile;
            }
        }
        return $profileUsage;
    }

    /*
     * Overwrite ID when importing destinations. Thanks to ST for the great idea.
     */
    public function saveWithId()
    {
        // First check if the ID we've set exists as a model right now.
        $realId = $this->getId();
        $idExists = Mage::getModel($this->_resourceName)->setId(null)->load($realId)->getId();

        // Perform the regular saving routine as if it's a new model
        if (!$idExists) {
            $this->setId(null);
        }
        $this->save();

        // Update the new model we created with the original ID.
        if (!$idExists) {
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write->update(
                $this->_getResource()->getMainTable(),
                array($this->_getResource()->getIdFieldName() => $realId),
                array("`{$this->_getResource()->getIdFieldName()}` = {$this->getId()}")
            );
            #$write->commit();
        }

        return $this;
    }

    /*
     * Fix bad user input for specific configuration values when requested by the module
     */
    public function getHostname() {
        $hostname = $this->getData('hostname');
        $hostname = str_replace(array('ftp://', 'http://'), '', $hostname);
        $hostname = trim($hostname);
        return $hostname;
    }

    public function getPort() {
        $port = $this->getData('port');
        $port = preg_replace('/[^0-9]/', '', $port);
        return $port;
    }

    public function getTimeout() {
        $timeout = $this->getData('timeout');
        $timeout = preg_replace('/[^0-9]/', '', $timeout);
        return $timeout;
    }
}