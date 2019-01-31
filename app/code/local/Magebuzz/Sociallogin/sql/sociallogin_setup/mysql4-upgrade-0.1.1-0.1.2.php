<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
$installer = $this;
$installer->startSetup();
$installer->setCustomerAttributes(
	array(
		'magebuzz_sociallogin_lid' => array(
			'type'  =>  'text',
			'visible' => false,
			'required'  =>  false,
			'user_defined'  => false,
		),
		'magebuzz_sociallogin_ltoken' =>array(
			'type'  =>  'text',
			'visible' =>  false,
			'required'  => false,
			'user_defined'  =>  false,
		)
	)
);

	$installer->installCustomerAttributes();

	$installer->endSetup();