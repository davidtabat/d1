<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_DatabaseBrokenTables
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    public $emptyTables        = array();
    public $notInstalledTables = array();
    public $crashedTables      = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentInspectionDatabaseBrokenTables');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/inspection/databaseBrokenTables.phtml');

        $this->prepareTablesInfo();
    }

    //########################################

    protected function isShown()
    {
        return !empty($this->emptyTables) ||
               !empty($this->notInstalledTables) ||
               !empty($this->crashedTables);
    }

    //########################################

    protected function prepareTablesInfo()
    {
        $this->emptyTables        = $this->getEmptyTables();
        $this->notInstalledTables = $this->getNotInstalledTables();
        $this->crashedTables      = $this->getCrashedTables();
    }

    //########################################

    protected function getEmptyTables()
    {
        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        $emptyTables = array();
        foreach ($this->getGeneralTables() as $table) {
            if (!$helper->isTableReady($table)) {
                continue;
            }

            !$helper->getCountOfRecords($table) && $emptyTables[] = $table;
        }

        return $emptyTables;
    }

    protected function getNotInstalledTables()
    {
        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        $notInstalledTables = array();
        foreach ($helper->getMySqlTables() as $tableName) {
            !$helper->isTableExists($tableName) && $notInstalledTables[] = $tableName;
        }

        return $notInstalledTables;
    }

    protected function getCrashedTables()
    {
        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        $crashedTables = array();
        foreach ($helper->getMySqlTables() as $tableName) {
            if (!$helper->isTableExists($tableName)) {
                continue;
            }

            !$helper->isTableStatusOk($tableName) && $crashedTables[] = $tableName;
        }

        return $crashedTables;
    }

    //########################################

    protected function getGeneralTables()
    {
        return array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_synchronization_config',
            'm2epro_wizard',
            'm2epro_marketplace',
            'm2epro_amazon_marketplace',
            'm2epro_ebay_marketplace'
        );
    }

    //########################################
}
