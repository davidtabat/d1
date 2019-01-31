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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_MonitoringController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Index action 
     */
    public function indexAction(){
        
        try{
        
            $currentCountry = Mage::getModel('MarketPlace/Countries')->getCurrentCountry($this->getRequest()->getParam('countryId'));

            if(!$currentCountry instanceof MDN_MarketPlace_Model_Countries || !$currentCountry->getId()){
                Mage::getSingleton('adminhtml/session')->addError($this->__('No active account. Before using previous screen, you must activate at least one account.'));
                $this->_redirect('MarketPlace/Configuration');
            }else{
                Mage::register('mp_country', $currentCountry);

                $this->loadLayout();

                $this->_setActiveMenu('sales/marketplace/logs/monitoring');

                $this->renderLayout();
            }
        }catch(Exception $e){
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
            
        }
        
    }
    
    /**
     * Download product list 
     */
    public function downloadAction(){
        
        try{
            
            $content = '';
            $name = 'products';
            
            $countryId = $this->getRequest()->getParam('countryId');
            $status = $this->getRequest()->getParam('status');
            
            $name .= '_'.$status.'.csv';
            
            $collection = Mage::getModel('MarketPlace/Data')->getCollection()
                            ->addFieldToFilter('mp_marketplace_id', $countryId)
                            ->addFieldToFilter('mp_marketplace_status', $status);
            
            foreach($collection as $item){
                $content .= '"'.$item->getmp_product_id().'";';
                if($status == MDN_MarketPlace_Helper_ProductCreation::kStatusCreated){
                    $content .=  '"'.$item->getmp_reference().'"'."\n";
                }else{
                    $content .= '"'.$item->getmp_message().'"'."\n";
                }
            }
            
            $this->_prepareDownloadResponse($name, $content, 'text/csv');
            
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirect('MarketPlace/Monitoring/index', array('countryId' => $countryId));
        }
        
    }
    
}
