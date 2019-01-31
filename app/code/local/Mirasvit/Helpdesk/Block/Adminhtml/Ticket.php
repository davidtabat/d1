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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_ticket';
        $this->_blockGroup = 'helpdesk';
        if (Mage::registry('is_archive')) {
            $this->_headerText = Mage::helper('helpdesk')->__('Tickets Archive');
        } else {
            $this->_headerText = Mage::helper('helpdesk')->__('Tickets');
        }
        $this->_addButtonLabel = Mage::helper('helpdesk')->__('Create New Ticket');
        parent::__construct();

        $search = Mage::app()->getLayout()->createBlock('core/template')->setTemplate('mst_helpdesk/ticket/search/form.phtml');
        $this->setChild('search_form', $search);

        if (!Mage::app()->isSingleStoreMode()) {
            $switcher = Mage::app()->getLayout()->createBlock('adminhtml/store_switcher');
            $switcher->setUseConfirm(false)->setSwitchUrl(
                $this->getUrl('*/*/*/', array('store' => -1, '_current' => true)) //small hack here. i don't see other way to solve this.
            );
            if (!$this->getRequest()->getParam('store')) {
                $adminUser = Mage::getSingleton('admin/session')->getUser();
                $helpdeskUser = Mage::helper('helpdesk')->getHelpdeskUser($adminUser);
                $this->getRequest()->setParam('store', $helpdeskUser->getStoreId());
            }
            $this->setChild('store_switcher', $switcher);
        }

        $this->setTemplate('mst_helpdesk/ticket/grid/container.phtml');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
