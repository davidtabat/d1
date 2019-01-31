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
class MDN_MarketPlace_ManualController extends Mage_Adminhtml_Controller_Action {

    /**
     * Download documents
     *
     * @param unknown_type $fileName
     * @param unknown_type $content
     * @param unknown_type $contentType
     * @param unknown_type $contentLength
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null) {
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', strlen($content))
                ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
                ->setBody($content);
    }

    /**
     * Export products
     */
    public function exportAction() {
        
        $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
        Mage::register('mp_country', $country);

        try {
            
            $marketplace = Mage::registry('mp_country')->getAssociatedMarketplace();
            
            $helper = mage::helper(ucfirst($marketplace));
            $content = $helper->getProductFile();
            $this->_prepareDownloadResponse('export' . $marketplace, $content, 'text');
        } catch (Exception $e) {
            if ($e->getCode() == 0)
                Mage::getSingleton('adminhtml/session')->addSuccess($e->getMessage());
            else
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            mage::getModel('MarketPlace/Logs')->addLog(
                    Mage::Helper(ucfirst($marketplace))->getMarketPlaceName(),
                    MDN_MarketPlace_Model_Logs::kIsError,
                    $e->getMessage(),
                    MDN_MarketPlace_Model_Logs::kScopeCreation,
                    array('fileName' => NULL)
            );
            $this->_redirect(ucfirst($marketplace).'/Main/index', array());
        }
    }

    /**
     * Import orders
     *
     */
    public function importSalesAction() {

        $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('countryId'));
        $marketplace = $country->getAssociatedMarketplace();
        Mage::register('mp_country', $country);

        try {

            //save text file
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('txt', 'csv', 'xml'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/marketplace/' . $marketplace . '/';

            // create directories if necessary
            if (!file_exists($path))
                mkdir($path, 0755, true);

            $uploader->save($path);

            //if there is a file uploaded
            if ($uploadFile = $uploader->getUploadedFileName()) {
                $file = Mage::helper('MarketPlace')->renameUploadedFile($uploadFile, $path, mage::helper(ucfirst($marketplace))->getMarketPlaceName());
                $debug = Mage::helper(ucfirst($marketplace).'/Orders')->importOrdersFromUploadedFile($path, $file);
                Mage::getSingleton('adminhtml/session')->addSuccess($debug);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect(ucfirst($marketplace).'/Main/index', array('tab' => 'manual_import', 'country_id' => Mage::registry('mp_country')->getId()));
    }

    /**
     * Gril to CSV
     *
     */
    public function exportCsvAction() {

        $country = Mage::getModel('MarketPlace/Countries')->load($this->getRequest()->getParam('country_id'));
        Mage::register('mp_country', $country);
        $marketplace = $country->getAssociatedMarketplace();

        try {

            $fileName = 'produits' . $marketplace . '.csv';
            $block = $this->getLayout()->createBlock('MarketPlace/Products');
            $content = $block->getCsv();
            $this->_prepareDownloadResponse($fileName, $content);

        } catch (Exception $e) {

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect(ucfirst($marketplace).'/Main/index', array());

        }
    }

    /**
     * Import porducts action 
     */
    public function importProductsAction(){
        try {

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $mp = Mage::registry('mp_country')->getAssociatedMarketplace();

            if(Mage::Helper(ucfirst($mp))->allowInternationalization()){
                $country = Mage::Helper(ucfirst($mp).'/Internationalization')->getCurrentCountry($this->getRequest());
                Mage::register('country', $country);
            }

            $helper = Mage::helper(ucfirst($mp).'/ProductCreation');

            $nbr = 0;
            $message = "";

            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('txt', 'csv', 'xml'));
            $path = Mage::app()->getConfig()->getTempVarDir();

            // create directories if necessary
            if (!file_exists($path))
                mkdir($path, 0755, true);

            $uploader->save($path);

            //if there is a file uploaded
            if ($uploadFile = $uploader->getUploadedFileName()) {

                $lines = file($path . '/' . $uploadFile);

                if($helper->isProductFileOk($lines)){

                    $nbrLines = count($lines);

                    $result = $helper->importProducts($lines);
                }
                else{
                   $message = 'This file doesn\'t seems to be a valid '.ucfirst($mp).' product file. Please check it.';
                }

            }

            if($message == ""){
                /*$nbrProducts = $nbrLines - 1;
                $nbrProductsSkipped = $nbrProducts - $nbr;*/

                Mage::getSingleton('adminhtml/session')->addSuccess($result);
            }
            else{
                Mage::getSingleton('adminhtml/session')->addError($message);
            }

            $this->_redirect(ucfirst($mp).'/Main/index', array('tab' => 'manual_import', 'country_id' => $countryId));

        } catch (exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect(ucfirst($mp).'/Main/index', array('tab' => 'manual_import', 'country_id' => $countryId));
        }
    }

    /**
     * Import brands 
     */
    public function importBrandsAction(){

        try{

            $mp = $this->getRequest()->getParam('mp');

            $content = file_get_contents($_FILES['file']['tmp_name']);

            $retour = Mage::helper(ucfirst($mp).'/Brands')->addFromFile($content);

            if(!is_integer($retour)){

                Mage::getSingleton('adminhtml/session')->addSuccess('Brands successfuly imported. Somes brand are not exists on Pixmania.');
                $this->_prepareDownloadResponse('unexisting_brands.csv', $retour);

            }else{

                Mage::getSingleton('adminhtml/session')->addSuccess('Brands successfuly imported');
                $this->_redirectReferer();

            }

        }catch(Exception $e){

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirectReferer();

        }

    }


}
