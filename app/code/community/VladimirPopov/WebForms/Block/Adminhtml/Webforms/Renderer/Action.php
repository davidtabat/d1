<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2014 Vladimir Popov
 */
class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$store = Mage::app()->getStore(Mage::getStoreConfig('webforms/general/preview_store'));
    	if(!$store->getId()){
    		$store = Mage::app()->getStore();
    	}
        $href = $store->getUrl('webforms', array('_current' => false, 'id' => $row->getId()));
        return '<a href="' . $href . '" target="_blank">' . $this->__('Preview') . '</a>';
    }
}
