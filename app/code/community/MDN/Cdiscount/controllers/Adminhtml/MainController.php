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

class MDN_Cdiscount_Adminhtml_MainController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Main screen
     */
    public function indexAction() {                        
        
        try{
            $currentCountry = Mage::getModel('MarketPlace/Countries')->getCurrentCountry($this->getRequest()->getParam('country_id'), 'cdiscount');

            if(!$currentCountry instanceof MDN_MarketPlace_Model_Countries || !$currentCountry->getId()){
                Mage::getSingleton('adminhtml/session')->addError($this->__('No active account. Before using previous screen, you must activate at least one account.'));
                $this->_redirect('adminhtml/Configuration');
            }else{
                Mage::register('mp_country', $currentCountry);

                $this->loadLayout();

                $this->_setActiveMenu('sales/marketplace/cdiscount');

                $this->renderLayout();
            }
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }
        
    }
    

    /**
     * Save products datas
     */
    public function saveAction() {

        try {

             Mage::helper('MarketPlace')->save($this->getRequest(), $this->getRequest()->getParam('country_id'));

            //confirm & redirect
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());            
        }

        $this->_redirect('Cdiscount/Main/index');
    }

    /**
     * Check connection
     * 
     * @deprecated since version 2.0
     */
    public function checkConnexionAction(){

        throw new Exception($this->__('Deprecated method in %s', __METHOD__));
        
        /*try{

            $connexion = mage::helper('Cdiscount/Auth')->checkConnection();
            if($connexion === true){

                Mage::getSingleton('adminhtml/session')->addSuccess('Connexion OK.');

            }else{

                Mage::getSingleton('adminhtml/session')->addError('Connexion FAILED : '.$connexion);

            }

            $this->_redirectReferer();

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }*/

    }

    /**
     * reset last update
     */
    public function resetLastUpdateAction(){

        try{

            $countryId = $this->getRequest()->getParam('country_id');
            $country = Mage::getModel('MarketPlace/Countries')->load($cpountryId);
            Mage::register('mp_country', $country);
            
            Mage::Helper('Cdiscount/ResetLastUpdate')->run();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Cdiscount last updated successfully reset'));
            $this->_redirectReferer();

        }catch(Exception $e){

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();

        }

    }
    
    /**
     * products created grid ajax 
     */
    public function productsCreatedGridAjaxAction(){
        
        $country = $this->getRequest()->getParam('country_id');

        Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));

        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsCreated');
        $this->getResponse()->setBody(
            $block->toHtml()
        );
        
    }
    
    /**
     * products created waiting for update grid ajax 
     */
    public function productsCreatedWaitingForUpdateGridAjaxAction(){
        
        $country = $this->getRequest()->getParam('country_id');

        Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));

        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsCreatedWaitingForUpdate');
        $this->getResponse()->setBody(
            $block->toHtml()
        );
        
    }
    
    /**
     * products created up to date grid ajax 
     */
    public function productsCreatedUpToDateGridAjaxAction(){
        
        $country = $this->getRequest()->getParam('country_id');

        Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));

        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsCreatedUpToDate');
        $this->getResponse()->setBody(
            $block->toHtml()
        );
        
    }
    
    /**
     * products t oadd grid ajax 
     */
    public function productsToAddGridAjaxAction(){
        
        $country = $this->getRequest()->getParam('country_id');

        Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));

        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsToAdd');
        $this->getResponse()->setBody(
            $block->toHtml()
        );
        
    }
    
    /**
     * products pending grid ajax
     */
    public function productsPendingGridAjaxAction(){
        
        $country = $this->getRequest()->getParam('country_id');

        Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));

        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsPending');
        $this->getResponse()->setBody(
            $block->toHtml()
        );
        
    }
    
    /**
     * products in error grid ajax 
     */
    public function productsInErrorGridAjaxAction(){
        
        $country = $this->getRequest()->getParam('country_id');

        Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));

        $block = $this->getLayout()->createBlock('Cdiscount/Grids_ProductsInError');
        $this->getResponse()->setBody(
            $block->toHtml()
        );
        
    }


    protected function _isAllowed() {
        return true;
    }

}
