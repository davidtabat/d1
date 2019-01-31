<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('kaufberatung')};
CREATE TABLE {$this->getTable('kaufberatung')} (
  `kaufberatung_id` int(11) unsigned NOT NULL auto_increment,
  `color` varchar(255) NOT NULL default '',
  `print` varchar(255) NOT NULL default '',
  `other_print` varchar(255) NOT NULL default '',
  `features` varchar(255) NOT NULL default '',
  `paper_trays` varchar(255) NOT NULL default '',
  `connect` varchar(255) NOT NULL default '',
  `volumes` varchar(255) NOT NULL default '',
  `desktop` varchar(255) NOT NULL default '',
  `budget` varchar(255) NOT NULL default '',
  `comments` text NOT NULL default '',
  `Salutation` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`kaufberatung_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 