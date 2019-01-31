<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_System_Config_Source_ShippingGroup
{
    private $_options = null;
    protected $_attribute;

    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => '1',
                    'value' =>  1
                ),
                array(
                    'label' => '2',
                    'value' =>  2
                ),
                array(
                    'label' => '3',
                    'value' =>  3
                ),
                array(
                    'label' => '4',
                    'value' =>  4
                ),
                array(
                    'label' => '5',
                    'value' =>  5
                ),
                array(
                    'label' => '6',
                    'value' =>  6
                ),
                array(
                    'label' => '7',
                    'value' =>  7
                ),
                array(
                    'label' => '8',
                    'value' =>  8
                ),
                array(
                    'label' => '9',
                    'value' =>  9
                ),
                array(
                    'label' => '10',
                    'value' =>  10
                ),
                array(
                    'label' => '11',
                    'value' =>  11
                ),
                array(
                    'label' => '12',
                    'value' =>  12
                ),
                array(
                    'label' => '13',
                    'value' =>  13
                ),
                array(
                    'label' => '14',
                    'value' =>  14
                ),
                array(
                    'label' => '15',
                    'value' =>  15
                ),
                array(
                    'label' => '16',
                    'value' =>  16
                ),
                array(
                    'label' => '17',
                    'value' =>  17
                ),
                array(
                    'label' => '18',
                    'value' =>  18
                ),
                array(
                    'label' => '19',
                    'value' =>  19
                ),
                array(
                    'label' => '20',
                    'value' =>  20
                ),
            );
        }
        return $this->_options;
    }

    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        if (sizeof($options) > 0) {
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    return $option['label'];
                }
            }
        }

        if (isset($options[$value])) {
            return $options[$value];
        }

        return false;
    }
}