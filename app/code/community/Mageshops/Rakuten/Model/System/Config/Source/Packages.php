<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2015
 * @author 	    Kristaps Rjabovs with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_System_Config_Source_Packages
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> '1'),
            array('value' => 5, 'label'=> '5'),
            array('value' => 10, 'label'=> '10'),
            array('value' => 25, 'label'=> '25'),
            array('value' => 50, 'label'=> '50'),
            array('value' => 100, 'label'=> '100'),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            1 => '1',
            5 => '5',
            10 => '10',
            25 => '25',
            50 => '50',
            10 => '100',

        );
    }
}