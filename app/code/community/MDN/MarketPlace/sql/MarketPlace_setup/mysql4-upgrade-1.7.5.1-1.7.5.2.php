<?php
/* 
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
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_internationalization')}` (
        mpi_id INTEGER AUTO_INCREMENT,
        mpi_store_id SMALLINT(5) NOT NULL,
        mpi_marketplace_id VARCHAR(50),
        mpi_language TEXT,
        CONSTRAINT PK_market_place_internationalization PRIMARY KEY(mpi_id)
    )ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS {$this->getTable('market_place_status')} (

        mps_id INTEGER AUTO_INCREMENT,
        mps_product_id INTEGER,
        mps_marketplace_id VARCHAR(50),
        mps_country VARCHAR(10),
        mps_status VARCHAR(50),
        mps_reference VARCHAR(255),
        mps_delay INTEGER,
        CONSTRAINT PK_market_place_status PRIMARY KEY(mps_id),
        CONSTRAINT UC_market_place_status UNIQUE(mps_marketplace_id,mps_country,mps_product_id)

    )ENGINE=InnoDB;

    ALTER TABLE {$this->getTable('market_place_feed')} ADD mp_country VARCHAR(50);

");

$installer->endSetup();
