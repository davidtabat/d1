<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('sttl_permissions_advancedrole')};
CREATE TABLE {$this->getTable('sttl_permissions_advancedrole')} (
  `advancedrole_id` smallint(5) unsigned NOT NULL auto_increment,
  `role_id` int(10) unsigned NOT NULL,
  `gws_is_all` text NOT NULL,
  `website_id` text NOT NULL,
  `store_id` text NOT NULL,
  `storeview_ids` text NOT NULL,
  `root_cat_ids` text NOT NULL,
  `sub_cat_ids` text NOT NULL,
  PRIMARY KEY  (`advancedrole_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

");

$installer->endSetup(); 

?>