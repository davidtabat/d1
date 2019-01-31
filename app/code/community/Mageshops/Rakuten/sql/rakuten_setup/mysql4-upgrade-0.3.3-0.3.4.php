<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 * @author 	    Viktors Stepucevs with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
TRUNCATE TABLE {$installer->getTable('rakuten/rakuten_request')};
");

$installer->endSetup();
