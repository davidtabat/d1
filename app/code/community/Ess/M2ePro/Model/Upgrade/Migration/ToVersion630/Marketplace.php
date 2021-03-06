<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Marketplace
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer = null;

    protected $_forceAllSteps = false;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     */
    public function getInstaller()
    {
        return $this->_installer;
    }

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->_installer = $installer;
    }

    // ---------------------------------------

    public function setForceAllSteps($value = true)
    {
        $this->_forceAllSteps = $value;
    }

    //########################################

    /**

        ALTER TABLE m2epro_amazon_dictionary_category
            DROP COLUMN node_hash,
            DROP COLUMN xsd_hash,
            DROP COLUMN item_types,
            DROP COLUMN sorder,
            DROP KEY node_hash,
            DROP KEY xsd_hash,
            CHANGE COLUMN path path VARCHAR(255) DEFAULT NULL,
            ADD COLUMN product_data_nick VARCHAR(255) DEFAULT NULL AFTER parent_category_id,
            ADD COLUMN product_data_required_specifics TEXT DEFAULT NULL AFTER product_data_nick,
            ADD COLUMN keywords TEXT DEFAULT NULL AFTER browsenode_id,
            ADD COLUMN required_attributes TEXT DEFAULT NULL AFTER keywords,
            ADD INDEX product_data_nick (product_data_nick);

        ALTER TABLE m2epro_amazon_dictionary_marketplace
            DROP COLUMN nodes,
            ADD COLUMN product_data LONGTEXT DEFAULT NULL AFTER marketplace_id,
            ADD COLUMN vocabulary LONGTEXT DEFAULT NULL AFTER product_data;

        ALTER TABLE m2epro_amazon_dictionary_specific
            DROP COLUMN xsd_hash,
            ADD COLUMN product_data_nick VARCHAR(255) NOT NULL AFTER parent_specific_id,
            ADD INDEX product_data_nick (product_data_nick);

        ALTER TABLE m2epro_amazon_marketplace
            ADD COLUMN is_asin_available tinyint(2) UNSIGNED NOT NULL DEFAULT 1 AFTER default_currency,
            ADD INDEX is_asin_available (is_asin_available);

    */

    //########################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->processAmazonDictionaryCaterory();
        $this->processAmazonDictionaryMarketplace();
        $this->processAmazonDictionarySpecific();
        $this->processAmazonMarketplace();
    }

    //########################################

    protected function isNeedToSkip()
    {
        if ($this->_forceAllSteps) {
            return false;
        }

        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_amazon_marketplace');
        if ($connection->tableColumnExists($tempTable, 'is_asin_available') !== false) {
            return true;
        }

        return false;
    }

    //########################################

    protected function processAmazonDictionaryCaterory()
    {
        $this->_installer->run("TRUNCATE TABLE `m2epro_amazon_dictionary_category`");

        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_amazon_dictionary_category');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'node_hash') !== false) {
            $connection->dropColumn($tempTable, 'node_hash');
        }

        if ($connection->tableColumnExists($tempTable, 'xsd_hash') !== false) {
            $connection->dropColumn($tempTable, 'xsd_hash');
        }

        if ($connection->tableColumnExists($tempTable, 'item_types') !== false) {
            $connection->dropColumn($tempTable, 'item_types');
        }

        if ($connection->tableColumnExists($tempTable, 'sorder') !== false) {
            $connection->dropColumn($tempTable, 'sorder');
        }

        if ($connection->tableColumnExists($tempTable, 'path') !== false) {
            $connection->changeColumn(
                $tempTable, 'path', 'path',
                'VARCHAR(255) DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'product_data_nick') === false) {
            $connection->addColumn(
                $tempTable, 'product_data_nick',
                'VARCHAR(255) DEFAULT NULL AFTER parent_category_id'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'product_data_required_specifics') === false) {
            $connection->addColumn(
                $tempTable, 'product_data_required_specifics',
                'TEXT DEFAULT NULL AFTER product_data_nick'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'keywords') === false) {
            $connection->addColumn(
                $tempTable, 'keywords',
                'TEXT DEFAULT NULL AFTER browsenode_id'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'required_attributes') === false) {
            $connection->addColumn(
                $tempTable, 'required_attributes',
                'TEXT DEFAULT NULL AFTER keywords'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('product_data_nick')])) {
            $connection->addKey($tempTable, 'product_data_nick', 'product_data_nick');
        }
    }

    protected function processAmazonDictionaryMarketplace()
    {
        $this->_installer->run("TRUNCATE TABLE `m2epro_amazon_dictionary_marketplace`");

        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_amazon_dictionary_marketplace');

        if ($connection->tableColumnExists($tempTable, 'nodes') !== false) {
            $connection->dropColumn($tempTable, 'nodes');
        }

        if ($connection->tableColumnExists($tempTable, 'product_data') === false) {
            $connection->addColumn(
                $tempTable, 'product_data',
                'LONGTEXT DEFAULT NULL AFTER marketplace_id'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'vocabulary') === false) {
            $connection->addColumn(
                $tempTable, 'vocabulary',
                'LONGTEXT DEFAULT NULL AFTER product_data'
            );
        }
    }

    protected function processAmazonDictionarySpecific()
    {
        $this->_installer->run("TRUNCATE TABLE `m2epro_amazon_dictionary_specific`");

        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_amazon_dictionary_specific');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'xsd_hash') !== false) {
            $connection->dropColumn($tempTable, 'xsd_hash');
        }

        if ($connection->tableColumnExists($tempTable, 'product_data_nick') === false) {
            $connection->addColumn(
                $tempTable, 'product_data_nick',
                'VARCHAR(255) NOT NULL AFTER parent_specific_id'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('product_data_nick')])) {
            $connection->addKey($tempTable, 'product_data_nick', 'product_data_nick');
        }
    }

    //########################################

    protected function processAmazonMarketplace()
    {
        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_amazon_marketplace');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'is_asin_available') === false) {
            $connection->addColumn(
                $tempTable, 'is_asin_available',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 1 AFTER default_currency'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('is_asin_available')])) {
            $connection->addKey($tempTable, 'is_asin_available', 'is_asin_available');
        }

        $this->_installer->run(
            <<<SQL

    UPDATE `m2epro_amazon_marketplace`
    SET is_asin_available = 0
    WHERE marketplace_id = 24;

SQL
        );
    }

    //########################################
}
