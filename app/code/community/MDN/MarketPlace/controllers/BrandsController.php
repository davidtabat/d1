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
 */
class MDN_MarketPlace_BrandsController extends Mage_Adminhtml_Controller_Action {
    
    public function MassDeleteAction(){
        
        try{
            
            $ids = $this->getRequest()->getParam('mpb_ids');
            
            $collection = Mage::getModel('MarketPlace/Brands')->getCollection()
                                ->addFieldToFilter('mpb_id', array('in' => $ids));
            
            foreach($collection as $item)
                $item->delete();
            
            Mage::getSingleton('adminhtml/session')->addSuccess('Brands deleted');
            
        }catch(Exception $e){
            
            Mage::getsSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            
        }
        
        $this->_redirectReferer();
        
    }

    protected function _isAllowed() {
        return true;
    }
    
}
