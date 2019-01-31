<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 */


/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
        ->newTable($installer->getTable('rakuten/rakuten_synchronization'))
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'auto_increment' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
                ), 'id')
        ->addColumn('locked', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
                ), 'locked')
        ->addColumn('percent', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
                ), 'percent')
        ->addColumn('message', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'unsigned' => true,
            'nullable' => false,
                ), 'message')
        ->addColumn('time', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'unsigned' => true,
            'nullable' => false,
                ), 'time');

$installer->getConnection()->createTable($table);
$installer->endSetup();
