<?php
/* 
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
 * @package MDN_Laredoute
 */

class MDN_Cdiscount_Block_Index_Tab_Brands_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Construct 
     */
    public function __construct(){
        parent::__construct();
        $this->setId('BrandsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     * 
     * @return type 
     */
    protected function _prepareCollection(){

        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_brand_attribute());
        $collection = $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                        ->setAttributeFilter($attribute->getData('attribute_id'))
                                        ->setStoreFilter(0, false);

        $this->setCollection($collection);
        return parent::_prepareCollection();

    }

    /**
     * Prepare columns
     * 
     * @return type 
     */
    public function _prepareColumns(){

        $this->addColumn('value', array(
            'header' => Mage::helper('MarketPlace')->__('Your brand'),
            'index' => 'value',
        ));

        $this->addColumn('cdiscount_brand', array(
                'header' => Mage::helper('MarketPlace')->__('Cdiscount brand'),
                'index' => 'value',
                'filter' => false,
                'renderer' => 'MDN_Cdiscount_Block_Widget_Grid_Column_Renderer_BrandAssociation',
                'sortable' => false
            ));

        return parent::_prepareColumns();

    }

    /**
     * Get grid parent html
     * 
     * @return type 
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }
    
    /**
     * get grid url (ajax use) 
     */
    public function getGridUrl() {
        return $this->getUrl('Cdiscount/Brands/GridAjax', array('_current' => true));
    }


    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('sync_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                        'label'     => Mage::helper('Cdiscount')->__('Synchronize'),
                        'onclick'   => "setLocation('".$this->getUrl('*/Brands/Sync')."')"
                    ))
        );

        $this->setChild('auto_association',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                        'label'     => Mage::helper('Cdiscount')->__('Automatic association'),
                        'onclick'   => "setLocation('".$this->getUrl('*/Brands/AutomaticAssociation')."')"
                    ))
        );
    }

    public function getSyncButtonHtml()
    {
        return $this->getChildHtml('sync_button');
    }

    public function getAutoAssociationButtonHtml()
    {
        return $this->getChildHtml('auto_association');
    }

    public function getMainButtonsHtml()
    {
        $html = '';
        if($this->getFilterVisibility()){
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
            $html.= $this->getSyncButtonHtml();
            $html.= $this->getAutoAssociationButtonHtml();
        }
        return $html;
    }

}
