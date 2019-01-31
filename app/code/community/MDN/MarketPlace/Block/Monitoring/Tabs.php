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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace 
 * @version 2.1
 */
class MDN_MarketPlace_Block_Monitoring_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    
    /**
     * Construct 
     */
    public function __construct() {

        parent::__construct();

        $this->setId('marketplace_monitoring_tabs');
        $this->setDestElementId('marketplace_monitoring_tabs_content');
        $this->setTitle(Mage::helper('MarketPlace')->__('Marketplaces'));
    }

    /**
     * Before HTML
     * 
     * @return type 
     */
    protected function _beforeToHtml() {

        foreach(Mage::Helper('MarketPlace')->getMarketplacesName() as $name){
            
            $this->addTab(strtolower($name).'_monitoring_tab', array(
                'label' => ucfirst($name),
                'content' => $this->getLayout()->createBlock('MarketPlace/Monitoring_Tabs_Infos')->setMp($name)->toHtml(),
            ));
            
        }

        //set active tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = strtolower($name).'_monitoring_tab';
        $this->setActiveTab($defaultTab);

        return parent::_beforeToHtml();
    }
    
}
