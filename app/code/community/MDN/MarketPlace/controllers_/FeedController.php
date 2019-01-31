<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2012 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_FeedController extends Mage_Adminhtml_Controller_Action {

    /**
     * Index action 
     */
    public function indexAction(){

        $this->LoadLayout();
        
        $this->_setActiveMenu('sales');
        //$this->getLayout()->getBlock('head')->setTitle($this->__('Marketplace - Feeds'));
        
        $this->renderLayout();

    }

    public function deleteAction()
    {
        try{

            $id = $this->getRequest()->getParam('feed_id');

            $feed = Mage::getModel('MarketPlace/Feed')->load($id);

            $feed->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Feeds delete'));

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError(Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e));
        }
        $this->_redirectReferer();
    }

    /**
     * Download Feed action 
     */
    public function downloadFeedAction(){
        try{

            $id = $this->getRequest()->getParam('id');
            $type = $this->getRequest()->getParam('type');

            $feed = Mage::getModel('MarketPlace/Feed')->load($id);
            
            switch($type){
                case 'response':
                    $content = $feed->getResponse();
                    break;
                case 'content':
                    $content = $feed->getContent();
                    break;
            }

            $this->_prepareDownloadResponse($type.'.xml', $content, 'text/xml');
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError(Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e));
            $this->_redirectReferer();
        }

    }
    
    /**
     * Download feed response 
     */
    public function downloadResultAction(){
        try{
            
            $countryId = $this->getRequest()->getParam('country');
            $id = $this->getRequest()->getParam('feed_id');
            $type = $this->getRequest()->getParam('type');
            
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            $mp = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id())->getmpa_mp();
            
            $helper = Mage::helper(ucfirst($mp).'/Feed');
            
            switch($type){
                
                case '_MATCHING_PRODUCTS_':
                case '_IMPORT_ORDERS_':
                case '_UNSHIPPED_ORDERS_':
                    $result = Mage::Helper('Amazon/MWS_Reports')->getReportById($id, $type);
                    break;
                default:
                    $result = $helper->getFeedSubmissionResult($id);
                    break;
                
            }
            
            $this->_prepareDownloadResponse('result.xml', $result, 'text/xml');
            
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError(Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e));
            $this->_redirectReferer();
        }
    }
    
    /**
     * Grid ajax action 
     */
    public function gridAjaxAction(){
        
        try{
            
            $block = $this->getLayout()->createBlock('MarketPlace/Feed_FeedGrid');
            
            $this->getResponse()->setBody($block->toHtml());
            
        }catch(Exception $e){
            
            Mage::getSingleton('adminhtml/session')->addError(Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e));
            $this->_redirectReferer();
            
        }
        
    }

    /**
     *
     */
    public function pruneAction()
    {
        $count = Mage::helper('MarketPlace/Feed')->prune();
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s feeds deleted', $count));
        $this->_redirectReferer();
    }

}
