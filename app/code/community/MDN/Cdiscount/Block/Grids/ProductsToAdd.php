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
class MDN_Cdiscount_Block_Grids_ProductsToAdd extends Mage_Adminhtml_Block_Widget_Grid {
    /* @var string */

    protected $_join = 'left';
    /* @var string */
    protected $_mp = '';
    /* @var MDN_MarketPlace_Model_Countries */
    protected $_country = null;

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
        $this->setId('to_add_products_grid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Get condition
     * 
     * @return string 
     */
    protected function _getCondition() {

        /*$cond = ' (mp_marketplace_status is null or mp_marketplace_status = "notCreated") ';
        if ($this->getCountry()->getId())
            $cond .= " AND mp_marketplace_id='" . $this->getCountry()->getId() . "'";
        return $cond;*/
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
                ->setStoreId($this->getCountry()->getParam('store_id'))
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

        $collection->addFieldToFilter('mp_marketplace_status', array(array('null' => ''), 'notCreated'));

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

        // DISPLAY PRODUCT ID
        /* if (Mage::getStoreConfig('marketplace/general/grid_show_id') == 1) {
          $this->addColumn('id', array(
          'header' => Mage::helper('MarketPlace')->__('ID'),
          'index' => 'entity_id',
          'type' => 'range'
          ));
          } */

        // LAST UPDATED DATE
        /* $this->addColumn('mp_last_update', array(
          'header' => Mage::Helper('MarketPlace')->__('Last update'),
          'index' => 'mp_last_update',
          'type' => 'datetime'
          )); */

        // DISPLAY MANUFACTURER
        $this->addColumn('manufacturer', array(
            'header' => Mage::helper('MarketPlace')->__('Manufacturer'),
            'index' => Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_manufacturer_attribute(),
            'type' => 'options',
            'options' => $this->getManufacturerOptions(),
            'width' => '100px'
        ));

        // DISPLAY SKU
        $this->addColumn('sku', array(
            'header' => Mage::helper('MarketPlace')->__('Sku'),
            'index' => 'sku',
            'width' => '120px'
        ));


        // DISPLAY EAN
        $this->addColumn('ean', array(
            'header' => Mage::helper('MarketPlace')->__('Ean'),
            'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Barcode',
            'filter' => 'MarketPlace/Widget_Grid_Column_Filter_Barcode',
            'index' => Mage::helper('MarketPlace/Barcode')->getCollectionBarcodeIndex(),
            'type' => 'number',
            'align' => 'center',
            'sort' => false,
            'width' => '110px'
        ));


        // DISPLAY NAME
        $this->addColumn('name', array(
            'header' => Mage::helper('MarketPlace')->__('Product'),
            'index' => 'name'
        ));

        $this->addColumn('type_id', array(
            'header' => Mage::Helper('MarketPlace')->__('Type'),
            'index' => 'type_id',
            'type' => 'options',
            'options' => array('simple' => 'simple', 'configurable' => 'configurable', 'bundle' => 'bundle', 'grouped' => 'grouped', 'virtual' => 'virutal', 'downloadable' => 'downloadable')
        ));

        // ATTRIBUTE SET
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));

        // DISPLAY PRICE
        $this->addColumn('price', array(
            'header' => Mage::helper('MarketPlace')->__('Price excl tax'),
            'index' => 'price',
            'type' => 'price',
            'align' => 'right',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
            'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Price'
        ));

        // DISPLAY MARGIN
        /* if (Mage::getStoreConfig('marketplace/general/grid_show_margin') == 1) {
          $this->addColumn('margin', array(
          'header' => Mage::helper('MarketPlace')->__('Margin'),
          'index' => 'margin',
          'align' => 'right',
          'type' => 'number',
          'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Margin',
          ));
          } */

