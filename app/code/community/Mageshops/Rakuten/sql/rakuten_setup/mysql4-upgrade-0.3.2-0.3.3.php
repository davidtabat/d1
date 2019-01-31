<?php
/**
 * @category    Mageshops
 * @package     Mageshops_Rakuten
 * @license     http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2015
 * @author      Viktors Stepucevs with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/** @var $setup Mage_Catalog_Model_Resource_Setup */
$setup = Mage::getResourceModel('catalog/setup', 'core_setup');

$attrCode = 'rakuten_id';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Sync Product to Rakuten Id';
$attrNote = null;
$sort_order = 10;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group'                => $attrGroupName,
    'sort_order'           => $sort_order,
    'type'                 => 'int',
    'backend'              => '',
    'frontend'             => '',
    'label'                => $attrLabel,
    'note'                 => $attrNote,
    'input'                => 'text',
    'class'                => '',
    'source'               => '',
    'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'              => true,
    'required'             => false,
    'user_defined'         => true,
    'default'              => null,
    'visible_on_front'     => false,
    'unique'               => false,
    'is_configurable'      => false,
    'used_for_promo_rules' => false,
));

$attrCode = 'rakuten_status';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Sync Product to Rakuten Status';
$attrNote = null;
$sort_order = 10;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group'                => $attrGroupName,
    'sort_order'           => $sort_order,
    'type'                 => 'varchar',
    'backend'              => '',
    'frontend'             => '',
    'label'                => $attrLabel,
    'note'                 => $attrNote,
    'input'                => 'text',
    'class'                => '',
    'source'               => '',
    'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'              => true,
    'required'             => false,
    'user_defined'         => true,
    'default'              => null,
    'visible_on_front'     => false,
    'unique'               => false,
    'is_configurable'      => false,
    'used_for_promo_rules' => false,
));

$attrCode = 'rakuten_variant_labels';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Sync Product to Rakuten Variant Labels';
$attrNote = null;
$sort_order = 10;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group'                => $attrGroupName,
    'sort_order'           => $sort_order,
    'type'                 => 'varchar',
    'backend'              => '',
    'frontend'             => '',
    'label'                => $attrLabel,
    'note'                 => $attrNote,
    'input'                => 'text',
    'class'                => '',
    'source'               => '',
    'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'              => true,
    'required'             => false,
    'user_defined'         => true,
    'default'              => null,
    'visible_on_front'     => false,
    'unique'               => false,
    'is_configurable'      => false,
    'used_for_promo_rules' => false,
));

$installer->endSetup();

$attrCode = 'rakuten_variants';
$attrGroupName = 'Rakuten Data';
$attrLabel = 'Sync Product to Rakuten Variants';
$attrNote = null;
$sort_order = 10;

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group'                => $attrGroupName,
    'sort_order'           => $sort_order,
    'type'                 => 'varchar',
    'backend'              => '',
    'frontend'             => '',
    'label'                => $attrLabel,
    'note'                 => $attrNote,
    'input'                => 'text',
    'class'                => '',
    'source'               => '',
    'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'              => true,
    'required'             => false,
    'user_defined'         => true,
    'default'              => null,
    'visible_on_front'     => false,
    'unique'               => false,
    'is_configurable'      => false,
    'used_for_promo_rules' => false,
));
