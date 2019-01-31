<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Rakuten Order Item model class
 */
class Mageshops_Rakuten_Model_Rakuten_Order_Item extends Mageshops_Rakuten_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('rakuten/rakuten_order_item');
    }

    public function getTaxPercent()
    {
        $tax = array(
            1 	=> 	19,
            2 	=> 	7,
            3 	=> 	0,
            4 	=> 	10.7,
            10 	=> 	10,
            11 	=> 	12,
            12 	=> 	20,
        );

        if (isset($tax[$this->getTax()])) {
            return $tax[$this->getTax()];
        }

        return 19;
    }
}
