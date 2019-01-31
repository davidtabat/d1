<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 */


/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$setup = Mage::getResourceModel('catalog/setup', 'core_setup');
$setup->removeAttribute(Mage_Catalog_Model_Category::ENTITY,'rakuten_map_category_id');

$installer->endSetup();
