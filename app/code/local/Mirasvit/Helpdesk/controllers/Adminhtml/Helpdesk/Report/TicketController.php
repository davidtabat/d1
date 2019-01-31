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



class Mirasvit_Helpdesk_Adminhtml_Helpdesk_Report_TicketController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('helpdesk');

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Tickets Report'));

        $this->_showLastExecutionTime();

        $this->_initAction();

        $this->renderLayout();

        if ($this->getRequest()->getParam('export')) {
            $grid = $this->getLayout()->getBlock('container')->getGrid();

            if ($this->getRequest()->getParam('export') == 'csv') {
                $fileName = 'report.csv';
                $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
            } elseif ($this->getRequest()->getParam('export') == 'xml') {
                $fileName = 'report.xml';
                $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
            }
        }
    }

    protected function _showLastExecutionTime()
    {
        $flag = Mage::getModel('reports/flag')
            ->setReportFlagCode(Mirasvit_Helpdesk_Model_Resource_Report_Ticket::FLAG_CODE)->loadSelf();

        $updatedAt = $flag->hasData()
            ?  Mage::getSingleton('core/date')->date('M, d Y h:i A', strtotime($flag->getLastUpdate()))
            : 'undefined';

        $refreshLifetimeLink = $this->getUrl('*/*/refreshLifetime');
        $refreshRecentLink = $this->getUrl('*/*/refreshRecent');

        Mage::getSingleton('adminhtml/session')->addNotice(
            Mage::helper('adminhtml')->__('Last updated: %s. To refresh recent statistics, click <a href="%s">here</a> (refresh lifetime statistic <a href="%s">here</a>)',
                $updatedAt,
                $refreshRecentLink,
                $refreshLifetimeLink
            )
        );

        return $this;
    }

    public function refreshRecentAction()
    {
        $flag = Mage::getModel('reports/flag')
            ->setReportFlagCode(Mirasvit_Helpdesk_Model_Resource_Report_Ticket::FLAG_CODE)->loadSelf();

        $from = $flag->hasData()
            ? Mage::app()->getLocale()->storeDate(
                0, new Zend_Date($flag->getLastUpdate(), Varien_Date::DATETIME_INTERNAL_FORMAT), true
            )
            : null;

        try {
            Mage::getResourceModel('helpdesk/report_ticket')->aggregate($from);

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Recent statistics was successfully updated'));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer('*/*/*');

        return $this;
    }

    public function refreshLifetimeAction()
    {
        try {
            Mage::getResourceModel('helpdesk/report_ticket')->aggregate();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Lifetime statistics was successfully updated'));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer('*/*/*');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdesk/report');
    }
}
