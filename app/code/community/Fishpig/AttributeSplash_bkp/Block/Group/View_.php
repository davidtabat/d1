<?php

/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
class Fishpig_AttributeSplash_Block_Group_View extends Mage_Core_Block_Template {

    /**
     * Splash page collection
     *
     * @var Fishpig_AttributeSplash_Model_Mysql4_Page_Collection
     */
    protected $_splashPages = null;

    /**
     * Retrieve the splash group
     *
     * @return Fishpig_AttributeSplash_Model_Group
     */
    public function getSplashGroup() {
        if (!$this->hasSplashGroup()) {
            return Mage::registry('splash_group');
        }

        return $this->_getData('splash_group');
    }

    /**
     * Retrieve the splash page collection
     *
     * @return Fishpig_AttributeSplash_Model_Mysql4_Page_Collection
     */
    public function getSplashPages() {
        if (is_null($this->_splashPages)) {
            $this->_splashPages = $this->getSplashGroup()
                    ->getSplashPages()
                    ->addOrderBySortOrder();
        }

        return $this->_splashPages;
    }

    /**
     * Check if category display mode is "Products Only"
     *
     * @return bool
     */
    public function isProductMode() {
        return $this->getSplashGroup()->getDisplayMode() == Mage_Catalog_Model_Category::DM_PRODUCT;
    }

    /**
     * Check if category display mode is "Static Block and Products"
     *
     * @return bool
     */
    public function isMixedMode() {
        return $this->getSplashGroup()->getDisplayMode() == Mage_Catalog_Model_Category::DM_MIXED;
    }

    /**
     * Determine whether it is content mode (Static Block)
     *
     * @return bool
     */
    public function isContentMode() {
        return $this->getSplashGroup()->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE;
    }

    /**
     * Retrieves the HTML for the CMS block
     *
     * @return string
     */
    public function getCmsBlockHtml() {
        if (!$this->_getData('cms_block_html')) {
            $html = $this->getLayout()->createBlock('cms/block')
                            ->setBlockId($this->getSplashGroup()->getCmsBlock())->toHtml();

            $this->setCmsBlockHtml($html);
        }

        return $this->_getData('cms_block_html');
    }

    public function getThumbnailUrl($page) {
        return $this->helper('attributeSplash/image')->init($page, 'thumbnail')
                        ->keepFrame($page->thumbnailShouldKeepFrame())
                        ->resize($page->getThumbnailWidth(), $page->getThumbnailHeight());
    }

    /**
     * Determine whether to use the list display mode
     *
     * @return bool
     */
    public function isListMode() {
        return $this->getMode() == Fishpig_AttributeSplash_Model_Page::ATTRIBUTE_MODE_LIST;
    }

    /**
     * Determine whether to use the grid display mode
     *
     * @return bool
     */
    public function isGridMode() {
        return $this->getMode() == Fishpig_AttributeSplash_Model_Page::ATTRIBUTE_MODE_GRID;
    }

    /**
     * Determine whether to use the simple display mode
     *
     * @return bool
     */
    public function isSimpleMode() {
        return $this->getMode() == Fishpig_AttributeSplash_Model_Page::ATTRIBUTE_MODE_SIMPLE;
    }

    /**
     * Get the user-defined display mode
     *
     * @return int
     */
    public function getMode() {
        return Mage::getStoreConfig('attributeSplash/list_page/display_mode');
    }

    /**
     * Retrieve the amount of columns for grid view
     *
     * @return int
     */
    public function getColumnCount() {
        return $this->hasColumnCount() ? $this->getData('column_count') : Mage::getStoreConfig('attributeSplash/list_page/grid_column_count');
    }

    /**
     * Retrives the HTML for the pager
     *
     * @return string
     */
    public function getPagerHtml() {
        if ($block = $this->getPagerBlock()) {
            $block->setAvailableLimit(array($this->getLimit() => $this->getLimit()));
            $block->setLimit($this->getLimit());

            return $block->setCollection($this->getSplashGroup()->getSplashPages())->toHtml();
        }
    }

    /**
     * Retrieves the amount of items to display on one page
     *
     * @return int
     */
    protected function getLimit() {
        if (!$this->hasLimit()) {
            if ($this->isListMode()) {
                $key = 'list';
            } elseif ($this->isGridMode()) {
                $key = 'grid';
            } else {
                $key = 'simple';
            }

            $this->setLimit(Mage::getStoreConfig('attributeSplash/list_page/' . $key . '_per_page'));
        }

        return $this->getData('limit');
    }

    /**
     * Returns the block for the pager
     * If no block is set via the XML, one will NOT be created
     *
     * @return null|Mage_Page_Block_Html_Pager
     */
    public function getPagerBlock() {
        if (!$this->hasPagerBlock()) {
            if ($this->hasPagerBlockName()) {
                $this->setPagerBlock($this->getChild($this->getPagerBlockName()));
            }
        }

        return $this->getData('pager_block');
    }

}
