<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Message_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'message_id';
        $this->_controller = 'adminhtml_message';
        $this->_blockGroup = 'helpdesk';
        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');
        if ($this->getMessage()) {
            $this->_addButton('update', array(
                'label' => Mage::helper('helpdesk')->__('Update'),
                'id' => 'saveMessageBtn',
                'onclick' => 'saveEdit(this)',
                'class' => 'save saveMessageBtn',
            ), -100);
        }

        $this->_addButton('back', array(
            'label' => Mage::helper('adminhtml')->__('Back'),
            'onclick' => 'setLocation(\''.Mage::helper('adminhtml')->getUrl('*/helpdesk_ticket/edit', array('id' => $this->getMessage()->getTicketId())).'\')',
            'class' => 'back',
            'level' => -1,
        ));

        $this->_formScripts[] = "
            function saveEdit(clicked){
                clicked.disabled = true;
                isAllowDraft=false;
                if(!editForm.submit($('edit_form').action)) {
                    clicked.disabled = false;
                }
            }

            function checkWYSIWYG(checked) {
                if(tinyMCE.activeEditor != null && document.getElementsByClassName('mceEditor').length) {
                    tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent() +
                        document.getElementById('htmltemplate-' + checked.value).innerHTML);

                }
            }
            function switchVisible(controlId, status) {
                // Ensure that cancellation of copy destroys the addresses
                if(status != 'true') {
                    if(controlId == 'allowCC') {
                        $('cc').value = '';
                    } else {
                        $('bcc').value = '';
                    }
                }

                // Switch visibility constant
                $(controlId).value = status;

                // emulate event firing as FormDependency Controller observes events not values
                if(document.createEventObject) {
                    var evt = document.createEventObject();
                    $(controlId).fireEvent('onchange', evt);
                } else {
                    var evt = document.createEvent('HTMLEvents');
                    evt.initEvent('change', false, true);
                    $(controlId).dispatchEvent(evt);
                }
            }
        ";

        return $this;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('helpdesk/config_wysiwyg')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getMessage()
    {
        if (Mage::registry('current_message') && Mage::registry('current_message')->getId()) {
            return Mage::registry('current_message');
        }
    }

    public function getHeaderText()
    {
        return Mage::helper('helpdesk')->__('Edit Message');
    }

    /************************/
}