        // DISPLAY VISIBILITY
        $this->addColumn('visibility', array(
            'header' => Mage::helper('MarketPlace')->__('Visibility'),
            'width' => '150px',
            'index' => 'visibility',
            'type' => 'options',
            'align' => 'center',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        // DISPLAY STATUS
        $this->addColumn('status', array(
            'header' => Mage::helper('MarketPlace')->__('Enabled'),
            'width' => '70px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        // DISPLAY REFERENCE
        /* $this->addColumn('mp_reference', array(
          'header' => Mage::helper('MarketPlace')->__('Reference'),
          'index' => 'mp_reference',
          'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_EditorReference',
          'align' => 'center',
          'width' => '30px'
          )); */

        // DISPLAY EXCLUDE ?
        /* $this->addColumn('mp_exclude', array(
          'header' => Mage::helper('MarketPlace')->__('Exclude ?'),
          'index' => 'mp_exclude',
          'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_EditorExclude',
          'align' => 'center',
          'type' => 'options',
          'sortable' => false,
          'options' => $this->getYesNoOptions(),
          )); */

        // DISPLAY STOCKS
        $this->addColumn('Stocks', array(
            'header' => Mage::helper('MarketPlace')->__('Stocks'),
            'sortable' => false,
            'filter' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Stocks',
            'width' => '10px',
            'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Stocks',
            'index' => 'entity_id'
        ));

        // DISPLAY LAST EXPORTED VALUES
        /* $this->addColumn('last_export', array(
          'header' => Mage::Helper('MarketPlace')->__('Last export'),
          'sortable' => false,
          'filter' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Filter_LastExport',
          'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_LastExport',
          'index' => 'entity_id'
          )); */

        // allow other modules to add some columns
        Mage::dispatchEvent('marketplace_products_grid_addcolumns', array('grid' => $this));

        /* $this->addColumn('mp_update_status', array(
          'header' => Mage::helper('MarketPlace')->__('Update'),
          'index' => 'mp_update_status',
          'type' => 'options',
          'options' => Mage::getModel('MarketPlace/Data')->getUpdateStatusesAsCombo(),
          )); */

        $this->addColumn('add', array(
            'header' => Mage::Helper('MarketPlace')->__('Status'),
            'index' => 'mp_marketplace_status',
            'type' => 'options',
            'options' => Mage::Helper('MarketPlace/ProductCreation')->getStatusesAsCombo(),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        // EXPORT
        $this->addExportType('MarketPlace/Manual/exportCsv/country_id/' . Mage::registry('mp_country')->getId(), Mage::helper('customer')->__('CSV'));

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
                if ($row->getmp_message() != '') {
                    $title = 'title="' . $row->getmp_message() . '"';
                }
                $cell = '<span class="grid-severity-minor" ' . $title . '><span>' . $value . '</span></span>';
                break;
            case 'pending':
            case 'action_required':
                $cell = '<span class="grid-severity-minor"><span>' . $value . '</span></span>';
                break;
            case 'error':
                $cell = '<span class="grid-severity-critical" title="' . $row->getmp_message() . '"><span>' . $value . '</span></span>';
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

        /* $this->getMassactionBlock()->addItem('revise_products', array(
          'label' => Mage::helper('MarketPlace')->__('Revise products'),
          'url' => $this->getUrl('MarketPlace/Products/MassAddProducts', array('country_id' => $this->getCountry()->getId())),
          )); */

        if (Mage::helper(ucfirst($this->getMp()) . '/ProductCreation')->allowDeleteProduct()) {
            $this->getMassactionBlock()->addItem('delete_product', array(
                'label' => Mage::helper('MarketPlace')->__('Delete'),
                'url' => $this->getUrl('MarketPlace/Products/MassDeleteProducts', array('country_id' => $this->getCountry()->getId(), 'form_key' => Mage::getSingleton('core/session')->getFormKey())),
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
    public function getGridUrl() {
        return $this->getUrl('Cdiscount/Main/productsToAddGridAjax', array('_current' => true, 'country_id' => $this->getCountry()->getId()));
    }

    /**
     * get csv (delete html tags)
     * 
     * @return string 
     */
    public function getCsv() {
        return strip_tags(parent::getCsv());
    }

}
