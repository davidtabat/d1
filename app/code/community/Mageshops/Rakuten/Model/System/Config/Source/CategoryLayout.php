<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_System_Config_Source_CategoryLayout extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 2, 'label'=>Mage::helper('rakuten')->__('Products from this category')),
            array('value' => 1, 'label'=>Mage::helper('rakuten')->__('Products from this category and subcategories')),
            array('value' => 0, 'label'=>Mage::helper('rakuten')->__('No products, only description')),
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
            0 => 'no_products',
            1 => 'child_products',
            2 => 'products',
        );
    }

    /**
     * Retrieve all options array for category attribute
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('rakuten')->__('Take from settings'),
                    'value' =>  3
                ),
                array(
                    'label' => Mage::helper('rakuten')->__('Products from this category'),
                    'value' =>  2
                ),
                array(
                    'label' => Mage::helper('rakuten')->__('Products from this category and subcategories'),
                    'value' =>  1
                ),
                array(
                    'label' => Mage::helper('rakuten')->__('No products, only description'),
                    'value' =>  0
                ),
            );
        }
        return $this->_options;
    }
}
