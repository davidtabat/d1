<?php

$installer = $this;
$installer->startSetup();

$command  = "
	DROP TABLE IF EXISTS `ewemail_blacklist`;
	CREATE TABLE `ewemail_blacklist` (
	  `blacklist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `email_address` varchar(255) NOT NULL,
	  `updated_at` datetime NOT NULL,
	  `created_at` datetime NOT NULL,
	  PRIMARY KEY (`blacklist_id`),
	  KEY `idx_email_address` (`email_address`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);
$installer->endSetup();