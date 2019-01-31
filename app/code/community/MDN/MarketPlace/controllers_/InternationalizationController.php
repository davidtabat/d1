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
class MDN_MarketPlace_InternationalizationController extends Mage_Adminhtml_Controller_Action {

    /**
     * Index      
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Edit 
     */
    public function editAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save 
     */
    public function saveAction() {

        try {

            $data = $this->getRequest()->getPost();
            //echo '<pre>';var_dump($data);die('</pre>');
            $marketplace = $data['marketplace'];
            $store_id = $data['store_id'];
            $language = $data['associated_data'];

            Mage::getModel('MarketPlace/Internationalization')->updateAssociation($marketplace, $store_id, $language);

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
            $this->_redirect('*/*');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . ' : ' . $e->getTraceAsString());
            $this->_redirect('*/*');
        }
    }

    /**
     * Delete 
     */
    public function deleteAction() {

        try {

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data deleted'));
            $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . ' : ' . $e->getTraceAsString());
            $this->_redirectReferer();
        }
    }

}
