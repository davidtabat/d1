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
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_Cdiscount
 * @version 2.0
 */
class MDN_Cdiscount_Adminhtml_BrandsController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Grid ajax action 
     */
    public function gridAjaxAction(){
        
        try{

            $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_Brands_Grid');
            $this->getResponse()->setBody(
                $block->toHtml()
            );

        }catch(Exception $e){
            $this->getResponse()->setBody($e->getMessage().' : '.$e->getTraceAsString());
        }
        
    }
    
    /**
     * Add action 
     */
    public function addAction(){
        
        try{
            
            $code = $this->getRequest()->getPost('mpb_code');
            $label = $this->getRequest()->getPost('mpb_label');
            
            $item = Mage::getModel('MarketPlace/Brands')->getCollection()
                        ->addFieldToFilter('mpb_marketplace_id', 'cdiscount')
                        ->addFieldToFilter('mpb_code', array('like' => "%$code%"))
                        ->getFirstItem();
            
            if($item->getId()){
                
                Mage::getSingleton('adminhtml/session')->addError('Brand already exists');
                
            }else{
                
                $item = Mage::getModel('MarketPlace/Brands')
                        ->setmpb_code($code)
                        ->setmpb_label($label)
                        ->setmpb_marketplace_id('cdiscount')
                        ->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess('Brand added');
                
            }
            
        }catch(Exception $e){
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            
        }
        
        $this->_redirectReferer();
        
    }

    public function SyncAction()
    {
        try
        {
            $count = Mage::helper('Cdiscount/Brand')->synchronize();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Brands list synchronized : %s added', $count));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    public function AutomaticAssociationAction()
    {
        try
        {
            $count = Mage::helper('Cdiscount/Brand')->autoAssociation();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Automatic association performed : %s brands associated', $count));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    public function EditAction()
    {
        $manufacturerId = $this->getRequest()->getParam('manufacturer');
        $brands = Mage::getModel('MarketPlace/Brands')->getCollection()
            ->addFieldToFilter('mpb_marketplace_id', 'cdiscount')
            ->setOrder('mpb_label', 'ASC');

        $divId = 'mp_brand_cdiscount_'.$manufacturerId;

        $html = '<select id="select_brand_'.$manufacturerId.'">';
        $html .= '<option value=""></option>';
        foreach($brands as $brand)
            $html .= '<option value="'.$brand->getId().'" '.($manufacturerId && $manufacturerId == $brand->getmpb_manufacturer_id() ? ' selected ' : '').'>'.$brand->getmpb_label().'</option>';
        $html .= '</select>';

        $onClick = "new Ajax.Updater('".$divId."', '".$this->getUrl('Cdiscount/Brands/Associate', array('mp' => 'cdiscount'))."', {method: 'get', 'parameters': {'manufacturer_id': ".$manufacturerId." ,  'mpb_id': document.getElementById('select_brand_".$manufacturerId."').value}});";

        $html .= '&nbsp; <input type="button" value="Select" onclick="'.$onClick.'">';

        die($html);
    }

    public function AssociateAction()
    {
        $manufacturerId = $this->getRequest()->getParam('manufacturer_id');
        $mpbId = $this->getRequest()->getParam('mpb_id');

        $item = Mage::getModel('MarketPlace/Brands')->load($mpbId);
        $item->setmpb_manufacturer_id($manufacturerId)->save();

        die('Association saved');
    }

    protected function _isAllowed() {
        return true;
    }
}
