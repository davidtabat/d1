<?php

class Trustedshops_Trustedshops_Model_System_Config_Source_Variant
{
    const VARIANT_HIDE = 'hide';
    const VARIANT_REVIEWS = 'reviews';
    const VARIANT_NO_REVIEWS = 'default';

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
            array('value' => self::VARIANT_REVIEWS, 'label' => $this->getHelper()->__('Display Trustbadge with review stars')),
            array('value' => self::VARIANT_NO_REVIEWS, 'label' => $this->getHelper()->__('Display Trustbadge without review stars')),
            array('value' => self::VARIANT_HIDE, 'label' => $this->getHelper()->__("Don't show Trustbadge")),
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::VARIANT_REVIEWS => $this->getHelper()->__('Display Trustbadge with reviews'),
            self::VARIANT_NO_REVIEWS => $this->getHelper()->__('Display Trustbadge without reviews'),
            self::VARIANT_HIDE => $this->getHelper()->__("Don't show Trustbadge"),
        );
    }
}