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


$attrCode = 'rakuten_category_layout';
$attrLabel = 'Show Products in Category';
$attrNote = null;
$sort_order = 50;
$attrGroupName = 'Rakuten Data';


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
        'source'        => 'rakuten/system_config_source_categoryLayout',
        'user_defined'  => true,
        'default'       => '3',
        'unique'        => false,
        'sort_order' => $sort_order,
    ));


$installer->endSetup();
