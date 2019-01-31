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
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Configuration_Main extends Mage_Adminhtml_Block_Template {
 
    /**
     * Prepare layout (set current configuration view)
     * 
     * @return type 
     */
    protected function _prepareLayout(){
        
        if($this->getRequest()->getParam('type') && $this->getRequest()->getParam('mp')){
            
            $type = $this->getRequest()->getParam('type');
            $mp = $this->getRequest()->getParam('mp');
            
            $block = $this->getLayout()->createBlock(ucfirst($mp).'/Configuration_'.ucfirst($type));
            
            switch($type){
                
                case 'main':
                    $block->setMp(strtolower($mp));
                    break;
                case 'account':
                    $block->setAccountId($this->getRequest()->getParam('account'));
                    break;
                case 'country':
                    $block->setCountry($mp.'_'.$this->getRequest()->getParam('country'));
                    break;
                default:
                    break;
            }                        
            
        }else{
            
            $block = $this->getLayout()->createBlock('MarketPlace/Configuration');
            
        }
        
        $this->setChild('content', $block);
        
        return parent::_prepareLayout();
        
    }
    
}
