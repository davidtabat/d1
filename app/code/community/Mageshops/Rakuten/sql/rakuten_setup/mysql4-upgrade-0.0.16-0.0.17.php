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


$attrCode = 'rakuten_custom_price';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Rakuten Price Difference';
$attrComment = null;
$attrNote = 'Price change for product exported to Rakuten. Is defined as difference from price/special price.
 Can be set as absolute value or percent: 10, -10, +10%, -10%';
$sort_order = 100;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => $sort_order,
    'type' => 'varchar',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'comment' => $attrComment,
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


$installer->endSetup();
