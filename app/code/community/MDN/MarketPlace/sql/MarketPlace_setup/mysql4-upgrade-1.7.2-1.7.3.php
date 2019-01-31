<?php

$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

     CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_token')}` (
                mp_id INTEGER AUTO_INCREMENT,
                mp_marketplace_id VARCHAR(50),
                mp_token VARCHAR(255),
                mp_validity TIMESTAMP,
                CONSTRAINT PK_market_place_token PRIMARY KEY (mp_id)
     )ENGINE=InnoDB;

");



$installer->endSetup();

?>