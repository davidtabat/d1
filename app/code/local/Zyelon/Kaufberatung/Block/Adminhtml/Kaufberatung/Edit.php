<?php

class Zyelon_Kaufberatung_Block_Adminhtml_Kaufberatung_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'kaufberatung';
        $this->_controller = 'adminhtml_kaufberatung';
        
        $this->_updateButton('save', 'label', Mage::helper('kaufberatung')->__('Save User'));
        $this->_updateButton('delete', 'label', Mage::helper('kaufberatung')->__('Delete User'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('kaufberatung_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'kaufberatung_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'kaufberatung_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('kaufberatung_data') && Mage::registry('kaufberatung_data')->getId() ) {
            return Mage::helper('kaufberatung')->__("Edit User Guide '%s'", $this->htmlEscape(Mage::registry('kaufberatung_data')->getName()));
        } else {
            return Mage::helper('kaufberatung')->__('Add User');
        }
    }
}