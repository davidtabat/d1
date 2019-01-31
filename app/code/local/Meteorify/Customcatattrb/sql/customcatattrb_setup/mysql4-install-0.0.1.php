<?php
$installer = $this;
$installer->startSetup();
$attribute  = array(
    'type' => 'text',
    'label'=> 'erweiterte Beschriftung oben',
    'input' => 'textarea',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'wysiwyg_enabled' => true,
    'visible_on_front' => true,
    'is_html_allowed_on_front' => true,
    'user_defined' => true,
    'default' => "",
    'group' => "General"
);
$installer->addAttribute('catalog_category', 'custom_attribute2', $attribute);
$installer->endSetup();
?>