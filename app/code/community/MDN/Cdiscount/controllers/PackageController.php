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

class MDN_Cdiscount_PackageController extends Mage_Core_Controller_Front_Action {

    /**
     * Download 
     */
    public function downloadAction(){

        try{

            $countryId = $this->getRequest()->getParam('country_id');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $type = $this->getRequest()->getParam('type');
            $filename = $this->getRequest()->getParam('filename');

            $content = file_get_contents(Mage::Helper('Cdiscount/Package_'.$type)->getPackageDirectory().'/'.$type.'/'.$filename.'.zip');

            $this->_prepareDownloadResponse($filename.'.zip', $content, 'application/zip');

        }catch(Exception $e){

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();

        }

    }
    
    /**
     * Custom download response method for magento multi version compatibility
     */
    /*protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', strlen($content))
                ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
                ->setBody($content);
    }*/


    protected function _isAllowed() {
        return true;
    }

}
