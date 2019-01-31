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
class MDN_Cdiscount_Block_Configuration_Country extends Mage_Adminhtml_Block_Widget_Form {
    
    protected $_country = null;
    
    /**
     * Construct 
     */
    public function __construct(){

        parent::_construct();
        
        $this->setTemplate('Cdiscount/Configuration/Country.phtml');
        
    }
    
    /**
     * Setter country
     * 
     * @param int $id 
     */
    public function setCountry($id){
        
        $tmp = explode('_', $id);
        
        $this->_country = Mage::getModel('MarketPlace/Countries')->load($tmp[1]);

    }
    
    /**
     * Getter country
     * 
     * @return MDN_MarketPlace_Model_Countries 
     */
    public function getCountry(){
        return $this->_country;
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
                    'onclick'   => 'countryForm.submit()',
                    'class' => 'save'
                ))
        );                
        
        return parent::_prepareLayout();
    }
    
    /**
     * Prepare form
     * 
     * @return type 
     */
    protected function _prepareForm(){
        
        $form = new Varien_Data_Form(array(
            'id' => 'countryForm',
            'name' => 'countryForm',
            'action' => $this->getUrl('MarketPlace/Configuration/saveCountry'),
            'method' => 'post',
            'onsubmit' => ''
        ));
        
        $form->addField(
                'mpac_account_id',
                'hidden',
                array(
                    'name' => 'data[column][mpac_account_id]',
                    'value' => $this->_country->getmpac_account_id()
                )
        );
        
        $form->addField(
                'mpac_country_codee',
                'hidden',
                array(
                    'name' => 'data[column][mpac_country_code]',
                    'value' => $this->_country->getmpac_country_code()
                )
        );
        
        $form->addField(
                'mpac_id',
                'hidden',
                array(
                    'name' => 'data[column][mpac_id]',
                    'value' => $this->_country->getmpac_id()
                )
        );
        
        $container = $form->addFieldSet(
                'container',
                array(
                    'class' => 'entry-edit'
                )
        );
        
        // main
        $mainFieldset = $container->addFieldset(
                'main_fieldset',
                array(
                    'legend' => $this->__('Main')
                )
        );
        
        $mainFieldset->addField(
                'active',
                'select',
                array(
                    'name' => 'data[params][active]',
                    'value' => $this->_country->getParam('active'),
                    'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                    'label' => $this->__('Active'),
                    'required' => true
                )
        );
        
        $mainFieldset->addField(
                'reset_action',
                'button',
                array(
                    'value' => $this->__('Process'),
                    'label' => $this->__('Reset update'),
                    'note' => $this->__('Note : this action will reset all updates for %s ', $this->_country->getParam('name')),
                    'onclick' => "setLocation('".$this->getUrl('MarketPlace/Products/resetLastUpdate', array('countryId' => $this->_country->getId()))."')",
                    'class' => 'scalable button'
                )
        );
        
        // scheduled tasks
        $scheduledTasksFieldset = $container->addFieldSet(
                'scheduled_tasks',
                array(
                    'legend' => $this->__('Scheduled Tasks')
                )
        );
        
        $scheduledTasksFieldset->addField(
                'enable_product_update',
                'select',
                array(
                    'name' => 'data[params][enable_product_update]',
                    'value' => $this->_country->getParam('enable_product_update'),
                    'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                    'label' => $this->__('Enable product update'),
                    'required' => true
                )
        );
        
        $scheduledTasksFieldset->addField(
                'enable_order_importation',
                'select',
                array(
                    'name' => 'data[params][enable_order_importation]',
                    'value' => $this->_country->getParam('enable_order_importation'),
                    'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                    'label' => $this->__('Enable order importation'),
                    'required' => true
                )
        );
        
        $scheduledTasksFieldset->addField(
                'enable_tracking_export',
                'select',
                array(
                    'name' => 'data[params][enable_tracking_export]',
                    'value' => $this->_country->getParam('enable_tracking_export'),
                    'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                    'label' => $this->__('Enable trackings export'),
                    'required' => true
                )
        );
        
        $scheduledTasksFieldset->addField(
                'enable_product_creation',
                'select',
                array(
                    'name' => 'data[params][enable_product_creation]',
                    'value' => $this->_country->getParam('enable_product_creation'),
                    'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                    'label' => $this->__('Enable product creation'),
                    'required' => true
                )
        );                                
        
        $pricesFieldset = $container->addFieldSet(
                'price_fieldset',
                array(
                    'legend' => $this->__('Prices')
                )
        );
        
        $pricesFieldset->addField(
           'price_attribute',
            'select',
            array(
                'name' => 'data[params][price_attribute]',
                'value' => $this->_country->getParam('price_attribute'),
                'options' => $this->_getAttributesValues(array('varchar', 'decimal')),
                'label' => $this->__('Price attribute'),
                'note' => $this->__('Must be a catalog_product_entity_decimal attribute<br/>Note : leave empty in order to disable this feature'),
                'required' => true
            )
        );
        
        $pricesFieldset->addField(
           'is_price_attribute_including_tax',
            'select',
            array(
                'name' => 'data[params][is_price_attribute_including_tax]',
                'value' => $this->_country->getParam('is_price_attribute_including_tax'),
                'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                'label' => $this->__('Is price attribute including taxes ?'),                
                'required' => true
            )
        );
        
        $pricesFieldset->addField(
                'price_coef',
                'text',
                array(
                    'name' => 'data[params][price_coef]',
                    'value' => $this->_country->getParam('price_coef'),
                    'label' => $this->__('Price coef'),
                    'note' => $this->__('Multiply price before sending it. (ie : 1.1 coef adds 10% to the price). Leave empty in order to disable this feature.')
                )
        );
        
        $pricesFieldset->addField(
                'taxes',
                'text',
                array(
                    'name' => 'data[params][taxes]',
                    'value' => $this->_country->getParam('taxes'),
                    'label' => $this->__('Taxes'),
                    'note' => $this->__('Note : taxes to apply.'),
                    'required' => true
                )
        );      
        
        $pricesFieldset->addField(
                'currency',
                'select',
                array(
                    'name' => 'data[params][currency]',
                    'value' => $this->_country->getParam('currency'),
                    'options' => $this->_getCurrenciesOptions(),
                    'label' => $this->__('Currency'),
                    'required' => true
                )
        ); 
        
        $pricesFieldset->addField(
                'margin_min',
                'text',
                array(
                    'name' => 'data[params][margin_min]',
                    'value' => $this->_country->getParam('margin_min'),
                    'label' => $this->__('Margin min'),
                    'note' => $this->__('Note : leave empty in order to disable this feature.')
                )
        );   
        
        $pricesFieldset->addField(
                'eco_part_attribute',
                'select',
                array(
                    'name' => 'data[params][eco_part_attribute]',
                    'value' => $this->_country->getParam('eco_part_attribute'),
                    'label' => $this->__('Eco part attribute'),
                    'options' => $this->_getAttributesValues()
                )
        );
        
        $pricesFieldset->addField(
                'eco_part_default',
                'text',
                array(
                    'name' => 'data[params][eco_part_default]',
                    'value' => $this->_country->getParam('eco_part_default'),
                    'label' => $this->__('Eco part (default)'),
                    'note' => $this->__('When this field is filled, it will be used instead of eco part attribute')
                )
        );
        
        $pricesFieldset->addField(
                'dea_attribute',
                'select',
                array(
                    'name' => 'data[params][dea_attribute]',
                    'value' => $this->_country->getParam('dea_attribute'),
                    'label' => $this->__('Dea attribute'),
                    'options' => $this->_getAttributesValues()
                )
        );
        
        $pricesFieldset->addField(
                'dea_default',
                'text',
                array(
                    'name' => 'data[params][dea_default]',
                    'value' => $this->_country->getParam('dea_default'),
                    'label' => $this->__('Dea (default)'),
                    'note' => $this->__('When this field is filled, it will be used instead of Dea attribute')
                )
        );
        
        $miscFieldset = $container->addFieldSet(
                'misc_fieldset',
                array(
                    'legend' => $this->__('Misc')
                )
        );                        
        
        $miscFieldset->addField(
                'store_id',
                'select',
                array(
                    'name' => 'data[params][store_id]',
                    'value' => $this->_country->getParam('store_id'),
                    'options' => $this->_getStoreIdOptions(),
                    'label' => $this->__('Store ID'),
                    'required' => true,
                    'note' => $this->__('Note : select store id associated to %s', $this->_country->getParam('name'))
                )
        );
        
        $miscFieldset->addField(
                'customer_group_id',
                'select',
                array(
                    'name' => 'data[params][customer_group_id]',
                    'value' => $this->_country->getParam('customer_group_id'),
                    'options' => $this->_getCustomersGroupIdOptions(),
                    'label' => $this->__('Customer group ID'),
                    'required' => true,
                    'note' => $this->__('Note : select group id for new customers')
                )
        );                             
        
        $miscFieldset->addField(
                'weight_unit',
                'select',
                array(
                    'name' => 'data[params][weight_unit]',
                    'value' => $this->_country->getParam('weight_unit'),
                    'options' => $this->_getWeightUnitOptions(),
                    'label' => $this->__('Weight unit'),
                    'required' => true
                )
        );           
        
        $productTitleFieldset = $container->addFieldset(
                'producttitle_fieldset',
                array(
                    'legend' => $this->__('Product title')
                )
        );  
        
        $productTitleFieldset->addField(
                'product_title_type',
                'select',
                array(
                    'name' => 'data[params][product_title_type]',
                    'value' => $this->_country->getParam('product_title_type'),
                    'options' => $this->_getProductTitleTypeOptions(),
                    'label' => $this->__('Product title type')
                )
        );
        
        $productTitleFieldset->addField(
                'product_custom_title',
                'text',
                array(
                    'name' => 'data[params][product_custom_title]',
                    'value' => $this->_country->getParam('product_custom_title'),
                    'label' => $this->__('Product custom title')
                )
        );
        
        $productTitleFieldset->addField(
                'product_title_style',
                'select',
                array(
                    'name' => 'data[params][product_title_style]',
                    'value' => $this->_country->getParam('product_title_style'),
                    'options' => $this->_getProductTitleStyleOptions(),
                    'label' => $this->__('Product title style')
                )
        );
        
        $shippingFieldset = $container->addFieldset(
                'shipping_fieldset',
                array(
                    'legend' => $this->__('Shipping')
                )
        );  
        
        $shippingFieldset->addField(
                'default_shipment_method',
                'select',
                array(
                    'name' => 'data[params][default_shipment_method]',
                    'label' => $this->__('Standard shipping method'),
                    'options' => $this->_getShipmentMethodOptions(false),
                    'value' => $this->_country->getParam('default_shipment_method')
                )
        );

        $shippingFieldset->addField(
            'registered_shipment_method',
            'select',
            array(
                'name' => 'data[params][registered_shipment_method]',
                'label' => $this->__('Registered shipping method'),
                'options' => $this->_getShipmentMethodOptions(false),
                'value' => $this->_country->getParam('registered_shipment_method')
            )
        );

        $shippingFieldset->addField(
            'tracking_shipment_method',
            'select',
            array(
                'name' => 'data[params][tracking_shipment_method]',
                'label' => $this->__('Tracking shipping method'),
                'options' => $this->_getShipmentMethodOptions(false),
                'value' => $this->_country->getParam('tracking_shipment_method')
            )
        );

        $shippingFieldset->addField(
                'default_shipment_delay',
                'text',
                array(
                    'name' => 'data[params][default_shipment_delay]',
                    'value' => $this->_country->getParam('default_shipment_delay'),
                    'label' => $this->__('Default shipment delay'),
                    'note' => $this->__('In days')
                )
        );
        
        $shippingFieldset->addField(
                'delta_tracking',
                'text',
                array(
                    'name' => 'data[params][delta_tracking]',
                    'value' => $this->_country->getParam('delta_tracking'),
                    'label' => $this->__('Delta tracking'),
                    'note' => $this->__('In hours')
                )
        );
        
        $shippingFieldset->addField(
                'default_tracking',
                'text',
                array(
                    'name' => 'data[params][default_tracking]',
                    'value' => $this->_country->getParam('default_tracking'),
                    'label' => $this->__('Default tracking'),
                    'note' => $this->__('This message will be used instead of tracking number')
                )
        );

        /*
        $autoSubmitFieldset = $container->addFieldset(
                'autosubmit_fieldset',
                array(
                    'legend' => $this->__('Auto submit')
                )
        );  
        
        // auto soumission
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoActive,
                'select',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoActive.']',
                    'value' => $this->getDataParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoActive),
                    'options' => $this->_getYesNoOptions(),
                    'label' => $this->__('Active'),
                    'note' => $this->__('Note : is auto submission active ?')
                )
        );              
        
        // visibility operator
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoVisibilityOperator,
                'select',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoVisibilityOperator.']',
                    'value' => $this->_country->getParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoVisibilityOperator),
                    'options' => Mage::Helper('MarketPlace/AutoSubmit')->getOperatorsGlobalAsArray(),
                    'label' => $this->__('Visibility operator')
                )
        );
        
        // visibility values
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoVisibilityValues,
                'multiselect',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoVisibilityValues.']',
                    'value' => $this->_country->getParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoVisibilityValues),
                    'values' => $this->_getVisibilityValuesOptions(),
                    'label' => $this->__('Visibility values')
                )
        );
        
        // brands operator
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoBrandOperator,
                'select',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoBrandOperator.']',
                    'value' => $this->_country->getParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoBrandOperator),
                    'options' => Mage::Helper('MarketPlace/AutoSubmit')->getOperatorsGlobalAsArray(),
                    'label' => $this->__('Brands operator')
                )
        );                
        
        // brands values
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoBrandValues,
                'multiselect',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoBrandValues.']',
                    'value' => $this->_country->getParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoBrandValues),
                    'values' => $this->_getBrandsValues(),
                    'label' => $this->__('Brands values')
                )
        );
        
        // attribute set operator
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoAttrSetOperator,
                'select',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoAttrSetOperator.']',
                    'value' => $this->_country->getParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoAttrSetOperator),
                    'options' => Mage::Helper('MarketPlace/AutoSubmit')->getOperatorsGlobalAsArray(),
                    'label' => $this->__('Attribute set operator')
                )
        );
        
        // attribute set values
        $autoSubmitFieldset->addField(
                MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoAttrSetValues,
                'multiselect',
                array(
                    'name' => 'data[params]['.MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoAttrSetValues.']',
                    'value' => $this->_country->getParam(MDN_MarketPlace_Model_Accounts::kParamSoumissionAutoAttrSetValues),
                    'values' => $this->_getAttributeSetValues(),
                    'label' => $this->__('Attribute set values')
                )
        );
        
        foreach(Mage::Helper('MarketPlace/AutoSubmit')->getCustomAttributes() as $name){
            
            // custom attribute one
            $autoSubmitFieldset->addField(
                'so_attribute'.$name,
                'select',
                array(
                    'name' => 'data[params][so_attribute'.$name.']',
                    'value' => $this->_country->getParam('so_attribute'.$name),
                    'options' => $this->_getAttributesValues(),
                    'label' => $this->__('Custom attribute %s', $name)
                )
            );

            // custom attribute one operator
            $autoSubmitFieldset->addField(
                    'so_attribute'.$name.'operator',
                    'select',
                    array(
                        'name' => 'data[params][so_attribute'.$name.'operator]',
                        'value' => $this->_country->getParam('so_attribute'.$name.'operator'),
                        'options' => Mage::Helper('MarketPlace/AutoSubmit')->getOperatorsGlobalAsArray(),
                        'label' => $this->__('Custom attribute %s operator', $name)
                    )
            );

            // custom attribute one values
            $autoSubmitFieldset->addField(
                    'so_attribute'.$name.'values',
                    'text',
                    array(
                        'name' => 'data[params][so_attribute'.$name.'values]',
                        'value' => $this->_country->getParam('so_attribute'.$name.'values'),
                        'label' => $this->__('Custom attribute %s values', $name),
                        'note' => $this->__('Coma separated values')
                    )
            );
            
        }
        
        $autoSubmitFieldset->addField(
                'autosubmit_action',
                'button',
                array(
                    'value' => $this->__('Process'),
                    'label' => $this->__('Auto submit'),
                    'note' => $this->__('Note : this action will submit products on %s ', $this->_country->getParam('name')),
                    'onclick' => "setLocation('".$this->getUrl('MarketPlace/Products/autoSubmit', array('countryId' => $this->_country->getId()))."')",
                    'class' => 'scalable button'
                )
        );                
        */

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
        
    }  
    
    /**
     * Get default shipment methods
     * 
     * @return array $retour 
     */
    public function _getShipmentMethodOptions($empty = false){
        
        $retour = array();
        $carriers = Mage::getStoreConfig('carriers', 0);

        if ($empty)
            $retour[''] = '';

        foreach ($carriers as $carrierKey => $item) {

            //skip shipping method if not enabled
            if (Mage::getStoreConfigFlag('carriers/' . $carrierKey . '/active') != 1)
                continue;

            $instance = mage::getModel($item['model']);
            $code = $item['model'];
            if ($item['model']) {
                $model = mage::getModel($item['model']);
                $allowedMethods = $model->getAllowedMethods();
                if ($allowedMethods) {
                    foreach ($allowedMethods as $methodKey => $method) {
                        $retour[$carrierKey . '_' . $methodKey] = $instance->getConfigData('title') . ' - ' . $method;                 
                    }
                }
            }
        }
        
        return $retour;
        
    }
    
    /**
     * get product title types
     * 
     * @return array 
     */
    protected function _getProductTitleTypeOptions(){
        
        return Mage::Helper('MarketPlace/ProductCreation')->getProductTitleTypes();
        
    }
    
    /**
     * Get product title style
     * 
     * @return array 
     */
    protected function _getProductTitleStyleOptions(){
        
        return Mage::Helper('MarketPlace/ProductCreation')->getProductTitleStyles();
        
    }
    
    /**
     * Get currencies options
     * 
     * @return array $retour 
     */
    protected function _getCurrenciesOptions(){
        
        $retour = array();
        $currencies = Mage::getModel('Core/Locale_Config')->getAllowedCurrencies();
        
        foreach($currencies as $currency)
            $retour[$currency] = $currency;
        
        ksort($retour);
        return $retour;
        
    }
    
    /**
     * Get weight unit options
     * 
     * @return array $retour 
     */
    protected function _getWeightUnitOptions(){
        
        $retour = array();
        
        $retour = array(
                    'kg' => 'Kilograms',
                    'lb' => 'Pounds'              
                  );
        
        return $retour;
        
    }
    
    /**
     * Get store id options
     * 
     * @return array $retour 
     */
    protected function _getStoreIdOptions(){
        
        $retour = array();
        $stores = mage::getModel('Core/Store')
                    ->getCollection();

        foreach($stores as $store){
            $retour[$store->getstore_id()] = $store->getname();          
        }

        return $retour;
    }
    
    /**
     * Get customers group id options
     * 
     * @return array $retour 
     */
    protected function _getCustomersGroupIdOptions(){
        
        $retour = array();

        $groups = mage::getModel('Customer/group')
                    ->getCollection();

        foreach($groups as $group){
            $retour[$group->getcustomer_group_id()] = $group->getcustomer_group_code();
        }

        return $retour;
    }
    
    /**
     * Get header
     * 
     * @return string 
     */
    protected function _getHeader(){
        return $this->__('Edit %s', $this->_country->getParam('name'));
    }
    
    /**
     * Get visibility options
     * 
     * @return array $retour 
     */
    protected function _getVisibilityValuesOptions(){
        
        $retour = array();
        
        $values = Mage::getModel('catalog/product_visibility')->getOptionArray();
        
        foreach($values as $k => $v){
            $retour[] = array(
                'label' => $v,
                'value' => $k
            );
        }
                        
        return $retour;        
    }
   
    /**
     * Get margins options
     * 
     * @return array $retour 
     */
    protected function _getMarginValues(){
        $retour = array();
        for($i = 0; $i <= 100; $i++)
            $retour[$i] = $i.' %';
        
        return $retour;
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
     * Get brabds values
     * 
     * @return array $retour 
     */
    protected function _getBrandsValues() {

        $retour = array();
        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('attribute_code', Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_manufacturer_attribute());
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        foreach($manufacturers as $manufacturer)
            $retour[] = array(
                'value' => $manufacturer['value'],
                'label' => $manufacturer['label']
            );
        
        return $retour;
    }
    
    /**
     * Get attributes set values
     * 
     * @return array $retour 
     */
    protected function _getAttributeSetValues() {

        $retour = array();
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();
        
        foreach($sets as $k => $v){
            $retour[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        
        return $retour;
    }

    /**
     * get attributes values
     *
     * @return array $options
     */
    protected function _getAttributesValues($type = null) {

        $options = array();
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId);

        if($type !== null)
        {
            if (!is_array($type))
                $type = array($type);
            $attributes->addFieldToFilter('backend_type', array('in' => $type));
        }

        //add empty
        $options[] = '';

        foreach ($attributes as $attribute) {

            $options[$attribute->getAttributeCode()] = $attribute->getName();

        }

        return $options;
    }
    
}
