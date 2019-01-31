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
class MDN_MarketPlace_Block_Feed_FeedGrid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Construct
     */
    public function __construct() {

        parent::__construct();
        $this->setId('Feeds');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Load collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        //charge
        $collection = mage::getModel('MarketPlace/Feed')->getCollection()->setOrder('mp_id', 'DESC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

     /**
     * Grid configuration
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('mp_id', array(
                'header'=> Mage::helper('sales')->__('Id'),
                'index' => 'mp_id'
        ));

        $this->addColumn('mp_feed_id', array(
                'header'=> Mage::helper('MarketPlace')->__('Feed id'),
                'index' => 'mp_feed_id'
        ));

        $this->addColumn('mp_marketplace_id', array(
                'header'=> Mage::helper('MarketPlace')->__('Marketplace'),
                'index' => 'mp_marketplace_id',
                'type'  => 'options',
                'options' => Mage::helper('MarketPlace')->getMarketPlaceOptions()
        ));
        
        $this->addColumn('mp_country', array(
                'header' => Mage::Helper('MarketPlace')->__('Country'),
                'index' => 'mp_country',
                'type' => 'options',
                'options' => $this->_getCountries()
        ));

        $this->addColumn('mp_status', array(
                'header'=> Mage::helper('MarketPlace')->__('Status'),
                'index' => 'mp_status',
                'type' => 'options',
                'options' => Mage::helper('MarketPlace/Feed')->getFeedStatusOptions()
        ));

        $this->addColumn('mp_type', array(
                'header'=> Mage::helper('MarketPlace')->__('Type'),
                'index' => 'mp_type',
                'type' => 'options',
                'options' => Mage::helper('MarketPlace/Feed')->getFeedTypeOptions()
        ));

        $this->addColumn('mp_content', array(
                'header' => Mage::helper('MarketPlace')->__('Content'),
                'index' => 'mp_content',
                'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Feed_Content',
                'filter' => false
        ));

        $this->addColumn('mp_response', array(
                'header' => Mage::helper('MarketPlace')->__('Response'),
                'index' => 'mp_response',
                'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Feed_Response',
                'filter' => false
        ));

        $this->addColumn('mp_date', array(
                'header'=> Mage::helper('MarketPlace')->__('Date'),
                'index' => 'mp_date',
                'type' => 'datetime'
        ));
        
        $this->addColumn('feed_result', array(
            'header' => Mage::Helper('MarketPlace')->__('Action'),
            'index' => 'feed_result',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Feed_Action'
        ));

        return parent::_prepareColumns();

    }

    /**
     * Get grid parent html
     * 
     * @return type 
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    /**
     * Get countries
     * 
     * @return array $retour 
     */
    protected function _getCountries(){
        
        $retour = array();
        
        $collection = Mage::getModel('MarketPlace/Countries')->getCollection();
        
        foreach($collection as $item){
            
            // retrieve account
            $account = $item->getAccountByCountryId($item->getId());
            
            $retour[$item->getId()] = $account->getmpa_name().' - '.$item->getmpac_country_code();
            
        }
        
        return $retour;
        
    }
    
    /**
     * get grid URL
     * 
     * @return type 
     */
    public function getGridUrl(){
        return $this->getUrl('MarketPlace/Feed/gridAjax', array('_current' => true));
    }

}
