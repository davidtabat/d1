<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'imported_at',
    array(
        'label' => 'Imported At',
        'group' => 'General',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'show_on_frontend' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_price_rules' => false,
        'visible' => false,
        'required' => false,
        'user_defined' => true,
        'is_user_defined' => false,
        'searchable' => false,
        'filterable' => false,
        'visible_on_front' => false,
        'default' => null,
        'type' => 'datetime',
        'input' => 'date',
    )
);

$installer->endSetup();