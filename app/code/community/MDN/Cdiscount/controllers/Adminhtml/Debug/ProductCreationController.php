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

class MDN_Cdiscount_Adminhtml_Debug_ProductCreationController extends Mage_Adminhtml_Controller_Action {

    /**
     * Check product creation CRON action
     */
    public function checkProductCreationCronAction(){

        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            Mage::helper('MarketPlace/Main')->checkProductCreation(Mage::helper('Cdiscount')->getMarketPlaceName());
            Mage::getSingleton('adminhtml/session')->addSuccess('Product creation checked');
            $this->_redirectReferer();

        }catch(Exception $e){
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();

        }

    }

    /**
     * Generate catalog as csv 
     */
    public function generateCatalogCsvAction(){
        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $content = Mage::Helper('Cdiscount/ProductCreation')->getCatalogAsCsv();

            $this->_prepareDownloadResponse('catalog.csv', $content, 'text/csv');

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }
    }

    /**
     * Synchronize cdisocunt => magento listings
     */
    public function syncCatalogAction()
    {

        try
        {
            $countryId = $this->getRequest()->getParam('countryId');
            $page = $this->getRequest()->getParam('page');
            if (!$page)
                $page = 0;
            $result = Mage::helper('Cdiscount/ListingSynchronization')->synchronize($countryId, $page);


            if ($result)
            {
                $this->loadLayout();
                $block = $this->getLayout()->createBlock('Cdiscount/Debug_ListingSynchronization');
                $block->setPage($page);
                $block->setResult($result);
                $block->setMessage('Page #'.($page + 1).' processed');
                $block->setGoto($this->getUrl('*/*/*', array('countryId' => $countryId, 'page' => ($page + 1))));
                $this->getLayout()->getBlock('content')->append($block);
                $this->renderLayout();
            }
            else
            {
                Mage::getSingleton('adminhtml/session')->addSuccess('Listing synchronization complete, please check log tab for more information');
                $this->_redirect('Cdiscount/Main/index');
            }


        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::helper('Cdiscount')->magentoLog('Error in sync catalog : '.$e->getMessage());
            $this->_redirectReferer();
        }

    }


    public function CheckSubmitProductAction()
    {
        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);

            $res = Mage::Helper('Cdiscount/Services')->getProductPackageSubmissionResult($this->getRequest()->getPost('offer_package_id'));

            $this->_prepareDownloadResponse('result.xml', $res['content'], 'text/xml');

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectreferer();
        }
    }


    protected function _isAllowed() {
        return true;
    }

}
