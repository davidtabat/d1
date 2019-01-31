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
class MDN_MarketPlace_ProductsController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Mass add products 
     */
    public function MassAddProductsAction(){

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        $message = '';
        
        $start = microtime(true);
        
        try{

            $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
            Mage::register('mp_country', $country);
            
            $marketplace = ucfirst($country->getAssociatedMarketplace());

            if(Mage::Helper($marketplace)->allowProductCreation()){

                $helper = mage::helper($marketplace.'/ProductCreation');

                $res = $helper->massProductCreation($this->getRequest());

                $message = $this->__(' %s product(s) submitted to %s', $res['ok'], $marketplace);  
                if(count($res['ok']) < count($res['total']))
                    $message .= $this->__('See error tab for more informations');
                
                Mage::getSingleton('adminhtml/session')->addSuccess($message);

            }else{

                $error = MDN_MarketPlace_Model_Logs::kIsError;
                $message = $this->__('Product creation is not enable  for %s', $marketplace);
                Mage::getSingleton('adminhtml/session')->addError($message);

            }
            

        }catch(Exception $e){
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e);            
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
       
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            strtolower($marketplace),
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeCreation,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirect(ucfirst($marketplace).'/Main/index', array('country_id' => $country->getId(), 'tab' => 'main'));
        
    }
    
    /**
     * Mass revise products 
     */
    public function MassReviseProductsAction(){

        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        $message = '';
        
        $start = microtime(true);
        
        try{

            $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
            Mage::register('mp_country', $country);
            
            $marketplace = ucfirst($country->getAssociatedMarketplace());
            
            if(Mage::Helper($marketplace)->allowReviseProducts()){

                $helper = mage::helper($marketplace.'/ProductCreation');

                $helper->massReviseProducts($this->getRequest());

                $message = $this->__('Products submitted to %s', $marketplace);                
                Mage::getSingleton('adminhtml/session')->addSuccess($message);

            }else{

                $error = MDN_MarketPlace_Model_Logs::kIsError;
                $message = $this->__('Actually, %s doesn\'t allow revise products', $marketplace);
                Mage::getSingleton('adminhtml/session')->addError($message);

            }
            

        }catch(Exception $e){
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
       
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            strtolower($marketplace),
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeUpdate,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirect(ucfirst($marketplace).'/Main/index', array('country_id' => $country->getId(), 'tab' => 'main'));
        
    }

    /**
     * Mass update status 
     */
    public function MassUpdateStatusAction(){
        try{            
            $helper = mage::helper('MarketPlace/ProductCreation');
            $helper->updateStatus($this->getRequest());
            Mage::getSingleton('adminhtml/session')->addSuccess('Status updated.');
            $this->_redirectReferer();
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }
    }

    /**
     * Mass generate product feed 
     * 
     * 
     */
    public function MassGenerateProductFeedAction(){
        
        try{
            
            $start = 0;
            $end = 0;
            $executionTime = 0;
            $error = MDN_MarketPlace_Model_Logs::kNoError;
            $message = '';
            $isExceptionError = false;
            
            $start = microtime(true);
            
            $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
            Mage::register('mp_country', $country);
            
            $mp = ucfirst($country->getAssociatedMarketplace());

            $helper = Mage::helper($mp.'/ProductCreation');
            if($helper->allowGenerateProductFeed()){
                $res = $helper->generateProductFeed($this->getRequest());
                $type = $helper->getProductFileType();
                $content = $res['content'];
                
            }else{
                throw new Exception($this->__('Not allowed'));                
            }
            
            $message .= $this->__('Product feed creation')."\n";
            
            if($res['error'] === true){
                
                $error = MDN_MarketPlace_Model_Logs::kIsError;
                $message .= $res['errorMessage'];
                
            }
            
        }catch(Exception $e){
            
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);
            $isExceptionError = true;
                       
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            strtolower($mp),
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeCreation,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        if($isExceptionError === false){
            $this->_prepareDownloadResponse('productFeed.'.$type, $content, 'text/'.$type);
        }else{
            $this->_redirectReferer();
        }
        
    }

    /**
     * Mass update free shipping 
     * 
     * @deprecated
     */
    public function MassUpdateFreeShippingAction(){
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try{

            throw new Exception($this->__('Deprecated method %', __METHOD__));        

        }catch(Exception $e){
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = $e->getMessage()."\n\n";
            $message .= $e->getTraceAsString();
            $message = str_replace("\n", '<br/>', $message);
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            $mp,
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeUpdate,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirectReferer();
        
    }

    /**
     * Grid ajax action 
     */
    public function gridAjaxAction(){
        
        try{
            
            $country = $this->getRequest()->getParam('country');

            Mage::register('mp_country', Mage::getModel('MarketPlace/Countries')->load($country));
            
            $block = $this->getLayout()->createBlock('MarketPlace/Products');
            $this->getResponse()->setBody(
                $block->toHtml()
            );

        }catch(Exception $e){
            $this->getResponse()->setBody($e->getMessage().' : '.$e->getTraceAsString());
        }

    }

    /**
     * Mass update stock and price
     */
    public function MassUpdateStockPriceAction() {
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try {

            $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
            Mage::register('mp_country', $country);
            
            $mp = ucfirst($country->getAssociatedMarketplace());

            $helper = Mage::Helper(ucfirst($mp) . '/ProductUpdate');
            $helper->update($this->getRequest());

            $message = $this->__('Prices & stocks exported.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            

        } catch (Exception $e) {
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            strtolower($mp),
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeUpdate,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirectReferer();
    }

    /**
     * Mass update image
     */
    public function MassUpdateImageAction() {
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try {

            $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
            Mage::register('mp_country', $country);
            
            $mp = ucfirst($country->getAssociatedMarketplace());
            $ids = $this->getRequest()->getPost('product_ids');
            
            $helper = Mage::Helper(ucfirst($mp) . '/ProductUpdate');
            $helper->updateImageFromGrid($ids);

            $message = $this->__('Images exported');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);            

        } catch (Exception $e) {
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            strtolower($mp),
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeUpdate,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirectReferer();
    }

    /**
     * Mass matching EAN
     */
    public function MassMatchingEanAction() {

        $products = array();
        $errors = array();
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try {

            $countryId = $this->getRequest()->getParam('country_id');
            $ids = $this->getRequest()->getPost('product_ids');  
            
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            $mp = $country->getAssociatedMarketplace();
                        
            $helper = Mage::Helper(ucfirst($mp) . '/Matching');

            if (Mage::Helper(ucfirst($mp) . '/ProductCreation')->allowMatchingEan()) {

                foreach ($ids as $id) {

                    // load product
                    $product = Mage::getModel('Catalog/Product')->setStoreId($country->getParam('store_id'))->load($id);
                    $barcode = Mage::Helper('MarketPlace/Barcode')->getBarcodeForProduct($product);

                    if (Mage::Helper('MarketPlace/Checkbarcode')->checkCode($barcode) === true) {
                        
                        $products[$barcode] = $product;
                        
                    }else{

                        $errors[] = $product->getsku();

                    }
                }

                // ok
                if(count($products) > 0){
                    $mathingArray = $helper->buildMatchingArray($products);
                    $helper->Match($mathingArray);

                    $message = $this->__('Matching processing');
                }

                // errors
                if(count($errors) > 0){
                    Mage::getModel('MarketPlace/Data')->updateStatus($errors, $country->getId(), MDN_MarketPlace_Helper_ProductCreation::kStatusInError);
                    foreach($errors as $id){
                        Mage::getModel('MarketPlace/Data')->addMessage($id, $this->__('Invalid EAN code'), $country->getId());
                    }
                    $message = $this->__('Some products can\'t be matched. Please check EAN codes');
                    // add log
                    $message .= ' : '.implode(',',$errors);
                    $error = MDN_MarketPlace_Model_Logs::kIsError;
                                        
                }

            } else {

                $error = MDN_MarketPlace_Model_Logs::kIsError;
                $message = $this->__(ucfirst($mp) . ' is not allowed to process matching EAN');
            }

            // message
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__($message));
            
        } catch (Exception $e) {
            
            // message
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);            
            
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            $mp,
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeCreation,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirect(ucfirst($mp).'/Main/index', array('country_id' => $country->getId()));
    }
    
    /**
     * Reset update 
     *  
     */
    public function resetLastUpdateAction(){
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        $message = '';
        
        $start = microtime(true);
        
        try{
            
            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $mp = $country->getAssociatedMarketplace();
            
            Mage::Helper(ucfirst($mp).'/ProductUpdate')->reset();
            
            $message = $this->__('Update reseted');
            
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            
        }catch(Exception $e){
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            $mp,
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeUpdate,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirectReferer();
        
    }

    /**
     * Mass delete products 
     */
    public function MassDeleteProductsAction(){
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try{
            
            $ids = $this->getRequest()->getPost('product_ids');
            
            $countryId = $this->getRequest()->getParam('country_id');            
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            
            Mage::register('mp_country', $country);
            
            $mp = $country->getAssociatedMarketplace();
            
            Mage::Helper(ucfirst($mp).'/Delete')->process($ids);
            
            $message = $this->__('Product(s) deleted');
            
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            
        }catch(Exception $e){
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            $mp,
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeUpdate,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirect(ucfirst($mp).'/Main/index', array('country_id' => $country->getId()));
        
    }

    /**
     * Auto submit action 
     */
    public function autoSubmitAction(){
        
        $start = 0;
        $end = 0;
        $executionTime = 0;
        $message = '';
        $error = MDN_MarketPlace_Model_Logs::kNoError;
        
        $start = microtime(true);
        
        try{                        
            
            $countryId = $this->getRequest()->getParam('countryId');            
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            
            Mage::register('mp_country', $country);
            
            $mp = $country->getAssociatedMarketplace();
            
            $retour = Mage::Helper(ucfirst($mp).'/ProductCreation')->autoSubmit();
            
            switch($retour){
                case 0:
                    $message = $this->__('Product(s) submitted');
                    break;
                case 1:
                    $message = $this->__('No product to export');
                    break;                    
            }            
            
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            
        }catch(Exception $e){
            $error = MDN_MarketPlace_Model_Logs::kIsError;
            $message = Mage::Helper('MarketPlace/Errors')->formatErrorMessage($e); 
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        mage::getModel('MarketPlace/Logs')->addLog(
            $mp,
            $error,
            $message,
            MDN_MarketPlace_Model_Logs::kScopeCreation,    
            array(
                'fileName' => NULL
            ),
            $executionTime
        );
        
        $this->_redirectReferer();
        
    }
    
    /**
     * Match by mp reference 
     */
    public function matchByMpReferenceAction(){
        try{
            
            $productId = $this->getRequest()->getParam('productId');
            $reference = $this->getRequest()->getParam('reference');
            $countryId = $this->getRequest()->getParam('countryId');
            
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            $mp = $country->getAssociatedMarketplace();
            
            Mage::Helper(ucfirst($mp).'/Matching')->matchByMpReference($productId, $reference);            
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Matching in progress'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
        }
        $this->_redirectReferer();
    }
    
    public function processFromMonitoringAction(){
        try{
            $message = '';
            $action = $this->getRequest()->getParam('action');
            $productId = $this->getRequest()->getParam('productId');
            $countryId = $this->getRequest()->getParam('countryId');
            $message = Mage::Helper('MarketPlace/Data')->process($action, $productId, $countryId);
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
        }
        $this->_redirectReferer();
    }
    
    public function processFromProductSheetAction(){
        
        $message = '';
        
        try{
            
            $action = $this->getRequest()->getParam('action');
            $productId = $this->getRequest()->getParam('productId');
            $countryId = $this->getRequest()->getParam('countryId');
            
            $message = Mage::Helper('MarketPlace/Data')->process($action, $productId, $countryId);            
            
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
        }
        
        $this->_redirectReferer();
        
    }

    protected function _isAllowed() {
        return true;
    }
    
}
