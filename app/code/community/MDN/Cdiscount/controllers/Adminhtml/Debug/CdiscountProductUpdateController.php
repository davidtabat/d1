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

class MDN_Cdiscount_Adminhtml_Debug_CdiscountProductUpdateController extends Mage_Adminhtml_Controller_Action {

    /**
     * Update stock / prices CRON
     */
    public function cronExportStockAndPriceAction(){

        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            Mage::helper('MarketPlace/Main')->updateStocksAndPrices(Mage::helper('Cdiscount')->getMarketPlaceName());
            Mage::getSingleton('Adminhtml/session')->addSuccess('Stock & prices updated');
            $this->_redirectReferer();

        }catch(Exception $e){
            Mage::getSIngleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }

    }

    /**
     * check offer submit 
     */
    public function CheckSubmitOfferAction(){

        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $res = Mage::Helper('Cdiscount/Services')->getOfferPackageSubmissionResult($this->getRequest()->getPost('offer_package_id'));

            $this->_prepareDownloadResponse('result.xml', $res['content'], 'text/xml');

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectreferer();
        }

    }

    /*public function getStockFeedAction(){

        try{

            $content = Mage::Helper('Cdiscount/ProductUpdate')->getOfferFeed();

            $this->_prepareDownloadResponse('stock.xml', $content, 'type/xml');

        }catch(Exception $e){

            Mage::getSingleton('Adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();

        }

    }*/


    protected function _isAllowed() {
        return true;
    }



}
