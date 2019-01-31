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



class Mirasvit_Helpdesk_Block_Adminhtml_Message_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_helpdesk/message/edit/tab/general.phtml');
    }

    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    protected function getGeneralForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        /** @var Mirasvit_Helpdesk_Model_Message $message */
        $message = Mage::registry('current_message');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('Message Summary')));

        $fieldset->addField('message_id', 'hidden', array(
            'name' => 'message_id',
            'value' => $message->getId(),
        ));

        $text = $message->getBody();
        if ($message->getBodyFormat() == Mirasvit_Helpdesk_Model_Config::FORMAT_HTML) {
            $fieldset->addField('reply', 'editor', array(
                'label' => Mage::helper('helpdesk')->__('Message'),
                'required' => false,
                'name' => 'reply',
                'value' => $text,
                'config' => Mage::getSingleton('helpdesk/config_wysiwyg')->getConfig(),
                'wysiwyg' => true,
                'style' => 'height:15em',
            ));
        } else {
            $fieldset->addField('reply', 'textarea', array(
                'label' => Mage::helper('helpdesk')->__('Message'),
                'required' => false,
                'name' => 'reply',
                'value' => $text,
            ));
        }

        return $form;
    }

    public function getMessage()
    {
        return Mage::registry('current_message');
    }
}
