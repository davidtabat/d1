<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Cdiscount_Block_Index_Tab_Brands_Add extends Mage_Adminhtml_Block_Widget_Form {
    
    /**
     * Prepare layout
     * 
     * @return type 
     */
    protected function _prepareLayout(){
        
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save'),
                    'onclick'   => 'addBrandForm.submit()',
                    'class' => 'save'
                ))
        );            
        
        return parent::_prepareLayout();
        
    }
    
    protected function _prepareForm(){
        
        $form = new Varien_Data_Form(array(
                'id' => 'addBrandForm',
                'name' => 'addBrandForm',
                'action' => $this->getUrl('Cdiscount/Brands/Add'),
                'method' => 'post',
                'onsubmit' => ''
        ));              
        
        $mainFieldset = $form->addFieldset(
                'main_fieldset',
                array(
                    'legend' => $this->__('Add Brand')
                )
        );                             
        
        // brand code
        $mainFieldset->addField(
                'mpb_code',
                'text',
                array(
                    'name' => 'mpb_code',
                    'value' => '',
                    'label' => $this->__('Code'),
                    'required' => true
                )
        );
        
        // brand label
        $mainFieldset->addField(
                'mpb_label',
                'text',
                array(
                    'name' => 'mpb_label',
                    'value' => '',
                    'label' => $this->__('Label'),
                    'required' => true
                )
        );                
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
        
    }
    
}
