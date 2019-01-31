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
class MDN_MarketPlace_Block_Products extends Mage_Adminhtml_Block_Widget_Grid {
    /* @var string */

    protected $_mp = '';
    /* @var MDN_MarketPlace_Model_Countries */
    protected $_country = null;
    protected $_join = 'inner';

    /**
     * Getter Account
     * 
     * @return MDN_MarketPlace_Model_Account 
     */
    public function getAccount() {
        return Mage::getModel('MarketPlace/Countries')->getAccountByCurrentCountryId($this->getRequest()->getParam('country_id'));
    }

    /**
     * Getter country
     * 
     * @return MDN_MarketPlace_Model_Countries 
     */
    public function getCountry() {
        if (!$this->_country)
            $this->_country = Mage::registry('mp_country');        
        return $this->_country;
    }

    /**
     * Getter mp
     * 
     * @return string 
     */
    public function getMp() {
        if (!$this->_mp) {
            $this->_mp = $this->getAccount()->getmpa_mp();
        }
        return $this->_mp;
    }

    /**
     * Construct 
     */
    public function __construct() {

        parent::__construct();
        $this->setId('ProductsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('mp_products_filter');
    }

    protected function _getCondition() {
        return ($this->getCountry()->getId()) ? "mp_marketplace_id='" . $this->getCountry()->getId() . "'" : '1';
    }

    /**
     * Prepare collection
     * 
     * @return type 
     */
    protected function _prepareCollection() {

        $cond = $this->_getCondition();

        //load collection
        $collection = mage::getResourceModel('catalog/product_collection')
                //->setStoreId($this->getCountry()->getParam('store_id'))
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('cost')
                ->addAttributeToSelect(Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_manufacturer_attribute()) // on n'arrive pas a le retrouver à cause du store id.... :(
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image');

        // is custom price attribute ?
        if ($this->getCountry()->getParams('price_attribute'))
            $collection->addAttributeToSelect($this->getCountry()->getParams('price_attribute'));

        // is custom stock attribute
        $account = Mage::getModel('MarketPlace/Accounts')->load($this->getCountry()->getmpac_account_id());
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration($account->getmpa_mp());
        if ($config->getstockAttribute())
            $collection->addAttributeToSelect($config->getstockAttribute());

        $collection->joinTable(
                'MarketPlace/Data', 'mp_product_id=entity_id', array(
            'mp_reference' => 'mp_reference',
            'mp_exclude' => 'mp_exclude',
            'mp_force_qty' => 'mp_force_qty',
            'mp_delay' => 'mp_delay',
            'mp_marketplace_id' => 'mp_marketplace_id',
            'mp_marketplace_status' => 'mp_marketplace_status',
            'mp_free_shipping' => 'mp_free_shipping',
            'mp_force_export' => 'mp_force_export',
            'mp_product_id' => 'mp_product_id',
            'mp_message' => 'mp_message',
            'mp_id' => 'mp_id',
            'mp_last_update' => 'mp_last_update',
            'mp_update_status' => 'mp_update_status',
            'mp_last_stock_sent' => 'mp_last_stock_sent',
            'mp_last_delay_sent' => 'mp_last_delay_sent',
            'mp_last_price_sent' => 'mp_last_price_sent'
                ), $cond, $this->_join
        );

        $collection->addExpressionAttributeToSelect(
                'margin', 'round(({{price}} - {{cost}}) / {{price}} * 100, 2)', array('price', 'cost')
        );

        //add barcode attribute
        $collection = mage::helper('MarketPlace/Barcode')->addBarcodeAttributeToProductCollection($collection);

        // dispatch event for adding some other custom attribute
        Mage::dispatchEvent('marketplace_products_grid_collection', array('collection' => $collection));
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     * 
     * @return type 
     */
    protected function _prepareColumns() {

        return parent::_prepareColumns();
    }

    /**
     * Decorate status
     * 
     * @param srring $value
     * @param Varien_Object $row
     * @param type $column
     * @param type $isExport
     * @return string $cell
     */
    public function decorateStatus($value, $row, $column, $isExport) {

        $class = '';
        $title = '';

        switch ($row->getmp_marketplace_status()) {

            case 'created':
                $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
                break;
            case 'incomplete':
            case 'pending':
            case 'action_required':
                $cell = '<font color="orange">'.$row->getmp_message().'</font>';
                break;
            case 'error':
                $cell = '<font color="red">'.$row->getmp_message().'</font>';
                break;
            default :
                $cell = '<span class="grid-severity-major"><span>Not Created</span></span>';
                break;
        }

        return $cell;
    }

    /**
     * Get row URL
     * 
     * @param type $row
     * @return string 
     */
    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId()));
    }

    /**
     * get grid parent HTML
     * 
     * @return type 
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Get manufacturer options
     * 
     * @return array $retour
     */
    public function getManufacturerOptions() {

        $retour = array();

        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('attribute_code', Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_manufacturer_attribute());
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        foreach ($manufacturers as $manufacturer) {
            $retour[$manufacturer['value']] = $manufacturer['label'];
        }

        return $retour;
    }

    /**
     * Get yes no options
     * 
     * @return array 
     */
    public function getYesNoOptions() {
        return array('0' => 'No', '1' => 'Yes');
    }

    /**
     * Prepare mass actions
     * 
     * @return MDN_MarketPlace_Block_Products 
     */
    protected function _prepareMassaction() {

        $this->setMassactionIdField('mp_marketplace_id');
        $this->getMassactionBlock()->setFormFieldName('product_ids');

        if (Mage::helper(ucfirst($this->getMp()) . '/ProductCreation')->allowMatchingEan()) {
            $this->getMassactionBlock()->addItem('matching_ean', array(
                'label' => Mage::helper('MarketPlace')->__('Matching EAN'),
                'url' => $this->getUrl('MarketPlace/Products/MassMatchingEAN', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey()))
            ));
        }

        $this->getMassactionBlock()->addItem('add_selection', array(
            'label' => Mage::helper('MarketPlace')->__('Add to marketplace'),
            'url' => $this->getUrl('MarketPlace/Products/MassAddProducts', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
        ));

        if (Mage::helper(ucfirst($this->getMp()))->allowReviseProducts()) {
            $this->getMassactionBlock()->addItem('revise_products', array(
                'label' => Mage::helper('MarketPlace')->__('Revise products'),
                'url' => $this->getUrl('MarketPlace/Products/MassReviseProducts', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
                'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
            ));
        }

        if (Mage::helper(ucfirst($this->getMp()) . '/ProductCreation')->allowDeleteProduct()) {
            $this->getMassactionBlock()->addItem('delete_product', array(
                'label' => Mage::helper('MarketPlace')->__('Delete'),
                'url' => $this->getUrl('MarketPlace/Products/MassDeleteProducts', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
                'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
            ));
        }

        if (Mage::helper(ucfirst($this->getMp()) . '/ProductCreation')->allowGenerateProductFeed()) {
            $this->getMassactionBlock()->addItem('generate_product_file', array(
                'label' => Mage::helper('MarketPlace')->__('Generate product feed'),
                'url' => $this->getUrl('MarketPlace/Products/MassGenerateProductFeed', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
            ));
        }
        if (Mage::helper(ucfirst($this->getMp()))->allowManualUpdate()) {
            $this->getMassactionBlock()->addItem('update_stock_price', array(
                'label' => Mage::helper('MarketPlace')->__('Update stock & price'),
                'url' => $this->getUrl('MarketPlace/Products/MassUpdateStockPrice', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
                'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
            ));

            $this->getMassactionBlock()->addItem('update_image', array(
                'label' => Mage::helper('MarketPlace')->__('Update image'),
                'url' => $this->getUrl('MarketPlace/Products/MassUpdateImage', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
                'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
            ));
        }

        if (Mage::helper(ucfirst($this->getMp()))->allowFreeShipping()) {

            $this->getMassactionBlock()->addItem('add_free_shipping', array(
                'label' => Mage::helper('MarketPlace')->__('Enable free shipping'),
                'url' => $this->getUrl('MarketPlace/Products/MassUpdateFreeShipping', array('country_id' => $this->getCountry()->getId(), 'value' => '1', 'form_key' => Mage::getSingleton('core/session')->getFormKey()))
            ));

            $this->getMassactionBlock()->addItem('remove_free_shipping', array(
                'label' => Mage::helper('MarketPlace')->__('Disable free shipping'),
                'url' => $this->getUrl('MarketPlace/Products/MassUpdateFreeShipping', array('country_id' => $this->getCountry()->getId(), 'value' => '0', 'form_key' => Mage::getSingleton('core/session')->getFormKey()))
            ));
        }

        $this->getMassactionBlock()->addItem('set_to_notCreated', array(
            'label' => Mage::helper('MarketPlace')->__('Set as not created'),
            'url' => $this->getUrl('MarketPlace/Products/MassUpdateStatus', array('_current' => true, 'country_id' => $this->getCountry()->getId(), 'status' => Mage::helper('MarketPlace/ProductCreation')->getStatusNotCreated(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
            'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
        ));

        $this->getMassactionBlock()->addItem('set_to_pending', array(
            'label' => Mage::helper('MarketPlace')->__('Set as pending'),
            'url' => $this->getUrl('MarketPlace/Products/MassUpdateStatus', array('_current' => true, 'country_id' => $this->getCountry()->getId(), 'status' => Mage::helper('MarketPlace/ProductCreation')->getStatusPending(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
            'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
        ));

        $this->getMassactionBlock()->addItem('set_to_created', array(
            'label' => Mage::helper('MarketPlace')->__('Set as created'),
            'url' => $this->getUrl('MarketPlace/Products/MassUpdateStatus', array('_current' => true, 'country_id' => $this->getCountry()->getId(), 'status' => Mage::helper('MarketPlace/ProductCreation')->getStatusCreated(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
            'confirm' => Mage::helper('MarketPlace')->__('Are you sure ?')
        ));
        
        return $this;
    }

    /**
     * get grid url (ajax use) 
     */
    /* public function getGridUrl() {
      return $this->getUrl('MarketPlace/Products/GridAjax', array('_current' => true, 'country_id' => $this->getRequest()->getParam('country_id')));
      } */

    /**
     * get csv (delete html tags)
     * 
     * @return string 
     */
    public function getCsv() {
        return strip_tags(parent::getCsv());
    }

}
