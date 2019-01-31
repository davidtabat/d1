<?php
/*
* @copyright   Copyright (c) 2015 www.magebuzz.com
*/
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', 'provider', array(
	'input'         => 'text',
	'type'          => 'varchar',
	'label'         => 'provider',
	'visible'       => 1,
	'required'      => 0,
	'user_defined' => 1,
));

$installer->endSetup();