<?php


$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

	ALTER TABLE  `{$this->getTable('market_place_categories')}` ADD  `mpc_category_path` VARCHAR( 50 ) NOT NULL

");

$installer->endSetup();

?>
