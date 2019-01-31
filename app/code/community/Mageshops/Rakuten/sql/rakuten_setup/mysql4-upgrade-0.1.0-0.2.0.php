<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */


/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('rakuten/rakuten_request'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'auto_increment' => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id')
    ->addColumn('element_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
            'nullable'  => true,
        ), 'Element Id')
    ->addColumn('url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable'  => false,
        ), 'Request URL')
    ->addColumn('params', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(
            'nullable'  => false,
        ), 'Request parameters')
    ->addColumn('started', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => true,
        ), 'Request start time')
    ->addColumn('finished', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => true,
        ), 'Request finish time')
    ->addColumn('tries', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
            'default'   => 0,
        ), 'Number of tries')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
            'default'   => 0,
        ), 'Request status')
    ->addColumn('answer', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(
            'nullable'  => true,
        ), 'Rakuten answer')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(
            'nullable'  => true,
        ), 'Comment')
    ->addIndex(
        'rakuten_request_id',
        array('entity_id'))
    ->addIndex(
        'rakuten_request_element',
        array('element_id'))
    ->addIndex(
        'rakuten_request_status',
        array('status'));
$installer->getConnection()->createTable($table);


$installer->endSetup();
