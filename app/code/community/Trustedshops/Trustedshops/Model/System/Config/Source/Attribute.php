<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Model_System_Config_Source_Attribute
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAvailableAttributes();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getAvailableAttributes(true);
    }

    /**
     * @param bool $asArray
     * @return array
     */
    protected function getAvailableAttributes($asArray = false)
    {
        $availableAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setOrder('frontend_label', 'asc')
            ->load();
        
        $attributes = array();
        if ($asArray) {
            $attributes[0] = Mage::helper('trustedshops')->__('-- Please Select --');
        } else {
            $attributes[] = array(
                'value' => 0,
                'label' => Mage::helper('trustedshops')->__('-- Please Select --')
            );
        }

        foreach ($availableAttributes as $_attribute) {
            $label = $_attribute->getFrontendLabel();
            if (empty($label)) {
                continue;
            }
            if ($asArray) {
                $attributes[$_attribute->getAttributeCode()] = $_attribute->getFrontendLabel();
            } else {
                $attributes[] = array(
                    'value' => $_attribute->getAttributeCode(),
                    'label' => $_attribute->getFrontendLabel()
                );
            }
        }

        return $attributes;
    }
}