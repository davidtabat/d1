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
class MDN_MarketPlace_Block_Orders_Tabs_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    
    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct();
        $this->setId('OrdersGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return <type>
     */
    protected function _prepareCollection() {

        $collection = $this->getOrdersCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Get orders collection
     *
     * @return collection $collection
     */
    public function getOrdersCollection() {

        $marketplaces = array();
        $collection = null;
        
        $helpers = mage::helper('MarketPlace')->getHelpers();
        $prefix = Mage::getConfig()->getTablePrefix();

        if(count($helpers) > 0){

            for($i=0; $i<count($helpers);$i++){
                if(Mage::helper($helpers[$i])->isDisplayedInSalesOrderSummary()){
                    $marketplaces[$i] = strtolower(Mage::helper($helpers[$i])->getMarketPlaceName());
                }
                
            }

            if (Mage::helper('MarketPlace/FlatOrder')->isFlatOrder()) {

                $collection = mage::getModel('sales/order')
                                ->getCollection();
                $collection->addFieldToFilter('from_site', array('in', $marketplaces));
                $collection->getSelect()->joinLeft(array('s1' => $prefix.'sales_flat_order_address'), 'main_table.shipping_address_id = s1.entity_id', array('shipping_firstname'=>'firstname', 'shipping_lastname' => 'lastname', 'shipping_name' => 'firstname'));
                $collection->getSelect()->joinLeft(array('s2' => $prefix.'sales_flat_order_address'), 'main_table.billing_address_id = s2.entity_id', array('billing_firstname' => 'firstname', 'billing_lastname' => 'lastname', 'billing_name' => 'firstname'));

            } else {

                $collection = mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('from_site', array('in', $marketplaces))
                        ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                        ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                        ->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
                        ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                        ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                        ->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
                        ->addExpressionAttributeToSelect('billing_name',
                                'CONCAT({{billing_firstname}}, " ", {{billing_lastname}}, " (", {{billing_company}}, ")")',
                                array('billing_firstname', 'billing_lastname', 'billing_company'))
                        ->addExpressionAttributeToSelect('shipping_name',
                                'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}}, " (", {{shipping_company}}, ")")',
                                array('shipping_firstname', 'shipping_lastname', 'shipping_company'));
            }

            $collection->addAttributeToSort('entity_id', 'desc');
        }
        
        return $collection;
    }

    /**
     * Prepare columns
     */
    protected function _prepareColumns() {

        // ORDER ID
        $this->addColumn('increment_id', array(
            'header' => mage::helper('sales')->__('Order #'),
            'index' => 'increment_id'
        ));

        // CREATED AT
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        // BILLING NAME
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'entity_id',
            'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Order_BillingName',
            'filter' => false,
            'sortable' => false
            //'filter' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Order_BillingName'
        ));

        // SHIPPING NAME
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'entity_id',
            'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Order_ShippingName',
            'filter' => false,
            'sortable' => false
            //'filter' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Order_ShippingName'
        ));

        // GRAND TOTAL
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        // STATUS
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        // FROM SITE
        $this->addColumn('from_site', array(
            'header' => Mage::helper('MarketPlace')->__('From site'),
            'index' => 'from_site',
            'type' => 'options',
            'options' => Mage::helper('MarketPlace')->getMarketPlaceOptions()
        ));

        // MARKETPLACE ORDER ID
        $this->addColumn('marketplace_order_id', array(
            'header' => Mage::helper('MarketPlace')->__('Marketplace order id'),
            'index' => 'marketplace_order_id',
        ));

        // DISPLAY LINK TO PRODUCT INFORMATIONS
        $this->addColumn('action',
                array(
                    'header' => Mage::helper('MarketPlace')->__('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => Mage::helper('MarketPlace')->__('View'),
                            'url' => array('base' => 'adminhtml/sales_order/view/'),
                            'field' => 'order_id'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
        ));
    }

    /**
     * Get grid parent html
     *
     * @return <type>
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Get row url
     *
     * @param object $row
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
    }
    
    /**
     * get grid url (ajax use) 
     */
    public function getGridUrl() {
        return $this->getUrl('MarketPlace/Orders/GridAjax', array('_current' => true));
    }
    
}
