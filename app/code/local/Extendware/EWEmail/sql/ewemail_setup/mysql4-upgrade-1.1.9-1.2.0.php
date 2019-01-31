<?php

$installer = $this;
$installer->startSetup();

$command  = "
	ALTER TABLE `ewemail_log` 
		ADD COLUMN `status` enum('sent','failed')  COLLATE utf8_general_ci NOT NULL DEFAULT 'sent' after `log_id` , 
		CHANGE `type` `type` enum('plain','html')  COLLATE utf8_general_ci NOT NULL DEFAULT 'plain' after `status` , 
		ADD COLUMN `store_id` smallint(5) unsigned   NULL after `type` , 
		CHANGE `template_id` `template_id` varchar(255)  COLLATE utf8_general_ci NULL after `store_id` , 
		CHANGE `to_name` `to_name` varchar(255)  COLLATE utf8_general_ci NOT NULL after `template_id` , 
		CHANGE `to_email` `to_email` varchar(255)  COLLATE utf8_general_ci NOT NULL after `to_name` , 
		CHANGE `from_name` `from_name` varchar(255)  COLLATE utf8_general_ci NOT NULL after `to_email` , 
		CHANGE `from_email` `from_email` varchar(255)  COLLATE utf8_general_ci NOT NULL after `from_name` , 
		CHANGE `subject` `subject` text  COLLATE utf8_general_ci NOT NULL after `from_email` , 
		CHANGE `body` `body` text  COLLATE utf8_general_ci NOT NULL after `subject` , 
		CHANGE `sent_at` `sent_at` datetime   NOT NULL after `body` , 
		ADD KEY `idx_store_id`(`store_id`) ;
		
	ALTER TABLE `ewemail_log`
		ADD CONSTRAINT `fk_hwwlirf7o6vw6sp` 
		FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE ;
";
$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);
$installer->endSetup();