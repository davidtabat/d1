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
class Mageshops_Rakuten_Model_Rakuten_Request extends Mageshops_Rakuten_Model_Abstract
{
    const STATUS_ADDED          = 0;
    const STATUS_STARTED        = 1;
    const STATUS_FINISHED       = 2;
    const STATUS_ERROR          = 100;
    const STATUS_ERROR_NON_XML  = 100;
    const STATUS_ERROR_PRODUCT  = 100;
    const STATUS_ERROR_ORDER    = 100;


    protected function _construct()
    {
        $this->_init('rakuten/rakuten_request');
    }

    public function getStatuses()
    {
        $options = array(
            self::STATUS_ADDED          => 'Added',
            self::STATUS_STARTED        => 'Started',
            self::STATUS_FINISHED       => 'Finished',
            self::STATUS_ERROR          => 'Error',
//            self::STATUS_ERROR_NON_XML  => 'Error',
//            self::STATUS_ERROR_PRODUCT  => 'Error',
//            self::STATUS_ERROR_ORDER    => 'Error',
        );

        return $options;
    }
}
