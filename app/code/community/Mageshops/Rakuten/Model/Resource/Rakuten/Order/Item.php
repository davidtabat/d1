<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

// Support for magento 1.5
class Mageshops_Rakuten_Model_Resource_Rakuten_Order_Item extends Mage_Core_Model_Mysql4_Abstract
// Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('rakuten/rakuten_order_item', 'item_id');
        $this->_isPkAutoIncrement = false;
    }
}