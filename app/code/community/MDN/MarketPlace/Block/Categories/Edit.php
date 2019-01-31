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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Categories_Edit extends Mage_Adminhtml_Block_Widget_Form {
    /* @var Mage_Catalog_Model_Category */

    private $_category = null;

    /**
     * Return html controls to set up category association
     * 
     * @return string $html
     */
    public function getCategoryForm() {
        $html = '';

        try {
            $name = 'association_data';
            $value = mage::getModel('MarketPlace/Category')->getAssociationValue($this->getCategory()->getId(), $this->getMarketPlace()->getMarketPlaceName());
            $html = $this->getMarketPlace()->getCategoryForm($name, $value);
        } catch (Exception $ex) {
            $html = '<font color="red">' . $ex->getMessage() . '</font>';
        }

        return $html;
    }

    /**
     * Return current category
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory() {
        if ($this->_category == null) {
            $categoryId = $this->getRequest()->getParam('category_id');
            $this->_category = mage::getModel('catalog/category')->load($categoryId);
        }
        return $this->_category;
    }

    /**
     * Return current market place helper
     * 
     * @return array
     */
    public function getMarketPlace() {
        $marketPlaceName = $this->getRequest()->getParam('marketplace');
        return mage::helper('MarketPlace')->getHelperByName($marketPlaceName);
    }

    /**
     * Return category tree
     * 
     * @return string $html
     */
    public function getCategoryInformation() {
        $html = '';
        $category = $this->getCategory();
        $t = explode('/', $category->getPath());
        $indent = 0;
        $isFirst = true;
        foreach ($t as $cat) {
            if ($isFirst) {
                $isFirst = false;
                continue;
            }
            $cat = mage::getModel('catalog/category')->load($cat);
            $indentText = '';
            for ($i = 0; $i <= $indent; $i++)
                $indentText .= '&nbsp;';
            $html .= $indentText . '| ' . $cat->getName() . '<br>';
            $indent += 5;
        }
        return $html;
    }

}