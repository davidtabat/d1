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
class MDN_MarketPlace_Adminhtml_ConfigurationController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Index action 
     */
    public function indexAction(){
        $this->loadLayout();
        
        $this->_setActiveMenu('sales');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Marketplace - Configuration'));
        
        $this->renderLayout();
    }
    
    /**
     * Delete account 
     */
    public function deleteAccountAction(){
        
        try{
            
            $id = $this->getRequest()->getParam('account');            
            
            $account = Mage::getModel('MarketPlace/Accounts')->load($id);
            
            $account->delete();
            
            Mage::getSingleton('adminhtml/session')->addSuccess('Account deleted');
        }catch(Exception $e){
            Mage::getSingmeton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
        }
                
        $this->_redirect('MarketPlace/Configuration/index');
        
    }
    
    /**
     * Save account 
     */
    public function saveAccountAction(){
        try{
            
            $data = $this->getRequest()->getPost();
            
            $id = $data['data']['column']['mpa_id'];
            $account = Mage::getModel('MarketPlace/Accounts')->load($id);
            
            $params = $data['data']['params'];
            
            if(!$account->getmpa_id()){
                $account = Mage::getModel('MarketPlace/Accounts');
                
                unset($data['data']['column']['mpa_id']);
            }
            
            foreach($data['data']['column'] as $k => $v){
                $method = 'set'.$k;
                $account->$method($v);
            }
            
            $account->setmpa_params($params);                        
            $account->save();
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Account saved'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }                
        
        $this->_redirect('*/*/index', array('type' => 'account', 'mp' => $account->getmpa_mp(), 'account' => $account->getId()));
        
    }
    
    /**
     * Display account edit template
     */
    public function getEditAccountTemplateAction(){
        
        try{
            
            $mp = $this->getRequest()->getParam('mp');
            $id = $this->getRequest()->getParam('account');
            $accountId = null;
            if($id){
                $tmp = explode('_', $id);
                $accountId = $tmp[1];
                $mp = Mage::getModel('MarketPlace/Accounts')->load($accountId)->getmpa_mp();
            }
            
            $block = $this->getLayout()->createBlock(ucfirst($mp).'/Configuration_Account');
            $block->setAccountId($accountId);
            
            $content = $block->toHtml();            
            
        }catch(Exception $e){
            $content = $e->getMessage();
        }                
        
        $this->getResponse()->setBody($content);
        
    }
    
    /**
     * Diplay country edit template 
     */
    public function getEditCountryTemplateAction(){
        
        try{
            
            $id = $this->getRequest()->getParam('country');
            
            $tmp = explode('_',$id);
            $mp = Mage::getModel('MarketPlace/Countries')->load($tmp[1])->getAssociatedMarketplace();
            
            $block = $this->getLayout()->createBlock(ucfirst($mp).'/Configuration_Country');
            $block->setCountry($id);
            
            $content = $block->toHtml();
            
        }catch(Exception $e){
            $content = $e->getMessage();
        }
        
        $this->getResponse()->setBody($content);
        
    }
    
    /**
     * Save country 
     */
    public function saveCountryAction(){
        try{
            
            $data = $this->getRequest()->getPost();            
            
            $country = Mage::getModel('MarketPlace/Countries')->load($data['data']['column']['mpac_id']);
            
            unset($data['data']['column']['mpac_id']);
            
            foreach($data['data']['column'] as $k => $v){
                
                $method = 'set'.$k;
                $country->$method($v);
                
            }
            
            $params = unserialize($country->getmpac_params());
            
            foreach($data['data']['params'] as $k => $v){
                
                $params[$k] = $v;
                
            }
            
            $country->setmpac_params($params)
                    ->save();

            Mage::getSingleton("adminhtml/session")->addSuccess($this->__('Country configuration saved'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/index', array('type' => 'country', 'mp' => $country->getAssociatedMarketplace(), 'country' => $country->getId()));
        
    }
    
    /**
     * Display global configuration template 
     */
    public function getEditMainTemplateAction(){
        
        try{
            
            $id = $this->getRequest()->getParam('mp');
            $mp = explode('_', $id);
            
            $block = $this->getLayout()->createBlock(ucfirst($mp[1]).'/Configuration_Main');
            $block->setMp($mp[1]);
            
            $content = $block->toHtml();
            
        }catch(Exception $e){
            $content = $e->getMessage();
        }
        
        $this->getResponse()->setBody($content);
        
    }
    
    /**
     * Save main configuration 
     */
    public function saveMainAction(){
        try{
            
            $data = $this->getRequest()->getPost();           
            
            $configuration = Mage::getModel('MarketPlace/Configuration')->load($data['data']['mpc_id']);
            
            if(!$configuration->getmpc_id()){
                $configuration = Mage::getModel('MarketPlace/Configuration');
            }                        
            
            foreach($data['data']['column'] as $k => $v){
                $method = 'set'.$k;
                $configuration->$method($v);
            }
            
            if(!isset($data['data']['params']['max_to_export']) || $data['data']['params']['max_to_export'] == '')
                $data['data']['params']['max_to_export'] = 500;

            $configuration->setmpc_params($data['data']['params']);
            $configuration->save();
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Main configuration saved'));
        }catch(Exeption $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/index', array('type' => 'main', 'mp' => $configuration->getmpc_marketplace_id()));
        
    }
    
    /**
     * save general configuration 
     */
    public function saveGeneralAction(){
        
        try{
                        
            $data = $this->getRequest()->getPost();
            $obj = new Mage_Core_Model_Config();                        
            
            foreach($data['data'] as $k => $v){
                             
                if(is_array($v)){
                    $v = serialize($v);
                }
                
                $obj->saveConfig($k, $v);
                
            }
            
            // reinit configuration
            Mage::getConfig()->reinit();
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Configuration saved'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getmessage().' : '.$e->getTraceAsString());
        }
        
        $this->_redirectReferer();
        
    }

    protected function _isAllowed() {
        return true;
    }
    
}
