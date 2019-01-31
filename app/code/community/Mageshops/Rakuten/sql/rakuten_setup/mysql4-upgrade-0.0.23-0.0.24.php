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


// $installer->getConnection()->dropTable($installer->getTable('rakuten/rakuten_product_image'));


$installer->run("
DROP TABLE IF EXISTS {$this->getTable('rakuten/rakuten_product_image')};
");


$installer->endSetup();
