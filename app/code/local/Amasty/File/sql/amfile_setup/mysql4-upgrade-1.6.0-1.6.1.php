<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

$this->startSetup();

$this->run(
    "ALTER TABLE `{$this->getTable('amfile/store')}`
    ADD COLUMN `show_ordered` TINYINT(1) UNSIGNED DEFAULT '0',
    ADD COLUMN `use_default_show_ordered` TINYINT(1) UNSIGNED DEFAULT '1',
    ADD COLUMN `use_default_customer_group` TINYINT(1) UNSIGNED DEFAULT '1'"
);

$this->run("CREATE TABLE `{$this->getTable('amfile/store_customer_group')}` (
  `id` INT(10) UNSIGNED NOT NULL COMMENT 'Id' AUTO_INCREMENT,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `file_id` MEDIUMINT(9) NOT NULL,
  `customer_group_id` smallint(5) NOT NULL COMMENT 'Customer Group Id',
  `is_active`         TINYINT(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_AMASTY_STORE_CUSTOMER_GROUP_STORE` FOREIGN KEY (`file_id`) REFERENCES `{$this->getTable('amfile/file')}` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMASTY_STORE_CUSTOMER_GROUP_AM_FILE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='amasty file customer group'");

$this->endSetup();