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
class MDN_MarketPlace_Adminhtml_CategoriesController extends Mage_Adminhtml_Controller_Action {

    /**
     * Main screen
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Edit association
     */
    public function EditAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save associations
     */
    public function SaveAction() {
        
        try{
            //get data
            $data = $this->getRequest()->getPost();

            $required = array('marketplace', 'category_id', 'association_data');

            foreach($required as $value){            
                if(!array_key_exists($value, $data))
                    throw new Exception($this->__('Some parameters are missing'));            
            }

            $marketPlace = $data['marketplace'];
            $categoryId = $data['category_id'];
            $associationdata = $data['association_data'];

            //serialize associationData & save
            $associationdata = mage::helper('MarketPlace/Serializer')->serializeObject($associationdata);
            $model = mage::getModel('MarketPlace/Category');
            $model->updateAssociation($marketPlace, $categoryId, $associationdata);

            //confirm & redirect
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    /**
     * Show categories select according to univers and marketplace
     */
    public function showCategoriesComboAction() {

        try {

            $mp = ucfirst($this->getRequest()->getParam('mp'));
            $univers = $this->getRequest()->getParam('univers');
            $html = Mage::helper($mp.'/Category')->getCategoriesAsCombo($univers);

            $this->getResponse()->setBody($html);

        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage().' : '.$e->getTraceAsString());
        }
    }

    /**
     * show sub categories select according to category and marketplace
     */
    public function showSubCategoriesComboAction(){

        try{

            $mp = ucfirst($this->getRequest()->getParam('mp'));
            $cat = $this->getRequest()->getParam('category');
            $html = Mage::helper($mp.'/Category')->getSubCategoriesAsCombo($cat);

            $this->getResponse()->setBody($html);
        }catch(Exception $e){
            $this->getResponse()->setBody($e->getMessage().' : '.$e->getTraceAsString());
        }

    }

    public function showSubSubCategoriesComboAction(){
        try{
            $mp = ucfirst($this->getRequest()->getParam('mp'));
            $cat = $this->getRequest()->getparam('category');
            $html  =Mage::helper($mp.'/Category')->getSubSubCategoriesAsCombo($cat);
            $this->getResponse()->setBody($html);
        }catch(Exception $e){
            $this->getResponse()->setBody($e->getMessage().' : '.$e->getTraceAsString());
        }
    }

    /**
     * Retrieve current category's marketplace reference
     */
    public function retrieveReferenceAction(){
        try{
            $mp = ucfirst($this->getRequest()->getParam('mp'));
            $cat = $this->getRequest()->getParam('selected');
            $html = Mage::helper($mp.'/Category')->getReference($cat);
            $this->getResponse()->setBody($html);
        }catch(Exception $e){
            $this->getResponse()->setBody($e->getMessage().' : '.$e->getTraceAsString());
        }
    }

    protected function _isAllowed() {
        return true;
    }

}