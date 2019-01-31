<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Rakuten Order model class
 */
class Mageshops_Rakuten_Model_Rakuten_Order extends Mageshops_Rakuten_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('rakuten/rakuten_order');
    }

    public function loadByOrderNo($orderNo)
    {
        return $this->load($orderNo, 'order_no');
    }

    public function getOrderItems()
    {
        $items = Mage::getModel('rakuten/rakuten_order_item')->getCollection()
            ->addFieldToFilter('rakuten_order_id', array('eq' => $this->getRakutenOrderId()));

        return $items;
    }
}
