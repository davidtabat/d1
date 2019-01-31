<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
$this->startSetup();

$this->run("
    ALTER TABLE  `{$this->getTable('amfile/stat')}`
        CHANGE  `date`  `date` DATETIME NOT NULL,
        CHANGE  `rating`  `rating` INT( 10 ) NOT NULL DEFAULT '1',
        ADD  `customer_id` INT( 10 ) UNSIGNED NOT NULL;

    ALTER TABLE `{$this->getTable('amfile/file')}`
        DROP `rating`;
");

$this->endSetup();
