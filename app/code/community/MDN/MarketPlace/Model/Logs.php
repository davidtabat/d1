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
 * @copyright  Copyright (c) 2012 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_Logs extends Mage_Core_Model_Abstract {

    const kScopeCreation = 'creation';
    const kScopeUpdate = 'update';
    const kScopeOrders = 'orders';
    const kScopeTracking = 'tracking';
    const kScopeMisc = 'misc';
    const kIsError = 1;
    const kNoError = 0;

    /**
     * Get errors
     * 
     * @return array 
     */
    public function getErrors() {
        return array(
            self::kNoError => Mage::Helper('MarketPlace')->__('No'),
            self::kIsError => Mage::Helper('MarketPlace')->__('Yes')
        );
    }

    /**
     * Get scopes
     * 
     * @return array 
     */
    public function getScopes() {
        return array(
            self::kScopeCreation => Mage::Helper('MarketPlace')->__('Creation'),
            self::kScopeUpdate => Mage::Helper('MarketPlace')->__('Update'),
            self::kScopeOrders => Mage::Helper('MarketPlace')->__('Orders'),
            self::kScopeTracking => Mage::Helper('MarketPlace')->__('Tracking'),
            self::kScopeMisc => Mage::Helper('MarketPlace')->__('Misc')
        );
    }

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Logs');
    }

    /**
     * Add logs
     * 
     * @param string $marketplace
     * @param int $error
     * @param string $message
     * @param array $file
     * @param float $executionTime
     * @throws Exception 
     */
    public function addLog($marketplace, $error, $message, $scope, $file, $executionTime = 0) {

        $countryId = (int) Mage::registry('mp_country')->getId();

        $log = mage::getModel('MarketPlace/Logs');
        $log->setmp_date(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()))
                ->setmp_marketplace($marketplace)
                ->setmp_is_error($error)
                ->setmp_message(str_replace("\n", '<br/>', $message))
                ->setmp_scope($scope)
                ->setmp_execution_time($executionTime)
                ->setmp_country($countryId);

        $log->save();

        $this->limitRecords();

        // send an email for "bad" errors
        $notNotifyErrors = array('0');
        if (!in_array($error, $notNotifyErrors)) {
            $dest = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_bug_report();
            if ($dest != "")
                mail($dest, utf8_decode('Error Marketplace (' . $marketplace . ')'), utf8_decode(str_replace('<br/>', "\n", $message)));
        }

        if ($file['fileName'] != NULL) {

            $content = $file['fileContent'];
            $filePath = Mage::app()->getConfig()->getTempVarDir();
            $filePath .= ($file['type'] == 'export') ? '/export/marketplace/' . $marketplace : '/import/marketplace/' . $marketplace;

            if (!file_exists($filePath))
                mkdir($filePath, 0755, true);

            $filePath .= '/' . $file['fileName'];

            $handle = @fopen($filePath, 'w');
            if (!$handle) {
                throw new Exception('Error while attempt to write file ' . $filePath, 10);
            }
            fputs($handle, $content);
            fclose($handle);
        }
    }

    /**
     * Limit logs records
     */
    public function limitRecords() {

        $prefix = Mage::getConfig()->getTablePrefix();

        $max = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_max_log();
        $max = ($max != '') ? $max : 500;

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = 'SELECT * FROM ' . $prefix . 'market_place_logs ORDER BY mp_id DESC';

        $res = $read->fetchAll($sql);

        if (count($res) > $max) {

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $delete = array();

            for ($i = $max; $i < count($res); $i++) {
                if (isset($res[$i]))
                {
                    if ($res[$i]['mp_id'] != '')
                        $delete[] = $res[$i]['mp_id'];
                }
            }

            if (count($delete) > 0) {
                $inClose = implode(",", $delete);
                $sql = 'DELETE FROM ' . $prefix . 'market_place_logs WHERE mp_id IN (' . $inClose . ')';
                $write->query($sql);
            }
        }
    }

    /**
     * Save file
     *
     * @param array $file
     * <ul>
     * <li>array $file['filenames']</li>
     * <li>string $file['fileContent']</li>
     * <li>string $file['marketplace']</li>
     * <li>string $file['type']</li>
     * </ul>
     */
    public function saveFile($file) {

        $filenames = $file['filenames'];
        $content = $file['fileContent'];
        $marketplace = $file['marketplace'];
        $filePath = Mage::app()->getConfig()->getTempVarDir();
        $filePath .= ($file['type'] == 'export') ? '/export/marketplace/' . $marketplace : '/import/marketplace/' . $marketplace;
        if (array_key_exists('dir', $file)) {
            $filePath .= '/' . $file['dir'];
        }

        if (!file_exists($filePath))
            mkdir($filePath, 0755, true);

        // update exported file and create history file
        foreach ($filenames as $filename) {
            $filePathTmp = $filePath . '/' . $filename;

            $handle = @fopen($filePathTmp, 'w+');
            if (!$handle)
                throw new Exception('Error while attempt to write file ' . $filePathTmp, 10);

            fputs($handle, $content);
            fclose($handle);
        }

        // remove old files
        $handle = opendir($filePath);
        $files = array();
        while ($file = readdir($handle)) {
            if (!is_dir($filePath . '/' . $file) && !preg_match('/^\./', $file))
                $files[$file] = filemtime($filePath . '/' . $file);
        }

        arsort($files);
        $oldFiles = array_slice($files, 20, count($files));

        foreach ($oldFiles as $k => $v) {
            unlink($filePath . '/' . $k);
        }
    }

}
