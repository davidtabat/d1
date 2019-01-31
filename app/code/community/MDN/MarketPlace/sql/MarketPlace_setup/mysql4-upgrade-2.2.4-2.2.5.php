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
 * @version 2.1.2
 */

$installer = $this;

$installer->startSetup();

$installer->run("

            
        ALTER TABLE `{$this->getTable('market_place_brands')}` ADD COLUMN mpb_manufacturer_id INT;
        CREATE INDEX IDX_mpb_manufacturer_id ON `{$this->getTable('market_place_brands')}` (mpb_manufacturer_id);

    
");

$installer->endSetup();
