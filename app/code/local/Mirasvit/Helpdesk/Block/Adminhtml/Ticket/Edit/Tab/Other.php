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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_Other extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid $grid */
    protected $grid;
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    protected function _toHtml()
    {
        $ticket = $this->getTicket();

        /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid $grid */
        $grid = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_grid');
        $this->grid = $grid;
        $grid->setId('helpdesk_grid_internal');
        $grid->setActiveTab('other');
        $customerCondition = $ticket->getCustomerId() ? ' OR main_table.customer_id='.(int) $ticket->getCustomerId() : '';
        $grid->addCustomFilter('(main_table.customer_email = "'.addslashes($ticket->getCustomerEmail()).'"'.$customerCondition.')
            AND ticket_id <> '.$ticket->getId());
        $grid->removeFilter('is_archived');
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setTabMode(true);

        return '<div>'.$grid->toHtml().'</div>';
    }

    public function getFormattedNumberOfTickets()
    {
        if (!$this->grid) {
            return '';
        }

        return $this->grid->getFormattedNumberOfTickets();
    }
}
