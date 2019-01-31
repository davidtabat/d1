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
class MDN_MarketPlace_Helper_Categories extends Mage_Core_Helper_Abstract {

    /**
     * Return categories
     */
    public function getCategories() {
        $categories = array();
        $rootCategories = $this->getRootCategories();
        
        foreach($rootCategories as $rootCategory)
            $this->parseCategoryRecursive($rootCategory, $categories, 0);

        return $categories;
    }

    /**
     * Parse categories recursively and fill flat array
     */
    protected function parseCategoryRecursive($currentCategory, &$categories, $depth) {
        if ($depth > $this->getMaxDepth())
            return;

        //get sub categories
        $children = $currentCategory->getChildren(true);
        $children = explode(',', $children);
        foreach ($children as $childId) {
            if (!$childId)
                continue;

            $category = mage::getModel('catalog/category')->load($childId);
            if ($category->getis_active() == 0)
                continue;
            $category->setDepth($depth);
            $categories[] = $category;
            $this->parseCategoryRecursive($category, $categories, $depth + 1);
        }
    }

    /**
     * Get root category
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getRootCategory() {
        $rootCategoryId = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_root_category();                
        
        if ($rootCategoryId)
            return mage::getModel('catalog/category')->load($rootCategoryId);
        else {

            Mage::getSingleton('adminhtml/session')->addError('Root category is not set');
            $url = Mage::Helper('adminhtml')->getUrl('MarketPlace/Configuration/index', array());
            header('location:' . $url);
            exit();
        }
    }
    
    /**
     * Get root categories
     * 
     * @return array $retour
     */
    public function getRootCategories(){
        $retour = array();
        
        $data = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_root_category();
        
        $ids = unserialize($data);
        
        if(is_array($ids) && count($ids) > 0){
        
            foreach($ids as $id) {
                $retour[] = Mage::getModel('catalog/category')->load($id);
            }
            
        }else{
            
            Mage::getSingleton('adminhtml/session')->addError('Root category is not set');
            $url = Mage::Helper('adminhtml')->getUrl('MarketPlace/Configuration/index', array());
            header('location:' . $url);
            exit();
            
        }
        
        return $retour;
    }

    /**
     * Get max depth for categories to associate from root category
     * 
     * @return int
     */
    public function getMaxDepth() {
        return Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_max_category_depth();
    }

    /**
     * Return association value for one product and one market place depending of product categories
     * 
     * @return string
     */
    public function getCategoryDataForProduct($product, $marketPlace) {
        $productCategories = "";
        $categoryIds = $product->getCategoryIds();

        // try to get parent category
        if (count($categoryIds) == 0) {

            $simpleProductId = $product->getentity_id();
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($simpleProductId);
            if (count($parentIds) > 0) {
                $parentProduct = Mage::getModel('catalog/product')->load($parentIds[0]);
                $parentCategories = $parentProduct->getCategoryIds();
                $productCategories = $parentCategories;
            }
        }

        foreach ($categoryIds as $categoryId) {
            $category = mage::getModel('catalog/category')->load($categoryId);
            $productCategories .= $category->getPath() . ',';
        }

        //parse all association
        $collection = mage::getModel('MarketPlace/Category')
                ->getCollection()
                ->addFieldToFilter('mpc_marketplace_id', $marketPlace)
                ->setOrder('mpc_category_path', 'ASC');

        $value = '';
        foreach ($collection as $item) {

            if (is_array($productCategories))
                $productCategories = implode(',', $productCategories);

            $pos = strpos($productCategories, $item->getmpc_category_path());
            if (!($pos === false)) {
                $value = $item->getmpc_association_data();
                $value = mage::helper('MarketPlace/Serializer')->unserializeObject($value);
            }
        }

        return $value;
    }

    /**
     * Is used
     * 
     * @param string $key
     * @param string $mp
     * @return boolean 
     */
    public function isUsed($key, $mp) {

        $selected = explode(",", Mage::getStoreConfig('marketplace/' . $mp . '/categories'));

        return in_array($key, $selected);
    }

}