<?php


$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_categories')}` (
		mpc_id INTEGER AUTO_INCREMENT,
		mpc_marketplace_id VARCHAR(50),
		mpc_category_id INTEGER,
		mpc_association_data VARCHAR(100),
		CONSTRAINT PK_market_place_categories PRIMARY KEY(mpc_id)
    )ENGINE=InnoDB;

");

$installer->endSetup();

?>
