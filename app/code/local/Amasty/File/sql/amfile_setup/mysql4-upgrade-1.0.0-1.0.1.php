<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfile/icon')}` CHANGE `active` `active` TINYINT( 1 ) NOT NULL DEFAULT '1';
");

$this->endSetup();