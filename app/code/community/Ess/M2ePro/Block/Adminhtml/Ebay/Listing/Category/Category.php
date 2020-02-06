<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Category
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategory');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_category_category';
        // ---------------------------------------

        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Set eBay Categories (Based On Magento Categories)');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/*/', array('step' => 1, '_current' => true));
        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'next', array(
            'id'        => 'ebay_listing_category_continue_btn',
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => "EbayListingCategoryCategoryGridHandlerObj.completeCategoriesDataStep()"
            )
        );
        // ---------------------------------------
    }

    //########################################

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $parentHtml = parent::_toHtml();
        $popupsHtml = $this->getPopupsHtml();

        return <<<HTML
<div id="products_progress_bar"></div>
<div id="products_container">{$parentHtml}</div>
<div style="display: none">{$popupsHtml}</div>
HTML;
    }

    //########################################

    protected function getPopupsHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_WarningPopup $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_warningPopup');
        $block->setCategoryGridJsHandler('EbayListingCategoryCategoryGridHandlerObj');

        return $block->toHtml();
    }

    //########################################
}
