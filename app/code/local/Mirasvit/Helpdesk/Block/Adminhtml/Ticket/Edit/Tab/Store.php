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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_Store extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        /** @var Mirasvit_Helpdesk_Model_Ticket $ticket */
        $ticket = Mage::registry('current_ticket');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('Select the store')));
        if ($ticket->getId()) {
            $fieldset->addField('ticket_id', 'hidden', array(
                'name' => 'ticket_id',
                'value' => $ticket->getId(),
            ));
        }

        $field = $fieldset->addField('store_id', 'select', array(
            'name' => 'store_id',
            'label' => Mage::helper('catalog')->__('Store'),
            'title' => Mage::helper('catalog')->__('Store'),
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false),
            'required' => true,
        ));

        return parent::_prepareForm();
    }
}
