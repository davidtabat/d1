<?php

/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 */
// Support for magento 1.5
class Mageshops_Rakuten_Model_Resource_Rakuten_Synchronization_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
// Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('rakuten/rakuten_synchronization');
    }

}
