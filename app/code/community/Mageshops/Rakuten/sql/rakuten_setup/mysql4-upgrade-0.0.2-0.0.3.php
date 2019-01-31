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


$attrGroupName = 'Rakuten Data';


$attrCode = 'rakuten_sync';
$attrLabel = 'Sync Category to Rakuten';
$attrNote = null;
$sort_order = 10;

$objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attrCode, array(
    'group'         => $attrGroupName,
    'input'         => 'select',
    'type'          => 'int',
    'label'         => $attrLabel,
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'          => $attrNote,
    'class'         => '',
    'source'        => 'eav/entity_attribute_source_boolean',
    'user_defined'  => true,
    'default'       => '0',
    'unique'        => false,
    'sort_order' => $sort_order,
));


$attrCode = 'rakuten_category_id';
$attrLabel = 'Rakuten Category Id';
$attrNote = null;
$sort_order += 10;

$objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attrCode, array(
    'group'         => $attrGroupName,
    'input'         => 'text',
    'type'          => 'int',
    'label'         => $attrLabel,
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'          => $attrNote,
    'class'         => '',
    'source'        => '',
    'user_defined'  => true,
    'default'       => '',
    'unique'        => false,
    'sort_order' => $sort_order,
));


$installer->endSetup();
