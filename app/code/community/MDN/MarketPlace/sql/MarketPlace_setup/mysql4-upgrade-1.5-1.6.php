<?php

$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

    ALTER TABLE `{$this->getTable('market_place_data')}` DROP INDEX UC_market_place_data

");

$installer->endSetup();

?>