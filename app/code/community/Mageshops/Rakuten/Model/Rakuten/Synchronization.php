<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 */

class Mageshops_Rakuten_Model_Rakuten_Synchronization extends Mageshops_Rakuten_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('rakuten/rakuten_synchronization'); 
    }

    public function toString($format = '')
    {
        return 'Time: ' . date('Y-m-d H:i:s', $this->getTime()) . '; Locked: ' . $this->getLocked()
            . '; Percent: ' . $this->getPercent() . '; Message: ' . $this->getMessage();
    }
}