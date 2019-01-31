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

class MDN_MarketPlace_Helper_ConfigurablesProducts extends Mage_Core_Helper_Abstract {

    /**
     * Get product parent id
     *
     * @param <type> $product
     * @return array
     */
    public function getProductParentId($product){
        return Mage::helper('MarketPlace/MagentoVersionCompatibility')->getProductParentIds($product);
    }

    /***
     * Get attributes
     *
     * @param int $parentId
     * @return array $attributes
     */
    public function getAttributes($parentId){

        $parent = Mage::getModel('catalog/product')->load($parentId);

        $attributes = $parent->getTypeInstance(true)->getConfigurableAttributes($parent);

        return $attributes;

    }

}
