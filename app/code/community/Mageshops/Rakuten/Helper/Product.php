<?php
/**
 * @category        Mageshops
 * @package         Mageshops_Rakuten
 * @license         http://license.mageshops.com/  Unlimited Commercial License
 * @copyright       mageSHOPS.com
 * @author          mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Helper_Product extends Mageshops_Market_Helper_Data
{
    /**
     * Gets simple products that need to be synchronized
     * 
     * @param int $limit
     * @param int $offset
     * @return Model 
     */
    public function getSimpleProductsToSynchronize($limit = false, $offset = false, $size = true)
    {
        /** @var Mageshops_Rakuten_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('rakuten');
        
        // If sync from categories enabled in config, 
        if ($dataHelper->productsFromCategories()) {
            $categoryIds = $this->getCategoriesForSynchronization();
            
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->distinct(true)
                ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
                ->addAttributeToFilter('category_id', array('in' => $categoryIds))
                ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                ->addAttributeToFilter('type_id', array('eq' => 'simple'));
            $collection->getSelect()->joinLeft(array('link_table' => Mage::getConfig()->getTablePrefix() . 'catalog_product_super_link'), 'link_table.product_id = e.entity_id', array('product_id'));
            $collection->getSelect()->where('link_table.product_id IS NULL');  
            if ($size) {
                $collection->getSelect()->group('e.entity_id');  
            }
        } else {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                ->addAttributeToFilter('rakuten_sync', array('eq' => 1));
            $collection->getSelect()->joinLeft(array('link_table' => Mage::getConfig()->getTablePrefix() . 'catalog_product_super_link'), 'link_table.product_id = e.entity_id', array('product_id'));
            $collection->getSelect()->where('link_table.product_id IS NULL');
        }
        
        if ($limit && $offset !== false) {
            $collection->getSelect()->limit($limit, $offset);
        }
        
        return $collection;
    }
    
    /**
     * Gets variant products (configurable, bundle, grouped) that need to be synchronized
     * 
     * @param int $limit
     * @param int $offset
     * @return Model 
     */
    public function getVariantProductsToSynchronize($limit = false, $offset = false, $size = true)
    {
        $variantHelper = Mage::helper('rakuten/variant');
        $dataHelper = Mage::helper('rakuten');

        // If sync from categories enabled in config, 
        if ($dataHelper->productsFromCategories()) {
            $categoryIds = $this->getCategoriesForSynchronization();

            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->distinct(true)
                ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
                ->addAttributeToFilter('category_id', array('in' => $categoryIds))
                ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                ->addAttributeToFilter('type_id', $variantHelper->getAllowedProductTypes(false))
                ->addOrder('entity_id', Varien_Data_Collection_Db::SORT_ORDER_ASC);
            if($size) {
                $collection->getSelect()->group('e.entity_id');  
            }
        } else {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                ->addAttributeToFilter('type_id', $variantHelper->getAllowedProductTypes(false))
                ->addOrder('entity_id', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        }
        
        if($limit && $offset) {
            $collection->getSelect()->limit($limit, $offset);
        }

        return $collection;
    }

    /**
     * Returns count of all products that need to be synchronized
     * 
     * @return int
     */
    public function getProductsCountToSynchronize()
    {
        return $this->getSimpleProductsToSynchronize(false, false, false)->getSize()
            + $this->getVariantProductsToSynchronize(false, false, false)->getSize();
    }
    
    /**
     * Gets categories that need to be synchronized
     * 
     * @return array Array of categories ids
     */
    private function getCategoriesForSynchronization()
    {
        $categoryIds = array();

        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();

        foreach ($collection as $category) {
            $categoryIds[] = $category->getId();
        }

        return array_unique($categoryIds);
    }

}
