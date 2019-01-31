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
class MDN_MarketPlace_OrdersController extends Mage_Adminhtml_Controller_Action {

    /**
     * Index 
     */
    public function indexAction(){

        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Grid ajax action 
     */
    public function GridAjaxAction(){
        
        try{
            
            $block = $this->getLayout()->createBlock('MarketPlace/Orders_Tabs_Grid');
            $this->getResponse()->setBody(
                $block->toHtml()
            );
            
        }catch(Exception $e){
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
            
        }
        
    }

}
