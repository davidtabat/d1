<?php

class Trustedshops_Trustedshops_Model_System_Config_Source_Mode
{
    const MODE_STANDARD = 'standard';
    const MODE_EXPERT = 'expert';

    /**
     * @var Trustedshops_Trustedshops_Helper_Data
     */
    protected $_helper;

    /**
     * @return Trustedshops_Trustedshops_Helper_Data
     */
    public function getHelper()
    {
        if (is_null($this->_helper))
        {
            $this->_helper = Mage::helper('trustedshops');
        }
        return $this->_helper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::MODE_STANDARD, 'label' => $this->getHelper()->__('Standard')),
            array('value' => self::MODE_EXPERT, 'label' => $this->getHelper()->__('Expert')),
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::MODE_STANDARD => $this->getHelper()->__('Standard'),
            self::MODE_EXPERT => $this->getHelper()->__('Expert'),
        );
    }
}