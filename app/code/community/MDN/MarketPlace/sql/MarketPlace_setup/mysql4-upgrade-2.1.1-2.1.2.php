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

        CREATE INDEX IDX_marketplace_id ON `{$this->getTable('market_place_data')}` (mp_marketplace_id);    
        CREATE INDEX IDX_marketplace_status ON `{$this->getTable('market_place_data')}` (mp_marketplace_status); 
        CREATE INDEX IDX_last_update ON `{$this->getTable('market_place_data')}` (mp_last_update); 
        CREATE INDEX IDX_update_status ON `{$this->getTable('market_place_data')}` (mp_update_status); 
        CREATE INDEX IDX_product_id ON `{$this->getTable('market_place_data')}` (mp_product_id); 
            
        CREATE INDEX IDX_feed_id ON `{$this->getTable('market_place_feed')}` (mp_feed_id);
        CREATE INDEX IDX_marketplace_id ON `{$this->getTable('market_place_feed')}` (mp_marketplace_id);    
        CREATE INDEX IDX_type ON `{$this->getTable('market_place_feed')}` (mp_type);
        CREATE INDEX IDX_status ON `{$this->getTable('market_place_feed')}` (mp_status);
        CREATE INDEX IDX_country ON `{$this->getTable('market_place_feed')}` (mp_country);
            
        CREATE INDEX IDX_marketplace_id ON `{$this->getTable('market_place_logs')}` (mp_marketplace);
        CREATE INDEX IDX_is_error ON `{$this->getTable('market_place_logs')}` (mp_is_error);
        CREATE INDEX IDX_scope ON `{$this->getTable('market_place_logs')}` (mp_scope);
        CREATE INDEX IDX_country ON `{$this->getTable('market_place_logs')}` (mp_country);
            
        ALTER TABLE `{$this->getTable('market_place_categories')}` MODIFY COLUMN mpc_association_data VARCHAR(1000);

    
");

$installer->endSetup();
