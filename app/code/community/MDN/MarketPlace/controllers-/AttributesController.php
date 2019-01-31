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
class MDN_MarketPlace_AttributesController extends Mage_Adminhtml_Controller_Action {

    /**
     * Main screen
     */
    public function indexAction() {
        $this->loadLayout();
        
        $switcherBlock = $this->getLayout()->createBlock('MarketPlace/Attributes_Switcher')->setTemplate('MarketPlace/Attributes/Switcher.phtml');
        $this->getLayout()->getBlock('left')->append($switcherBlock);
        
        if($this->getrequest()->getParam('mp')){
            
            $tabsBlock = $this->getLayout()->createBlock(ucfirst($this->getRequest()->getParam('mp')).'/Attributes_Tabs');
            $this->getLayout()->getBlock('left')->append($tabsBlock);
            
        }
        
        $indexBlock = $this->getLayout()->createBlock('MarketPlace/Attributes_Index')->setTemplate('MarketPlace/Attributes/Index.phtml');
        $this->getLayout()->getBlock('content')->append($indexBlock);
        
        $this->_setActiveMenu('sales/marketplace/configuration/attributes');
        
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return true;
    }

}