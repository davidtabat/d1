<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */


/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$setup = Mage::getResourceModel('catalog/setup', 'core_setup');
$setup->removeAttribute(Mage_Catalog_Model_Product::ENTITY, 'rakuten_product_id');


$installer->endSetup();