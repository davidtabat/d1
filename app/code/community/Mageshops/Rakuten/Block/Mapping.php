<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Mapping extends Mage_Adminhtml_Block_Template
{
    public function getButtonHtml()
    {
        return $this->getChildHtml('save_mapping_button');
    }

    protected function _prepareLayout()
    {
        $helper = Mage::helper('rakuten');

        $this->setChild('save_mapping_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Save Mapping'),
                    'element_name' => 'action',
                    'type'      => 'submit',
                    'value'     => 'save_mapping',
                ))
        );
    }

    public function getActionUrl()
    {
        return $this->getUrl('*/*/saveMapping', array('_current' => true));
    }

    public function getAttributes()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->getItems();

        return $attributes;
    }

    public function isOptionAvailable($code, $attribute)
    {
        if ($code == 'product_art_no') {
            if (!$attribute->getIsRequired() || !$attribute->getIsUnique()) {
                return false;
            }
        }
        if ($code == 'price' || $code == 'price_reduced') {
            if ($attribute->getFrontendInput() != 'price') {
                return false;
            }
        }
        if ($code == 'default_image') {
            if ($attribute->getFrontendInput() != 'media_image') {
                return false;
            }
        }

        return true;
    }

    public function hasEmptyValue($code)
    {
        switch ($code) {
            case 'product_art_no':
            case 'price':
                return false;
        }

        return true;
    }
}
