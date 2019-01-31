<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_Payment_Method_Rakuten extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'nnrakuten';
    protected $_canAuthorize    = true;
    protected $_canCapture      = true;
    protected $_canUseInternal  = true;
    protected $_canUseCheckout  = false;
}
