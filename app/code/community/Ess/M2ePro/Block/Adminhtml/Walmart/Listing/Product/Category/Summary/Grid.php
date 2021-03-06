<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Category_Summary_Grid
    extends Ess_M2ePro_Block_Adminhtml_Category_Grid
{
    //########################################

    public function setProductsForEachCategory($productsForEachCategory)
    {
        $this->setData('products_for_each_category', $productsForEachCategory);
        return $this;
    }

    public function getProductsForEachCategory()
    {
        return $this->getData('products_for_each_category');
    }

    public function setProductsIds($productsIds)
    {
        $this->setData('products_ids', $productsIds);
        return $this;
    }

    public function getProductsIds()
    {
        return $this->getData('products_ids');
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingProductSourceCategoriesSummaryGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect('name');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                            ->from(
                                Mage::helper('M2ePro/Module_Database_Structure')
                                     ->getTableNameWithPrefix('catalog/category_product'),
                                'category_id'
                            )
                             ->where('`product_id` IN(?)', $this->getProductsIds());

        $collection->getSelect()->where('entity_id IN ('.$dbSelect->__toString().')');

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->addItem(
            'remove', array(
             'label'    => Mage::helper('M2ePro')->__('Remove'),
            )
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'magento_category', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Category'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnMagentoCategory')
            )
        );

        $this->addColumn(
            'action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'center',
            'width'     => '75px',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnMagentoCategory($value, $row, $column, $isExport)
    {
        $productsForEachCategory = $this->getProductsForEachCategory();

        return parent::callbackColumnMagentoCategory($value, $row, $column, $isExport) .
               ' ('.$productsForEachCategory[$row->getId()].')';
    }

    //########################################

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');
        return <<<HTML
<a  href="javascript:"
    onclick="ProductSourceCategoriesSummaryGridHandlerObj.selectByRowId('{$row->getId()}');
             ProductSourceCategoriesSummaryGridHandlerObj.remove()"
   >{$helper->__('Remove')}</a>
HTML;
    }

    //########################################

    protected function _toHtml()
    {
        $beforeHtml = <<<HTML
<style>

    div#{$this->getId()} div.grid {
        overflow-y: auto !important;
        height: 263px !important;
    }

    div#{$this->getId()} div.grid th {
        padding: 2px 4px !important;
    }

    div#{$this->getId()} div.grid td {
        padding: 2px 4px !important;
    }

    div#{$this->getId()} table.massaction div.right {
        display: block;
    }

    div#{$this->getId()} table.massaction td {
        padding: 1px 8px;
    }

</style>
HTML;

        $help = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_product_category_summary_help'
        );

        $beforeHtml .= <<<HTML
<div style="margin: 15px 0 10px 0">{$help->toHtml()}</div>
HTML;

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close()',
            )
        );
        $afterHtml = <<<HTML
<div class="clear"></div>
<div class="right" style="margin-top: 15px">
    {$button->toHtml()}
</div>
<div class="clear" style="padding: 10px 0"></div>

HTML;

        $urls = array();

        $path = 'adminhtml_walmart_listing_productAdd/removeSessionProductsByCategory';
        $urls['adminhtml_walmart_listing_productAdd/removeSessionProductsByCategory'] = $this->getUrl('*/'.$path);

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $js = '';
        if (!$this->getRequest()->getParam('grid')) {
            $js .= <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});

    ProductSourceCategoriesSummaryGridHandlerObj = new WalmartListingSummaryGridHandler(
        '{$this->getId()}'
    );
</script>
HTML;
        }

        $js .= <<<HTML
<script type="text/javascript">
    {$this->getCollection()->getSize()} || Windows.getFocusedWindow().close();
    ProductSourceCategoriesSummaryGridHandlerObj.afterInitPage();
</script>
HTML;

        if ($this->getRequest()->getParam('grid')) {
            $beforeHtml = $afterHtml = NULL;
        }

        return $beforeHtml . parent::_toHtml() . $afterHtml . $js;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getCurrentUrl(array('grid' => true));
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
