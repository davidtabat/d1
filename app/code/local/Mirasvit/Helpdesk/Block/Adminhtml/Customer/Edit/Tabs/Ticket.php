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



class Mirasvit_Helpdesk_Block_Adminhtml_Customer_Edit_Tabs_Ticket extends Mage_Adminhtml_Block_Widget
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid $grid */
    protected $grid;
    protected $gridHtml;
    protected function _prepareLayout()
    {
        $customer = Mage::registry('current_customer');
        if (!$this->getId() || !$customer) {
            return;
        }
        $id = $this->getId();
        $grid = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_grid');
        // $grid->addCustomFilter('customer_id', $id);
        $grid->addCustomFilter('main_table.customer_email = "'.addslashes($customer->getEmail()).'" OR main_table.customer_id='.(int) $id);
        $grid->setId('helpdesk_grid_customer');
        $grid->removeFilter('is_archived');
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setTabMode(true);
        $grid->setActiveTab('tickets');
        $this->grid = $grid;
        $this->gridHtml = $this->grid->toHtml();

        return parent::_prepareLayout();
    }

    public function getTabLabel()
    {
        return Mage::helper('helpdesk')->__('Help Desk Tickets (%s)', $this->grid->getFormattedNumberOfTickets());
    }

    public function getTabTitle()
    {
        return Mage::helper('helpdesk')->__('Help Desk Tickets');
    }

    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function isHidden()
    {
        return false;
    }

    protected function _toHtml()
    {
        $customer = Mage::registry('current_customer');
        if (!$this->getId() || !$customer) {
            return '';
        }
        $id = $this->getId();
        $ticketNewUrl = $this->getUrl('adminhtml/helpdesk_ticket/add', array('customer_id' => $id));

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\''.$ticketNewUrl.'\'')
            ->setLabel($this->__('Create ticket for this customer'));

        return '<div>'.$button->toHtml().'<br><br>'.$this->gridHtml.'</div>';
    }
}
