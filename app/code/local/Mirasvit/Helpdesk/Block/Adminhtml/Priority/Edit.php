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



class Mirasvit_Helpdesk_Block_Adminhtml_Priority_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'priority_id';
        $this->_controller = 'adminhtml_priority';
        $this->_blockGroup = 'helpdesk';

        $this->_updateButton('save', 'label', Mage::helper('helpdesk')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('helpdesk')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('helpdesk')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action + 'back/edit/');
            }
        ";
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getPriority()
    {
        if (Mage::registry('current_priority') && Mage::registry('current_priority')->getId()) {
            return Mage::registry('current_priority');
        }
    }

    public function getHeaderText()
    {
        if ($priority = $this->getPriority()) {
            return Mage::helper('helpdesk')->__("Edit Priority '%s'", $this->htmlEscape($priority->getName()));
        } else {
            return Mage::helper('helpdesk')->__('Create New Priority');
        }
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $switcher = $this->getLayout()->createBlock('adminhtml/store_switcher');
        $switcher->setUseConfirm(false)->setSwitchUrl(
            $this->getUrl('*/*/*/', array('store' => null, '_current' => true))
        );
        $html = $switcher->toHtml().$html;

        return $html;
    }

    /************************/
}
