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

/** @var $setup Mage_Catalog_Model_Resource_Setup */
$setup = Mage::getResourceModel('catalog/setup', 'core_setup');


$attrCode = 'rakuten_default_category_id';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Default Rakuten Category for Product';
$attrNote = null;
$sort_order = 70;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => $sort_order,
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'note' => $attrNote,
    'input' => 'select',
    'class' => '',
    'source' => 'rakuten/kategorien',
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



$attrCode = 'rakuten_map_category_id';
$attrLabel = 'Default Rakuten Category to Map';
$attrNote = null;
$sort_order = 30;

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attrCode, array(
    'group'         => $attrGroupName,
    'input'         => 'select',
    'type'          => 'int',
    'label'         => $attrLabel,
    'backend'       => '',
    'frontend'      => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'          => $attrNote,
    'class'         => '',
    'source'        => 'rakuten/kategorien',
    'user_defined'  => true,
    'default'       => '0',
    'unique'        => false,
    'sort_order' => $sort_order,
));


$installer->endSetup();
