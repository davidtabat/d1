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
class MDN_MarketPlace_Block_Configuration extends Mage_Adminhtml_Block_Widget_Form {
    
    const XML_PATH_PAYMENT_METHODS = 'payment';
    
    /* @var */
    protected $_congig = null;
    
    /**
     * Construct 
     */
    public function __construct(){

        parent::_construct();
        
        $this->setTemplate('MarketPlace/Configuration.phtml');
        
    }
    
    /**
     * Prepare layout
     * 
     * @return type 
     */
    protected function _prepareLayout(){
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save'),
                    'onclick'   => 'marketplaceConfigurationForm.submit()',
                    'class' => 'save'
                ))
        );                
        
        return parent::_prepareLayout();
    }
    
    /**
     * get config
     * 
     * @return type 
     */
    public function getConfig(){
        
        if($this->_config === null){
            
            $this->_config = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject();
            
        }
        
        return $this->_config;
        
    }
    
    /**
     * Prepare form
     * 
     * @return type 
     */
    protected function _prepareForm(){
        
        $form = new Varien_Data_Form(array(
            'id' => 'marketplaceConfigurationForm',
            'name' => 'marketplaceConfigurationForm',
            'action' => $this->getUrl('MarketPlace/Configuration/saveGeneral'),
            'method' => 'post',
            'onsubmit' => ''
        ));
        
        $container = $form->addFieldSet(
                'container',
                array(
                    'class' => 'entry-edit'
                )
        );
        
        // products
        $productFieldset = $container->addFieldset(
                'product_config_fieldset',
                array(
                    'legend' => $this->__('Products')
                )
        );
        
        if(Mage::Helper('MarketPlace')->isErpInstalled()){
            $productFieldset->addField(
                    'mp_use_erp_barcode',
                    'select',
                    array(
                        'name' => 'data[mp_use_erp_barcode]',
                        'label' => $this->__('Use ERP barcode ?'),
                        'options' => $this->_getYesNoOptions(),
                        'value' => ($this->getConfig()->getmp_use_erp_barcode()) ? $this->getConfig()->getmp_use_erp_barcode() : 1
                    )
            );
        }
        
        $productFieldset->addField(
                'mp_barcode_attribute',
                'select',
                array(
                    'name' => 'data[mp_barcode_attribute]',
                    'label' => $this->__('Barcode attribute'),
                    'options' => $this->_getAttributeOptions(),
                    'value' => $this->getConfig()->getmp_barcode_attribute()
                )
        );
        
        $productFieldset->addField(
                'mp_manufacturer_attribute',
                'select',
                array(
                    'name' => 'data[mp_manufacturer_attribute]',
                    'label' => $this->__('Manufacturer attribute'),
                    'options' => $this->_getAttributeOptions(),
                    'value' => ($this->getConfig()->getmp_manufacturer_attribute()) ? $this->getConfig()->getmp_manufacturer_attribute() : 'manufacturer'
                )
        );
        
        $productFieldset->addField(
                'mp_brand_attribute',
                'select',
                array(
                    'name' => 'data[mp_brand_attribute]',
                    'label' => $this->__('Brand attribute'),
                    'value' => ($this->getConfig()->getmp_brand_attribute()) ? $this->getConfig()->getmp_brand_attribute() : 'manufacturer',
                    'options' => $this->_getAttributeOptions()
                )
        );
        
        // categories
        $categoryFieldset = $container->addFieldset(
                'category_config_fieldset',
                array(
                    'legend' => $this->__('Categories')
                )
        );
        
        $categoryFieldset->addField(
                'mp_max_category_depth',
                'text',
                array(
                    'name' => 'data[mp_max_category_depth]',
                    'label' => $this->__('Category depth for association'),
                    'value' => ($this->getConfig()->getmp_max_category_depth()) ? $this->getConfig()->getmp_max_category_depth() : 2
                )
        );
        
        $categoryFieldset->addField(
                'mp_root_category',
                'multiselect',
                array(
                    'name' => 'data[mp_root_category]',
                    'label' => $this->__('Root category'),
                    'value' => unserialize($this->getConfig()->getmp_root_category()),
                    'values' => $this->_getCategoryOptions()
                )
        );
        
        // orders
        $orderFieldset = $container->addFieldset(
                'order_config_fieldset',
                array(
                    'legend' => $this->__('Orders')
                )
        );
        
        $orderFieldset->addField(
                'mp_generate_invoice',
                'select',
                array(
                    'name' => 'data[mp_generate_invoice]',
                    'label' => $this->__('Generate invoices'),
                    'value' => $this->getConfig()->getmp_generate_invoice(),
                    'options' => $this->_getYesNoOptions()
                )
        );
        
        $orderFieldset->addField(
                'mp_order_status',
                'select',
                array(
                    'name' => 'data[mp_order_status]',
                    'label' => $this->__('Order State'),
                    'options' => $this->_getOrderStateOptions(),
                    'value' => $this->getConfig()->getmp_order_status()
                )
        );
        
        $orderFieldset->addField(
                'mp_default_payment_method',
                'select',
                array(
                    'name' => 'data[mp_default_payment_method]',
                    'label' => $this->__('Default payment method'),
                    'options' => $this->_getPaymentOptions(),
                    'value' => $this->getConfig()->getmp_default_payment_method()
                )
        );              
        
        // orders
        $debugFieldset = $container->addFieldset(
                'debug_config_fieldset',
                array(
                    'legend' => $this->__('Debug')
                )
        );
        
        $debugFieldset->addField(
                'mp_bug_report',
                'text',
                array(
                    'name' => 'data[mp_bug_report]',
                    'label' => $this->__('Email bug report'),
                    'value' => $this->getConfig()->getmp_bug_report()
                )
        );
        
        $debugFieldset->addField(
                'mp_debug_mode',
                'select',
                array(
                    'name' => 'data[mp_debug_mode]',
                    'label' => $this->__('Display tool tab'),
                    'value' => ($this->getConfig()->getmp_debug_mode()) ? $this->getConfig()->getmp_debug_mode() : 1,
                    'options' => $this->_getYesNoOptions()
                )
        );    
        
        $debugFieldset->addField(
                'mp_stack_trace',
                'select',
                array(
                    'name' => 'data[mp_stack_trace]',
                    'label' => $this->__('Display stack trace in error messages'),
                    'value' => ($this->getConfig()->getmp_stack_trace()) ? $this->getConfig()->getmp_stack_trace() : 0,
                    'options' => $this->_getYesNoOptions()
                )
        ); 
        
        // logs
        $logsFieldset = $container->addFieldset(
                'logs_fieldset',
                array(
                    'legend' => $this->__('Logs')
                )
        );       
        
        $logsFieldset->addField(
                'mp_max_log',
                'text',
                array(
                    'name' => 'data[mp_max_log]',
                    'label' => $this->__('Max'),
                    'value' => ($this->getConfig()->getmp_max_log()) ? $this->getConfig()->getmp_max_log() : 500
                )
        );

        $logsFieldset->addField(
            'prune_feeds_action',
            'button',
            array(
                'value' => $this->__('Prune'),
                'label' => $this->__('Prune feeds'),
                'onclick' => "setLocation('".$this->getUrl('MarketPlace/Feed/prune')."')",
                'class' => 'scalable button'
            )
        );
     
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
        
    }
    
    /**
     * Get attributes
     * 
     * @return array $retour 
     */
    protected function _getAttributeOptions(){
        
        $retour = array('' => '');
        
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($entityTypeId);        

        foreach ($attributes as $attribute) {
            $retour[$attribute->getAttributeCode()] = $attribute->getName();
        }
        
        return $retour;
        
    }
    
    /**
     * Get yes/no
     * 
     * @return array 
     */
    protected function _getYesNoOptions(){
        
        return array(
            '0' => $this->__('No'),
            '1' => $this->__('yes')
        );
        
    }
    
    /**
     * Get payments
     * 
     * @return array $retour 
     */
    protected function _getPaymentOptions(){
        
        $retour = array();
        
        $methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, null);
        $res = array();

        foreach ($methods as $code => $methodConfig) {
            $prefix = self::XML_PATH_PAYMENT_METHODS.'/'.$code.'/';

            if (!$model = Mage::getStoreConfig($prefix.'model', null)) {
                continue;
            }

            $retour[Mage::getModel($model)->getcode()] = Mage::getModel($model)->getcode();
        }
        
        return $retour;
        
    }
    
    /**
     * Get order statuses
     * 
     * @return array $retour 
     */
    protected function _getOrderStateOptions(){       
        
        $retour = array();
        
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        
        foreach($statuses as $code => $label){
            
            $retour[$code] = $label;
            
        } 
        
        return $retour;
        
    }
    
    /**
     * Get categories
     * 
     * @return array $retour 
     */
    protected function _getCategoryOptions(){
        
        $retour = array();
        
        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
            ->addPathFilter('^1/[0-9]+$')
            ->load();
        
        foreach ($collection as $category) {
            $retour[] = array(
                'value' => $category->getId(),
                'label' => $category->getName()
            );
        }        
        
        return $retour;
        
    }
    
    /**
     * Get customer group options
     * 
     * @return array $retour 
     */
    protected function _getCustomerGroupOptions(){
        
        $retour = array();
        
         $groups = mage::getModel('Customer/group')
                        ->getCollection();

        foreach($groups as $group){
            $retour[$group->getcustomer_group_id()] = $group->getcustomer_group_code();
        }
        
        return $retour;
        
    }
        
    
}
