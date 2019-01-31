<?php
/**
 * Class Cmsmart_CalculateShipping_Model_System_Config_Source_Position
 * @author Pham Hong Thanh
 * @email thanhpham0990@gmail.com
 *
 */

class Cmsmart_CalculateShipping_Model_System_Config_Source_Position{

    /**
     * Retrieve array position of calculate shipping form
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'left', 'label'=>Mage::helper('calculateshipping')->__('Left')),
            array('value' => 'right', 'label'=>Mage::helper('calculateshipping')->__('Right')),
            array('value' => 'content', 'label'=>Mage::helper('calculateshipping')->__('Content')),
            array('value' => 'popup', 'label'=>Mage::helper('calculateshipping')->__('Popup')),
        );
    }
}