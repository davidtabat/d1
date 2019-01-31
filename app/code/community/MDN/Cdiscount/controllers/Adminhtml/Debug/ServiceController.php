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
 * @package MDn_Cdiscount
 * @version 2.0
 */

class MDN_Cdiscount_Adminhtml_Debug_ServiceController extends Mage_Adminhtml_Controller_Action {

    /**
     * Service Operation
     *
     * @param array $data
     */
    public function operationAction(){

        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            $data = $this->getRequest()->getParams();

            if(!array_key_exists('type', $data))
                    throw new Exception('No action...');

            $helper = Mage::Helper('Cdiscount/Services');
            $method = $data['type'];

            $params = (array_key_exists('params', $data)) ? $data['params'] : null;

            $result = $helper->$method($params);

            if($result['type'] == MDN_Cdiscount_Helper_Services::kResponseTypeFile){

                $this->_prepareDownloadResponse('response.xml', $result['content'], 'text/xml');

            }else{

                Mage::getSingleton('Adminhtml/Session')->addSuccess($result['message']);
                $this->_redirectReferer();
            }


        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }

    }

    public function BuildCustomCategoriesFileAction()
    {
        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);

            Mage::helper('Cdiscount/Category')->generateCustomCategoryFile();
            Mage::getSingleton('Adminhtml/Session')->addSuccess($this->__('Custom categories file generated'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

        }
        $this->_redirectReferer();
    }


    protected function _isAllowed() {
        return true;
    }


}
