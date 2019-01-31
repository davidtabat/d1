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
 * @package MDN_Cdiscount
 * @version 2.0
 */
class MDN_Cdiscount_Block_Configuration_Account extends Mage_Adminhtml_Block_Widget_Form {
    
    /* @vars $_account */
    protected $_account = null;
    /* @vars $_params */
    protected $_params = null;   
    /* @vars $_accountId */
    protected $_accountId = null;
    
    /**
     * Construct 
     */
    public function __construct(){

        parent::_construct();
        
        $this->setTemplate('Cdiscount/Configuration/Account.phtml');
        
    }
    
    /**
     * Setter account id
     * 
     * @param int $value 
     */
    public function setAccountId($value){
        $this->_accountId = $value;
    }
    
    /**
     * Getter account id
     * 
     * @return int 
     */
    public function getAccountId(){
        return $this->_accountId;
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
                    'onclick'   => 'accountForm.submit()',
                    'class' => 'save'
                ))
        );            
        
        return parent::_prepareLayout();
        
    }
    
    /**
     * Before html
     * 
     * @return type 
     */
    protected function _beforeToHtml(){
        
        
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Delete'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('MarketPlace/Configuration/deleteAccount', array('account' => $this->_accountId)).'\')',
                    'class' => 'delete'
                ))
        );
        
        return parent::_beforeToHtml();
    }
    
    /**
     * Prepare form
     * 
     * @return type 
     */
    protected function _prepareForm(){
        
        $form = new Varien_Data_Form(array(
                'id' => 'accountForm',
                'name' => 'accountForm',
                'action' => $this->getUrl('MarketPlace/Configuration/saveAccount'),
                'method' => 'post',
                'onsubmit' => ''
        ));            
        
        $mainFieldset = $form->addFieldset(
                'main_fieldset',
                array(
                    'legend' => $this->__('Main')
                )
        );
        
        $apiFieldset = $form->addFieldset(
                'api_fieldset',
                array(
                    'legend' => $this->__('API'),                   
                )
        );                
        
        // account name
        $mainFieldset->addField(
                'mpa_name',
                'text',
                array(
                    'name' => 'data[column][mpa_name]',
                    'value' => $this->getAccount()->getmpa_name(),
                    'label' => $this->__('Account name'),
                    'required' => true,
                    'class' => 'validate-email'
                )
        );
        
        // marketplace id
        $form->addField(
                'mpa_mp',
                'hidden',
                array(
                    'name' => 'data[column][mpa_mp]',
                    'value' => Mage::Helper('Cdiscount')->getMarketPlaceName()
                )
        );
        
        $form->addField(
                'mpa_id',
                'hidden',
                array(
                    'name' => 'data[column][mpa_id]',
                    'value' => $this->getAccount()->getmpa_id()
                )
        );
        
        // login
        $apiFieldset->addField(
                'login',
                'text',
                array(
                    'name' => 'data[params][login]',
                    'value' => $this->getDataParam('login'),
                    'label' => $this->__('Login'),
                    'required' => true
                )
        );
        
        // password
        $apiFieldset->addField(
                'password',
                'text',
                array(
                    'name' => 'data[params][password]',
                    'value' => $this->getDataParam('password'),
                    'label' => $this->__('Password'),
                    'required' => true
                )
        );
        
        // seller product ID
        $apiFieldset->addField(
                'seller_product_reference',
                'select',
                array(
                    'name' => 'data[params][seller_product_reference]',
                    'value' => $this->getDataParam('seller_product_reference'),
                    'label' => $this->__('Seller product ID'),
                    'required' => true,
                    'options' => array('sku' => $this->__('SKU'), 'id' => $this->__('Product ID'))
                )
        );
        
        // use sandbox
        $apiFieldset->addField(
                'use_sandbox',
                'select',
                array(
                    'name' => 'data[params][use_sandbox]',
                    'value' => $this->getDataParam('use_sandbox'),
                    'options' => $this->_getYesNoOptions(),
                    'label' => $this->__('Use sandbox ?')
                )
        );
        
        // is active
        $mainFieldset->addField(
                'is_active',
                'select',
                array(
                    'name' => 'data[params][is_active]',
                    'value' => $this->getDataParam('is_active'),
                    'options' => $this->_getYesNoOptions(),
                    'required' => true,
                    'label' => $this->__('Active'),
                    'note' => $this->__('Note : is current account active ?')
                )
        );                               
        
        // prefix
        $mainFieldset->addField(
            'package_prefix',
            'text',
            array(
                'name' => 'data[params][package_prefix]',
                'value' => ($this->getDataParam('package_prefix') != '') ? $this->getDataParam('package_prefix') : 'PREFIX',
                'label' => $this->__('Package prefix'),
                'note' => $this->__('Set package prefix')
            )
        );
        
        // order_importation_start_date
        $mainFieldset->addField(
            'order_importation_start_date',
            'text',
            array(
                'name' => 'data[params][order_importation_start_date]',
                'value' => ($this->getDataParam('order_importation_start_date') != '') ? $this->getDataParam('order_importation_start_date') : date('Y-m-d\TH:i:s'),
                'label' => $this->__('Max order importation date'),
                'note' => $this->__('Set orders importation start date. <br/>ex : '.date('Y-m-d\TH:i:s'))
            )
        );
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
        
    }
    
    /**
     * Ge tyes / no options
     * 
     * @return array 
     */
    protected function _getYesNoOptions(){
        
        return array(
            '' => '',
            '0' => $this->__('No'),
            '1' => $this->__('Yes')
         );
        
    }
    

    /**
     * Get params
     * 
     * @return array  
     */
    public function getParams() {

        if ($this->_params === null) {
            $this->_params = unserialize($this->getAccount()->getmpa_params());
        }

        return $this->_params;
    }

    /**
     * Get account
     * 
     * @return MDN_MarketPlace_Model_Account 
     */
    public function getAccount() {

        if ($this->_account === null) {    
            
            $this->_account = Mage::getModel('MarketPlace/Accounts')->load($this->_accountId);
        }

        return $this->_account;
    }

    /**
     * Get data params
     * 
     * @param string $label
     * @return string $retour 
     */
    public function getDataParam($label) {

        $retour = '';
        $data = $this->getParams();

        if (is_array($data) && array_key_exists($label, $data))
            $retour = $data[$label];

        return $retour;
    }

    /**
     * get attributes values
     * 
     * @return array $options 
     */
    protected function _getAttributesValues() {

        $options = array();
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($entityTypeId);

        //add empty
        $options[] = '';

        foreach ($attributes as $attribute) {
            $options[$attribute->getAttributeCode()] = $attribute->getName();
        }
        
        return $options;
    }  
    
    /**
     * get header
     * 
     * @return string 
     */
    protected function _getHeader(){
        
        if($this->_accountId)
            $header = $this->__('%s',$this->getAccount()->getmpa_name());
        else
            $header = $this->__('Create a new account');
        
        return $header;
    }
    
}
