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
 * @package MDN_MarketPlace
 * @version 2.0.3
 */

$installer = $this;

$installer->startSetup();

$installer->run("

    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_brands')}` (
        mpb_id INT AUTO_INCREMENT,
        mpb_marketplace_id VARCHAR(50),
        mpb_code VARCHAR(50),
        mpb_label VARCHAR(50),
        CONSTRAINT PRIMARY KEY(mpb_id)
    )ENGINE=INNODB;      

    ALTER TABLE `{$this->getTable('market_place_token')}` ADD COLUMN mp_country INT;
    
");

$installer->endSetup();

