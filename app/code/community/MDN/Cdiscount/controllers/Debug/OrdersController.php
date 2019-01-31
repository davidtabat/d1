<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_Cdiscount
 * @version 2.0
 */

class MDN_Cdiscount_Debug_OrdersController extends Mage_Adminhtml_Controller_Action {

    /**
     * Import orders CRON action
     */
    public function importOrdersCronAction(){

        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $debug = mage::helper('MarketPlace/Main')->importOrders(Mage::helper('Cdiscount')->getMarketPlaceName());
            Mage::getSingleton('adminhtml/session')->addSuccess($debug);
            $this->_redirectReferer();

        }catch(Exception $e){

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();

        }

    }

    /**
     * Update order
     */
    public function updateOrderAction(){
        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $order_id = $this->getRequest()->getPost('order_id');
            $order_status_to = $this->getRequest()->getPost('order_status_to');
            $order_status_from = $this->getRequest()->getPost('order_status_from');
            $tab = array();
            
            $res = Mage::Helper('Cdiscount/Orders')->updateOrders($order_status_from, $order_status_to, $order_id);

            $this->_prepareDownloadResponse('validation_response.xml', $res['content'], 'txt/xml');

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }
    }

    /**
     * Get orders by status
     */
    public function getOrdersAction(){
        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $res = Mage::Helper('Cdiscount/Services')->getOrderList(array($this->getRequest()->getPost('status')));

            $this->_prepareDownloadResponse('orders.xml', $res['content'], 'txt/xml');

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }
    }

    protected function _isAllowed() {
        return true;
    }

}
