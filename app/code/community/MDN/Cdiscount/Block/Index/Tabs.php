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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Block_Index_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {

        parent::__construct();
        $this->setId('cdiscount_index_tab');
        $this->setDestElementId('cdiscount_index_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');

    }

    protected function _beforeToHtml(){

        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_ProductsUpToDate');
        $html = $block->toHtml();
        $this->addTab('products_created_up_to_date', array(
            'label' => Mage::helper('MarketPlace')->__("Products Added Up To Date (%s)", $block->getChild('up_to_date_products')->getCollection()->getSize()),
            'content' => $html,
        ));
        
        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_ProductsWaitingForUpdate');
        $html = $block->toHtml();
        $this->addTab('products_created_waiting_for_update', array(
            'label' => Mage::helper('MarketPlace')->__("Products Added Waiting For Update (%s)", $block->getChild('waiting_for_update_products')->getCollection()->getSize()),
            'content' => $html,
        ));

        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_ProductsToAdd');
        $html = $block->toHtml();
        $this->addTab('products_to_add', array(
            'label' => Mage::helper('MarketPlace')->__('Products to Add (%s)', $block->getChild('products_to_add_grid')->getCollection()->getSize()),
            'content' => $html,
        ));

        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_ProductsPending');
        $html = $block->toHtml();
        $this->addTab('products_pending', array(
            'label' => Mage::helper('MarketPlace')->__('Products pending response (%s)', $block->getChild('products_pending_grid')->getCollection()->getSize()),
            'content' => $html,
        ));

        $block = $this->getLayout()->createBlock('Cdiscount/Index_Tab_ProductsInError');
        $html = $block->toHtml();
        $this->addTab('products_in_error', array(
            'label' => Mage::helper('MarketPlace')->__('Products with error (%s)', $block->getChild('products_in_error_grid')->getCollection()->getSize()),
            'content' => $html,
        ));

        $this->addTab('manual_import', array(
            'label' => Mage::Helper('MarketPlace')->__('Manual import'),
            'content' => $this->getLayout()->createBlock('Cdiscount/Index_Tab_ManualImport')->toHtml()
        ));

        $logsBlock = $this->getLayout()->createBlock('MarketPlace/Index_Tab_Logs');
        $this->addTab('logs_grid', array(
            'label' => Mage::helper('MarketPlace')->__('Logs'),
            'content' => $logsBlock->toHtml()
        ));

        $feedsBlock = $this->getLayout()->createBlock('MarketPlace/Index_Tab_Feed');
        $this->addTab('feed_grid', array(
            'label' => Mage::helper('MarketPlace')->__('Feeds'),
            'content' => $feedsBlock->toHtml()
        ));       

        if (Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_debug_mode()) {
            $this->addTab('debug', array(
                'label' => Mage::helper('MarketPlace')->__('Tools'),
                'content' => $this->getLayout()->createBlock('Cdiscount/Index_Tab_Debug')->toHtml()
            ));
        }
        
        $brandsBlock = $this->getLayout()->createBlock('Cdiscount/Index_Tab_Brands');
        $this->addTab('brands', array(
            'label' => Mage::helper('MarketPlace')->__('Brands'),
            'content' => $brandsBlock->toHtml()
        ));  

        //set active tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = 'general';
        $this->setActiveTab($defaultTab);

        return parent::_beforeToHtml();
    }

}
