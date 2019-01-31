<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Rakuten_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Rakuten Orders'))
            ->_title($this->__('Manage Rakuten Orders'));

        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function updateAction()
    {
        try {
//            Mage::getModel('rakuten/order')->syncAllRakutenOrders();
            Mage::getModel('rakuten/order')->syncRakutenOrders();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Orders were successfully fetched from your Rakuten account.'));
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }

    public function syncFromRakutenAction()
    {
        $rakutenOrderId = $this->getRequest()->get('order_id', false);

        try {

            if (empty($rakutenOrderId)) {
                Mage::throwException($this->__('Invalid Order ID.'));
            }

            Mage::getModel('rakuten/order')->syncOrderFromRakuten($rakutenOrderId);
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order was successfully synchronized.'));

        } catch (Exception $e) {

            Mage::logException($e);
            $this->_getSession()->addError('Error '.$e->getMessage());

        }

        $this->_redirect('*/*');
    }

    public function massSyncFromRakutenAction()
    {
        $rakutenOrderIds = $this->getRequest()->getParam('rakuten_order_ids', array());

        try {

            foreach ($rakutenOrderIds as $id) {
                Mage::getModel('rakuten/order')->syncOrderFromRakuten($id);
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Orders were successfully synchronized.'));

        } catch (Exception $e) {

            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());

        }

        $this->_redirect('*/*');
    }

    public function syncToRakutenAction()
    {
        $rakutenOrderId = $this->getRequest()->get('order_id', false);

        try {

            if (empty($rakutenOrderId)) {
                Mage::throwException($this->__('Invalid Order ID.'));
            }

            Mage::getModel('rakuten/order')->exportOrderToRakuten($rakutenOrderId);
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order was successfully synchronized.'));

        } catch (Exception $e) {

            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());

        }

        $this->_redirect('*/*');
    }

    public function massSyncToRakutenAction()
    {
        $rakutenOrderIds = $this->getRequest()->getParam('rakuten_order_ids', array());

        try {

            foreach ($rakutenOrderIds as $id) {
                Mage::getModel('rakuten/order')->exportOrderToRakuten($id);
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Orders were successfully synchronized.'));

        } catch (Exception $e) {

            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());

        }

        $this->_redirect('*/*');
    }

    public function viewSyncedOrderAction()
    {
        $rakutenOrderId = $this->getRequest()->getParam('order_id', false);

        try {

            if (empty($rakutenOrderId)) {
                Mage::throwException($this->__('Invalid Order ID.'));
            }

            $rakutenOrder = Mage::getModel('rakuten/rakuten_order')->load($rakutenOrderId);
            $orderId = Mage::getModel('sales/order')->loadByIncrementId($rakutenOrder->getMagentoIncrementId())->getId();

            if ($orderId) {
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
            } else {
                $this->_getSession()->addError($this->__('Can not find order in magento. Try to sync.'));
                $this->_redirect('*/*');
            }

        } catch (Exception $e) {

            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*');

        }
    }
}
