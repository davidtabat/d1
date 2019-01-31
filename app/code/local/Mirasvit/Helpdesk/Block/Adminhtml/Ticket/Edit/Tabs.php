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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ticket_tabs');
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $ticket = Mage::registry('current_ticket');
        if ($ticket && !$ticket->getStoreId()) {
            $this->addTab('store', array(
                'label' => Mage::helper('helpdesk')->__('General'),
                'title' => Mage::helper('helpdesk')->__('General'),
                'content' => $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_store')->toHtml(),
            ));

            return parent::_beforeToHtml();
        }

        $this->addTab('general_section', array(
            'label' => Mage::helper('helpdesk')->__('General'),
            'title' => Mage::helper('helpdesk')->__('General'),
            'content' => $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_general')->toHtml(),
        ));
        $this->addTab('additional_section', array(
            'label' => Mage::helper('helpdesk')->__('Additional'),
            'title' => Mage::helper('helpdesk')->__('Additional'),
            'content' => $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_additional')->toHtml(),
        ));
        $this->addTab('followup_section', array(
            'label' => Mage::helper('helpdesk')->__('Follow Up'),
            'title' => Mage::helper('helpdesk')->__('Follow Up'),
            'content' => $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_followup')->toHtml(),
        ));

        if ($ticket && $ticket->getId()) {
            /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_Other $otherBlock */
            $otherBlock = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_other');
            $otherBlockHtml = $otherBlock->toHtml();
            $ticketsNumber = $otherBlock->getFormattedNumberOfTickets();
            $this->addTab('other', array(
                'label' => Mage::helper('helpdesk')->__('Other tickets (%s)', $ticketsNumber),
                'title' => Mage::helper('helpdesk')->__('Other tickets (%s)', $ticketsNumber),
                'content' => $otherBlockHtml,
            ));
        }
        if (!$ticket || !$ticket->getId()) {
            return parent::_beforeToHtml();
        }


        /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_Other $otherBlock */
        $otherBlock = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_other');
        $otherBlockHtml = $otherBlock->toHtml();
        $ticketsNumber = $otherBlock->getFormattedNumberOfTickets();
        $this->addTab('other', array(
            'label' => Mage::helper('helpdesk')->__('Other tickets (%s)', $ticketsNumber),
            'title' => Mage::helper('helpdesk')->__('Other tickets (%s)', $ticketsNumber),
            'content' => $otherBlockHtml,
        ));



        if (Mage::helper('mstcore')->isModuleEnabled('Mirasvit_Rma') && $ticket->getOrderId()) {
                //we use such way to make it work with old versions of RMA out of box
                Mage::app()->getRequest()->setParam('order_id', $ticket->getOrderId());
                /** @var Mirasvit_Rma_Block_Adminhtml_Sales_Order_View_Tabs_Rma $rmaBlock */
                $rmaBlock = $this->getLayout()->createBlock('rma/adminhtml_sales_order_view_tabs_rma');
                $rmaBlockHtml = $rmaBlock->toHtml();
                $this->addTab('rma', array(
                    'label' => $rmaBlock->getTabLabel(),
                    'title' => $rmaBlock->getTabTitle(),
                    'content' => $rmaBlockHtml,
                ));
        }

        return parent::_beforeToHtml();
    }

    /************************/
}
