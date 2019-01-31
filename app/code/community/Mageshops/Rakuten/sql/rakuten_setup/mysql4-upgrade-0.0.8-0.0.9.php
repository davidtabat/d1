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


$attrCode = 'rakuten_shipping_group';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Shipping Group';
$attrComment = 'Shipping groups are defined in Rakuten Merchant BackOffice';
$attrNote = null;
$sort_order = 90;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => $sort_order,
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'comment' => $attrComment,
    'note' => $attrNote,
    'input' => 'select',
    'class' => '',
    'source' => 'rakuten/system_config_source_shippingGroup',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '1',
    'visible_on_front' => false,
    'unique' => false,
    'is_configurable' => false,
    'used_for_promo_rules' => false,
));


$attrCode = 'rakuten_sync_description';
$attrLabel = 'Synchronize description to Rakuten';
$attrNote = null;
$sort_order = 40;

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
        'source'        => 'eav/entity_attribute_source_boolean',
        'user_defined'  => true,
        'default'       => '0',
        'unique'        => false,
        'sort_order' => $sort_order,
    ));


$installer->endSetup();
