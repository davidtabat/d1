<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_System_Config_Source_Attributeset
{
    public function toOptionArray()
    {
        $entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityType);

        $str = Mage::helper('rakuten')->__('Please, select Attribute Set');
        $optionArray = array(
            array(
                'value' => 0,
                'title' => $str,
                'label' => $str,
            ),
        );
        foreach ($collection as $id => $attributeSet) {
            $name = $attributeSet->getAttributeSetName();
            $optionArray[] = array(
                'value' => $id,
                'title' => $name,
                'label' => $name,
            );
        }

        return $optionArray;
    }
}
