<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 */

class Mageshops_Rakuten_Model_Resource_Rakuten_Synchronization extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('rakuten/rakuten_synchronization', 'entity_id'); 
    }
}