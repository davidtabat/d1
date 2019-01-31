<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2015 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'mirasvithd_create_tickets',
        'tinyint(1) NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'mirasvithd_default_department',
        'int(11) NOT NULL'
    )
;


$installer->endSetup();
