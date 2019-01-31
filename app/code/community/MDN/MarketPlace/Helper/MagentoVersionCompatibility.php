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
class MDN_MarketPlace_Helper_MagentoVersionCompatibility extends Mage_Core_Helper_Abstract {

    /**
     * return version
     *
     * @return string
     */
    public function getVersion() {
        $version = mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1];
    }

    /**
     * return version
     *
     * @return string
     */
    public function getVersionMinor() {
        $version = mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1] . '.' . $t[2];
    }

    /**
     * return parents for one product
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return array $parentIds
     */
    public function getProductParentIds($product) {

        $versionMinor = $this->getVersionMinor();
        $parentIds = array();

        $tmp = explode(".", $versionMinor);

        if ($tmp[0] == 1) {

            if ($tmp[1] > 4 || ($tmp[1] == 4 && $tmp[2] >= 2)) {
                // after 1.4.2
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            } else {
                // before 1.4.2
                $parentIds = $product->loadParentProductIds()->getData('parent_product_ids');
            }
        }

        return $parentIds;
    }

}