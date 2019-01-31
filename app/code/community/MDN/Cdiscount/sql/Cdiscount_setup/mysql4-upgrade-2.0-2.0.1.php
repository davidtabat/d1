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
 * @package MDN_Cdiscount
 * @version 2.0.1
 */

$installer = $this;

$installer->startSetup();

$installer->run("
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('cdiscount_token_history')}` (
        cth_id INTEGER AUTO_INCREMENT,
        cth_date DATETIME NOT NULL,
        cth_token VARCHAR(255) NOT NULL,
        cth_url VARCHAR(255) NOT NULL,
        CONSTRAINT PRIMARY KEY(cth_id)
    )ENGINE=InnoDB;

");

$installer->endSetup();
