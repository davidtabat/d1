<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
$this->startSetup();

$this->run("
  ALTER TABLE `{$this->getTable('amfile/file')}`
    CHANGE `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
    ADD INDEX(`product_id`);
");

$this->endSetup();
