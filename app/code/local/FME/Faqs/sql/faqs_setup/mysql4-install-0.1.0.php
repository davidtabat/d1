<?php
/**
 * Advance FAQ Management Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Advance FAQ Management
 * @author     Kamran Rafiq Malik <support@fmeextensions.com>
 *                          
 * 	       Asif Hussain <support@fmeextensions.com>
 * 	       
 * @copyright  Copyright 2012 © www.fmeextensions.com All right reserved
 */

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('faqs')};
CREATE TABLE {$this->getTable('faqs')} (
        `faqs_id` int(11) unsigned NOT NULL auto_increment,      
          `topic_id` int(11) default NULL,                         
          `title` varchar(255) NOT NULL default '',                
          `show_on_main` smallint(6) NOT NULL default '0',         
          `faq_answar` text NOT NULL,                              
          `faq_order` smallint(6) NOT NULL default '0',                
          `rating_num` bigint(20) NOT NULL default '0',            
          `rating_count` bigint(20) NOT NULL default '0',          
          `rating_stars` decimal(12,4) NOT NULL default '0.0000',  
          `accordion_opened` smallint(6) NOT NULL default '0',     
          `status` smallint(6) NOT NULL default '0',               
          `created_time` datetime default NULL,                    
          `update_time` datetime default NULL,                     
          PRIMARY KEY  (`faqs_id`)                               
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('faqs_topics')};
CREATE TABLE {$this->getTable('faqs_topics')} (
            `topic_id` int(11) unsigned NOT NULL auto_increment,  
               `title` varchar(255) NOT NULL default '',             
               `identifier` varchar(255) NOT NULL default '',        
               `status` smallint(6) NOT NULL default '0',            
               `default` smallint(6) NOT NULL default '2',           
               `show_on_main` smallint(6) NOT NULL default '0',      
               `topic_order` smallint(6) NOT NULL default '0',             
               `created_time` datetime default NULL,                 
               `update_time` datetime default NULL,                  
               PRIMARY KEY  (`topic_id`)                            
 ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('faqs_store')};
CREATE TABLE {$this->getTable('faqs_store')} (                                
	`topic_id` int(11) unsigned NOT NULL,                     
	`store_id` smallint(5) unsigned NOT NULL,                 
	PRIMARY KEY  (`topic_id`,`store_id`),                     
	KEY `FK_FAQS_STORE_STORE` (`store_id`)                    
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Faqs Stores';
");




$installer->setConfigData('faqs/list/page_title','Faqs');
$installer->setConfigData('faqs/list/identifier','faqs');
$installer->setConfigData('faqs/list/meta_keywords','Faqs');
$installer->setConfigData('faqs/list/meta_description','Faqs');
$installer->setConfigData('faqs/list/display_categories','all');
$installer->setConfigData('faqs/list/show_number_of_questions','3');
$installer->setConfigData('faqs/list/enable_view_more','1');
$installer->setConfigData('faqs/list/enable_accordion','1');

$installer->setConfigData('faqs/rating/enable','1');
$installer->setConfigData('faqs/rating/allow_customers','all');

$installer->setConfigData('faqs/general/faq_block','1');
$installer->setConfigData('faqs/general/faq_search_block','1');
$installer->setConfigData('faqs/general/faq_maxtopic','5');

$installer->setConfigData('faqs/themes/select_theme','theme5');

$installer->setConfigData('faqs/seo/url_suffix','.html');

$installer->endSetup(); 