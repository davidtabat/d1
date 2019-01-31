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
class MDN_MarketPlace_Block_Orders_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    /**
     * Construct 
     */
    public function __construct() {

        parent::__construct();

        $this->setId('marketplace_orders_tabs');
        $this->setDestElementId('marketplace_orders_tabs_content');
    }

    /**
     * Before HTML
     * 
     * @return type 
     */
    protected function _beforeToHtml() {

        $this->addTab('marketplace_orders_grid', array(
            'label' => Mage::helper('MarketPlace')->__('Orders'),
            'content' => $this->getLayout()->createBlock('MarketPlace/Orders_Tabs_Grid')->toHtml(),
        ));

        $this->addTab('orders_stats', array(
            'label' => Mage::helper('MarketPlace')->__('Statistics'),
            'content' => $this->getLayout()->createBlock('MarketPlace/Orders_Tabs_Statistics')->toHtml(),
        ));

        //set active tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = 'marketplace_orders_grid';
        $this->setActiveTab($defaultTab);

        return parent::_beforeToHtml();
    }

}
