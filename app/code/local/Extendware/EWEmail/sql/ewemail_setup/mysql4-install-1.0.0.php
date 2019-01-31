<?php
Mage::helper('ewcore/cache')->clean();
$installer = $this;
$installer->startSetup();

$command  = "
	DROP TABLE IF EXISTS `ewemail_log`;
	CREATE TABLE `ewemail_log` (
	  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `type` enum('plain','html') NOT NULL DEFAULT 'plain',
	  `template_id` varchar(255) DEFAULT NULL,
	  `to_name` varchar(255) NOT NULL,
	  `to_email` varchar(255) NOT NULL,
	  `from_name` varchar(255) NOT NULL,
	  `from_email` varchar(255) NOT NULL,
	  `subject` text NOT NULL,
	  `body` text NOT NULL,
	  `sent_at` datetime NOT NULL,
	  PRIMARY KEY (`log_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);

$installer->endSetup();
