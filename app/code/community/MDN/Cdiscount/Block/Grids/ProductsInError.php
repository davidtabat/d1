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
class MDN_Cdiscount_Block_Grids_ProductsInError extends MDN_MarketPlace_Block_Products {

    /**
     * Construct 
     */
    public function __construct() {

        parent::__construct();
        $this->setId('error_products_grid');
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
        return ($this->getCountry()->getId()) ? 'mp_marketplace_id="' . $this->getCountry()->getId() . '" AND mp_marketplace_status IN ("error", "action_required")' : '1';
    }

    /**
     * get grid url (ajax use) 
     */
    public function getGridUrl() {
        return $this->getUrl('Cdiscount/Main/productsInErrorGridAjax', array('_current' => true, 'country_id' => $this->getCountry()->getId()));
    }

    /**
     * Prepare columns
     * 
     * @return type 
     */
    protected function _prepareColumns() {

        // DISPLAY PRODUCT ID
        /*if (Mage::getStoreConfig('marketplace/general/grid_show_id') == 1) {
            $this->addColumn('id', array(
                'header' => Mage::helper('MarketPlace')->__('ID'),
                'index' => 'entity_id',
                'type' => 'range'
            ));
        }*/

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

        /* $this->addColumn('type_id', array(
          'header' => Mage::Helper('MarketPlace')->__('Type'),
          'index' => 'type_id',
          'type' => 'options',
          'options' => array('simple' => 'simple', 'configurable' => 'configurable', 'bundle' => 'bundle', 'grouped' => 'grouped', 'virtual' => 'virutal', 'downloadable' => 'downloadable')
          )); */

        // ATTRIBUTE SET
        /* $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
          ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
          ->load()
          ->toOptionHash();

          $this->addColumn('set_name', array(
          'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
          'width' => '100px',
          'index' => 'attribute_set_id',
          'type' => 'options',
          'options' => $sets,
          )); */

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
        /*if (Mage::getStoreConfig('marketplace/general/grid_show_margin') == 1) {
            $this->addColumn('margin', array(
                'header' => Mage::helper('MarketPlace')->__('Margin'),
                'index' => 'margin',
                'align' => 'right',
                'type' => 'number',
                'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Margin',
            ));
        }*/

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

        $this->addColumn('mp_message', array(
            'header' => Mage::helper('MarketPlace')->__('Message'),
            'index' => 'mp_message',
            'sortable' => false
        ));

        // EXPORT
        $this->addExportType('MarketPlace/Manual/exportCsv/country_id/' . Mage::registry('mp_country')->getId(), Mage::helper('customer')->__('CSV'));

        return parent::_prepareColumns();
    }

}
