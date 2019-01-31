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
class MDN_Cdiscount_Block_Configuration_Main extends Mage_Adminhtml_Block_Widget_Form {
    
    /* @vars $_mp */
    protected $_mp = '';
    
    /**
     * Construct 
     */
    public function __construct(){

        parent::_construct();
        
        $this->setTemplate('Cdiscount/Configuration/Main.phtml');
        
    }
    
    /**
     * Setter mp
     * 
     * @param string $value 
     */
    public function setMp($value){
        $this->_mp = $value;
    }
    
    /**
     * Getter mp
     * 
     * @return string 
     */
    public function getMp(){
        return $this->_mp;
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
                    'onclick'   => 'mainForm.submit()',
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
        
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration($this->_mp);                
        
        $form = new Varien_Data_Form(array(
            'id' => 'mainForm',
            'name' => 'mainForm',
            'action' => $this->getUrl('MarketPlace/Configuration/saveMain'),
            'method' => 'post',
            'onsubmit' => ''
        ));
        
        $form->addField(
                'mpc_id',
                'hidden',
                array(
                    'name' => 'data[mpc_id]',
                    'value' => $config->getmpc_id(),
                )
        );
        
        $form->addField(
                'mpc_marketplace_id',
                'hidden',
                array(
                    'name' => 'data[column][mpc_marketplace_id]',
                    'value' => $config->getmpc_marketplace_id()
                )
        );
        
        $fieldset = $form->addFieldSet(
                'main_fieldset',
                array(
                    'legend' => $this->__('Configuration'),
                )
        );

        $fieldset->addField(
            'disable_barcode',
            'select',
            array(
                'name' => 'data[params][disableBarcode]',
                'value' => $config->getdisableBarcode(),
                'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                'label' => $this->__('Disable barcode ?'),
                'note' => $this->__('If you do not have barcode for your product, set yes'),
                'required' => false
            )

        );

        $fieldset->addField(
            'remove_barcode_for_update',
            'select',
            array(
                'name' => 'data[params][removeBarcodeForUpdate]',
                'value' => $config->getremoveBarcodeForUpdate(),
                'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                'label' => $this->__('Remove barcode for update feed'),
                'required' => false
            )

        );

        $fieldset->addField(
           'stock_attribute',
            'select',
            array(
                'name' => 'data[params][stockAttribute]',
                'value' => $config->getstockAttribute('varchar'),
                'options' => $this->_getAttributesAsOptions(),
                'label' => $this->__('Stock attribute'),
                'note' => $this->__('Must be a catalog_product_entity_varchar attribute.<br/>Note : leave empty in order to disable this feature.'),
                'required' => true
            )
        );
        
        // decription attribute
        $fieldset->addField(
           'description_attribute',
            'select',
            array(
                'name' => 'data[params][descriptionAttribute]',
                'value' => $config->getdescriptionAttribute(),
                'options' => $this->_getAttributesAsOptions(),
                'label' => $this->__('Description attribute'),
                'note' => $this->__('Note : leave empty in order to disable this feature. Product description field will be used by default')
            )
        );
        
        $fieldset->addField(
           'categories',
            'multiselect',
            array(
                'name' => 'data[params][categories]',
                'value' => $config->getcategories(),
                'values' => $this->_getCategoriesOptions(),
                'label' => $this->__('Categories'),
                'note' => $this->__('Select used categories')
            )
        );

        if ($this->getCountry())
        {
            $fieldset->addField(
                'create_custom_categories',
                'button',
                array(
                    'value' => $this->__('Build'),
                    'label' => $this->__('Build custom categories file'),
                    'onclick' => "setLocation('".$this->getUrl('Cdiscount/Debug_Service/BuildCustomCategoriesFile', array('countryId' => $this->getCountry()->getId()))."')",
                    'class' => 'scalable button'
                )
            );
        }
        
        $fieldset->addField(
            'max_to_export',
            'text',
            array(
                'name' => 'data[params][max_to_export]',
                'value' => $config->getmax_to_export(),
                'label' => $this->__('Max to export'),
                'note' => $this->__('Set exported products limit')
            )
        );
        
        $fieldset->addField(
            'log_last_request',
            'select',
            array(
                'name' => 'data[params][logLastRequest]',
                'value' => $config->getlogLastRequest(),
                'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
                'label' => $this->__('Log last request ?'),
                'note' => $this->__('Enable this option will log all Cdiscount queries'),
                'required' => false
            )
                
        );
        
        // zip method
        $fieldset->addField(
            'zip_method',
            'select',
            array(
                'name' => 'data[params][zipMethod]',
                'value' => $config->getzipMethod(),
                'options' => array('zip' => $this->__('zip'), 'zip_archive' => $this->__('Zip Archive')),
                'label' => $this->__('Zip method'),
                'note' => $this->__('Select zip method to use'),
                'required' => true
            )
                
        );

        $fieldsetCatalogImport = $form->addFieldSet(
            'catalog_import_fieldset',
            array(
                'legend' => $this->__('Catalog Import Settings'),
            )
        );

        $fieldsetCatalogImport->addField(
            'catalog_extract_reference_index',
            'select',
            array(
                'name' => 'data[params][catalogExtractReferenceIndex]',
                'value' => $config->getcatalogExtractReferenceIndex(),
                'options' => $this->_getCatalogImporIndexes(),
                'label' => $this->__('Cdiscount catalog reference index'),
                'note' => $this->__('Set in which column is displayed Cdiscount reference in exported excel report (count from zero)'),
                'required' => false
            )

        );

        $fieldsetCatalogImport->addField(
            'catalog_extract_sku_index',
            'select',
            array(
                'name' => 'data[params][catalogExtractSkuIndex]',
                'value' => $config->getcatalogExtractSkuIndex(),
                'options' => $this->_getCatalogImporIndexes(),
                'label' => $this->__('Cdiscount catalog seller product reference index'),
                'note' => $this->__('Set in which column is displayed seller reference in exported excel report (count from zero)'),
                'required' => false
            )

        );
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
        
    }

    /**
     * Get catalog indexes
     *
     * @return array
     */
    protected function _getCatalogImporIndexes(){

        $indexes = array();

        for($i = 0; $i <= 60; $i++){

            $indexes[$i] = $i;

        }

        return $indexes;

    }
    
    /**
     * Get categories
     * 
     * @return array $retour 
     */
    protected function _getCategoriesOptions(){
        
        $retour = array();
        
        $categories = Mage::Helper('Cdiscount/Category')->getCategories();                     

        foreach($categories as $k => $v){                                

            if($k == "")
                continue;

            $retour[] = array(
                'value' => $k,
                'label' => $v
            );

        }
        
        return $retour;
        
    }
    
    /**
     * Get attributes
     * 
     * @return array $retour 
     */
    protected function _getAttributesAsOptions($type = null){
        
        $retour = array();
        
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($entityTypeId);

        if($type !== null)
            $attributes->addFieldToFilter('backend_type', $type);

        //add empty
        $retour[] = '';           

        foreach ($attributes as $attribute) {
            $retour[$attribute->getAttributeCode()] = $attribute->getName();            
        }

        return $retour;
    }
    
    /**
     * Get header
     * 
     * @return string 
     */
    protected function _getHeader(){
        return $this->__('Main');
    }

    protected function getCountry()
    {
        $countries = Mage::getModel('MarketPlace/Countries')->getActiveCountries(null, $this->_mp);
        foreach($countries as $country)
        {
            return $country;
        }
    }
    
}
