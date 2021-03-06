<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_Logs
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer = null;

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

    //########################################

     /**
        ALTER TABLE `m2epro_synchronization_log`
        CHANGE COLUMN `synchronization_run_id` `operation_history_id` INT(11) UNSIGNED DEFAULT NULL,
        CHANGE COLUMN `synch_task` `task` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        DROP INDEX `synchronization_run_id`,
        DROP INDEX `synch_task`,
        ADD INDEX `operation_history_id` (`operation_history_id`),
        ADD INDEX `task` (`task`);

        ALTER TABLE `m2epro_listing_log`
        CHANGE COLUMN `listing_id` `listing_id` INT(11) UNSIGNED DEFAULT NULL,
        CHANGE COLUMN `listing_title` `listing_title` VARCHAR(255) DEFAULT NULL;

        ALTER TABLE `m2epro_listing_other_log`
        ADD INDEX `identifier` (`identifier`);

        ALTER TABLE `m2epro_order_log`
        CHANGE COLUMN `type` `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
        CHANGE COLUMN `message` `message` TEXT NOT NULL AFTER `initiator`,
        ADD INDEX `component_mode` (`component_mode`);

        UPDATE `m2epro_order_log` SET `type` = `type` + 5 WHERE (`type` IN (0,2));
        UPDATE `m2epro_order_log` SET `type` = 2 WHERE (`type` = 5);
        UPDATE `m2epro_order_log` SET `type` = 4 WHERE (`type` = 7);
     */

    //########################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->processSynchronizationLogTable();
        $this->processListingLogTable();
        $this->processListingOtherLogTable();
        $this->processOrderLogTable();
    }

    //########################################

    protected function isNeedToSkip()
    {
        $connection = $this->_installer->getConnection();

        $synchronizationLogTable = $this->_installer->getTable('m2epro_synchronization_log');
        if ($connection->tableColumnExists($synchronizationLogTable, 'operation_history_id') !== false) {
            return true;
        }

        return false;
    }

    //########################################

    protected function processSynchronizationLogTable()
    {
        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_synchronization_log');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'synchronization_run_id') !== false &&
            $connection->tableColumnExists($tempTable, 'operation_history_id') === false) {
            $connection->changeColumn(
                $tempTable, 'synchronization_run_id', 'operation_history_id',
                'INT(11) UNSIGNED DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'synch_task') !== false &&
            $connection->tableColumnExists($tempTable, 'task') === false) {
            $connection->changeColumn(
                $tempTable, 'synch_task', 'task',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        if (isset($tempTableIndexList[strtoupper('synchronization_run_id')])) {
            $connection->dropKey($tempTable, 'synchronization_run_id');
        }

        if (isset($tempTableIndexList[strtoupper('synch_task')])) {
            $connection->dropKey($tempTable, 'synch_task');
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!isset($tempTableIndexList[strtoupper('operation_history_id')])) {
            $connection->addKey($tempTable, 'operation_history_id', 'operation_history_id');
        }

        if (!isset($tempTableIndexList[strtoupper('task')])) {
            $connection->addKey($tempTable, 'task', 'task');
        }
    }

    protected function processListingLogTable()
    {
        $connection = $this->_installer->getConnection();
        $tempTable = $this->_installer->getTable('m2epro_listing_log');

        if ($connection->tableColumnExists($tempTable, 'listing_id') !== false) {
            $connection->changeColumn(
                $tempTable, 'listing_id', 'listing_id',
                'INT(11) UNSIGNED DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'listing_title') !== false) {
            $connection->changeColumn(
                $tempTable, 'listing_title', 'listing_title',
                'VARCHAR(255) DEFAULT NULL'
            );
        }
    }

    protected function processListingOtherLogTable()
    {
        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_listing_other_log');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!isset($tempTableIndexList[strtoupper('identifier')])) {
            $connection->addKey($tempTable, 'identifier', 'identifier');
        }
    }

    protected function processOrderLogTable()
    {
        $connection = $this->_installer->getConnection();

        $tempTable = $this->_installer->getTable('m2epro_order_log');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'type') !== false) {
            $connection->changeColumn(
                $tempTable, 'type', 'type',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 2'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'message') !== false) {
            $connection->changeColumn(
                $tempTable, 'message', 'message',
                'TEXT NOT NULL AFTER `initiator`'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('component_mode')])) {
            $connection->addKey($tempTable, 'component_mode', 'component_mode');
        }

        $offset = 5;
        $connection->update(
            $tempTable, array('type' => new Zend_Db_Expr('`type` + '.$offset)), '`type` IN (0,2)'
        );

        $connection->update($tempTable, array('type' => 2), '`type` = '.(0 + $offset));
        $connection->update($tempTable, array('type' => 4), '`type` = '.(2 + $offset));
    }

    //########################################
}
