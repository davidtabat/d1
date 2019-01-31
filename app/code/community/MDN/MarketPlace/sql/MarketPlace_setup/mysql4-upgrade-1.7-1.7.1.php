<?php

$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

     CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_feed')}` (
		mp_id INTEGER AUTO_INCREMENT,
                mp_feed_id VARCHAR(50),
		mp_marketplace_id VARCHAR(50),
                mp_type VARCHAR(50),
		mp_status VARCHAR(50),
		mp_ids TEXT,
                mp_content LONGTEXT,
                mp_response LONGTEXT,
                mp_date DATETIME,
		CONSTRAINT PK_market_place_feed PRIMARY KEY(mp_id)
    )ENGINE=InnoDB;

");



$installer->endSetup();

?>