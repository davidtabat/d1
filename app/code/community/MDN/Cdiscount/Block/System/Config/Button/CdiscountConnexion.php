<?php

class MDN_Cdiscount_Block_System_Config_Button_CdiscountConnexion extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('Cdiscount/Main/checkConnexion');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel($this->__('Test connection'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}