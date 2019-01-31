<?php

$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

    ALTER TABLE `{$this->getTable('market_place_categories')}` MODIFY mpc_association_data VARCHAR(255)

");



$installer->endSetup();

?>