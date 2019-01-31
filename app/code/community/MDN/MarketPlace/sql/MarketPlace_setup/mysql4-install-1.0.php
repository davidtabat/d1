<?php


$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_data')}` (
		mp_id INTEGER AUTO_INCREMENT,
		mp_marketplace_id VARCHAR(50),
		mp_product_id INTEGER,
		mp_reference VARCHAR(255),
		mp_exclude BOOLEAN,
		mp_force_qty INTEGER,
		mp_delay INTEGER,
		CONSTRAINT PK_market_place_data PRIMARY KEY(mp_id),
		CONSTRAINT UC_market_place_data UNIQUE(mp_marketplace_id, mp_reference)
    )ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `{$this->getTable('market_place_logs')}` (
		mp_id INTEGER AUTO_INCREMENT,
		mp_date DATETIME,
		mp_marketplace VARCHAR(50),
		mp_is_error BOOLEAN,
		mp_message TEXT,
		CONSTRAINT PK_market_place_logs PRIMARY KEY(mp_id)
	)ENGINE=InnoDB;

");

//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order'); // get orders table name
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');

if ($useEavModel)
{
	$installer->addAttribute('order','marketplace_order_id',array(
        	'type' 		=> 'varchar',
        	'visible' 	=> true,
        	'label'		=> 'MarketPlace order id',
        	'required'  => false,
        	'default'   => ''
	));

        $installer->addAttribute('order_item', 'marketplace_item_id', array(
                'type' => 'varchar',
                'visible' => true,
                'label' => 'Marketplace item id',
                'required' => false,
                'default' => ''
        ));


}
else
{

	$installer->run("

		ALTER TABLE `{$this->getTable('sales_flat_order')}`
		ADD `marketplace_order_id` VARCHAR(50);

	");

        $installer->run("

                ALTER TABLE `{$this->getTable('sales_flat_order_item')}`
                ADD `marketplace_item_id` VARCHAR(50);

         ");

}


$installer->endSetup();

?>
