<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_System_Config_Source_Delivery
{
    private $_options = null;
    protected $_attribute;

    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => 'Sofort lieferbar (Lieferzeit 1-4 Werktage)',
                    'value' =>  0
                ),
                array(
                    'label' => 'versandfertig in 3 Werktagen (Lieferzeit 4-6 Werktage)',
                    'value' =>  3
                ),
                array(
                    'label' => 'versandfertig in 5 Werktagen (Lieferzeit 6-8 Werktage)',
                    'value' =>  5
                ),
                array(
                    'label' => 'versandfertig in 7 Werktagen (Lieferzeit 8-10 Werktage)',
                    'value' =>  7
                ),
                array(
                    'label' => 'versandfertig in 10 Werktagen (Lieferzeit 10-15 Werktage)',
                    'value' =>  10
                ),
                array(
                    'label' => 'versandfertig in 15 Werktagen (Lieferzeit 15-20 Werktage)',
                    'value' =>  15
                ),
                array(
                    'label' => 'versandfertig in 20 Werktagen (Lieferzeit 20-30 Werktage)',
                    'value' =>  20
                ),
                array(
                    'label' => 'versandfertig in 30 Werktagen (Lieferzeit 30-40 Werktage)',
                    'value' =>  30
                ),
                array(
                    'label' => 'versandfertig in 40 Werktagen (Lieferzeit 40-50 Werktage)',
                    'value' =>  40
                ),
                array(
                    'label' => 'versandfertig in 50 Werktagen (Lieferzeit 50-60 Werktage)',
                    'value' =>  50
                ),
                array(
                    'label' => 'versandfertig in 60 Werktagen (Lieferzeit länger als 3 Monate)',
                    'value' =>  60
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