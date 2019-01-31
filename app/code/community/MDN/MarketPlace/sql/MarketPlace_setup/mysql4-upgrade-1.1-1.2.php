<?php

$installer = $this;

$installer->startSetup();

//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order'); // get orders table name
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');

if ($useEavModel)
{
	$installer->addAttribute('order','from_site',array(
        	'type' 		=> 'varchar',
        	'visible' 	=> true,
        	'label'		=> 'From Site',
        	'required'      => false,
        	'default'       => ''
	));

}
else
{

	$installer->run("

		ALTER TABLE `{$this->getTable('sales_flat_order')}`
		ADD `from_site` VARCHAR(50);

	");

}

$installer->endSetup();

?>
