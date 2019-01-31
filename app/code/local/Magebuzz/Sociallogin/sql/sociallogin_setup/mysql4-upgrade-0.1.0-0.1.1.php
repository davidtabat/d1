<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
$installer = $this;

	$installer->startSetup();

	$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

	$entityTypeId     = $setup->getEntityTypeId('customer');
	$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
	$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

	$installer->addAttribute("customer", "sociallogin",  array(
		"type"     => "varchar",
		"backend"  => "",
		"label"    => "Social Login",
		"input"    => "text",
		"source"   => "",
		"visible"  => true,
		"required" => false,
		"default" => "",
		"frontend" => "",
		"unique"     => false,
		"note"       => "Social Login"

	));

	$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "sociallogin");


	$setup->addAttributeToGroup(
		$entityTypeId,
		$attributeSetId,
		$attributeGroupId,
		'sociallogin',
		'999'
	);

	$used_in_forms=array();

	$used_in_forms[]="adminhtml_customer";
	$used_in_forms[]="checkout_register";
	$used_in_forms[]="customer_account_create";
	$used_in_forms[]="customer_account_edit";
	$used_in_forms[]="adminhtml_checkout";
	$attribute->setData("used_in_forms", $used_in_forms)
		->setData("is_used_for_customer_segment", true)
		->setData("is_system", 0)
		->setData("is_user_defined", 1)
		->setData("is_visible", 1)
		->setData("sort_order", 100)
	;
	$attribute->save();



	$installer->endSetup();