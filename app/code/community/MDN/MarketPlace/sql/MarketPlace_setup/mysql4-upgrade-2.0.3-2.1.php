<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("
    
    ALTER TABLE `{$this->getTable('market_place_data')}` ADD COLUMN mp_last_stock_sent INT;
    ALTER TABLE `{$this->getTable('market_place_data')}` ADD COLUMN mp_last_delay_sent INT;
    ALTER TABLE `{$this->getTable('market_place_data')}` ADD COLUMN mp_last_price_sent FLOAT;
    ALTER TABLE `{$this->getTable('market_place_data')}` ADD COLUMN mp_update_status VARCHAR(50);
        
    ALTER TABLE `{$this->getTable('market_place_data')}` ADD COLUMN mp_last_update DATETIME NOT NULL DEFAULT '1900-01-01';
    ALTER TABLE `{$this->getTable('market_place_logs')}` ADD COLUMN mp_scope VARCHAR(50) NOT NULL DEFAULT 'misc';
    ALTER TABLE `{$this->getTable('market_place_logs')}` ADD COLUMN mp_country VARCHAR(50) NOT NULL;
    
    UPDATE `{$this->getTable('market_place_logs')}` SET mp_scope = 'creation' WHERE mp_is_error IN (14,15,19,21,22);
    UPDATE `{$this->getTable('market_place_logs')}` SET mp_scope = 'update' WHERE mp_is_error IN (4);
    UPDATE `{$this->getTable('market_place_logs')}` SET mp_scope = 'orders' WHERE mp_is_error IN (1,2,3);
    UPDATE `{$this->getTable('market_place_logs')}` SET mp_scope = 'tracking' WHERE mp_is_error IN (16);
    
    UPDATE `{$this->getTable('market_place_logs')}` SET mp_is_error = 1 WHERE mp_is_error != 0;
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_accounts')}` (
        mpa_id INT AUTO_INCREMENT NOT NULL,
        mpa_name VARCHAR(50) NOT NULL,
        mpa_params TEXT NOT NULL,
        mpa_mp VARCHAR(50) NOT NULL,
        CONSTRAINT Pk_market_place_accounts PRIMARY KEY(mpa_id)
    )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_configuration')}` (
        mpc_id INT AUTO_INCREMENT NOT NULL,
        mpc_marketplace_id VARCHAR(50) NOT NULL,
        mpc_params TEXT NOT NULL,
        CONSTRAINT Pk_market_place_configuration PRIMARY KEY(mpc_id)
    )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_accounts_countries')}` (
        mpac_id INT AUTO_INCREMENT NOT NULL,
        mpac_country_code VARCHAR(50) NOT NULL,
        mpac_params TEXT NOT NULL,
        mpac_account_id INTEGER,
        CONSTRAINT Pk_market_place_accounts_countries PRIMARY KEY(mpac_id)
    )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
    
");

$installer->endSetup();
