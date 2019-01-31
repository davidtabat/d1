<?php

$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

     CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_required_fields')}` (
                mp_id INTEGER AUTO_INCREMENT,
                mp_marketplace_id VARCHAR(50),
                mp_path VARCHAR(255),
                mp_attribute_name VARCHAR(50),
                CONSTRAINT PK_market_place_required_fields PRIMARY KEY (mp_id)
     )ENGINE=InnoDB;

");



$installer->endSetup();

?>