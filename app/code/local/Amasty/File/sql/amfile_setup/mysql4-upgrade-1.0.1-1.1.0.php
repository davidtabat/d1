<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfile/file')}` ADD `file_link` varchar(255) NOT NULL AFTER `file_name`;
");

$this->endSetup();