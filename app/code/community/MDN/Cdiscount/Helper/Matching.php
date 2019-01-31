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
 */

class MDN_Cdiscount_Helper_Matching extends MDN_MarketPlace_Helper_Matching {

    /**
     * Match products
     *
     * @param array $products
     * <ul>
     * <li>sku</li>
     * <li>ean</li>
     * <li>name</li>
     * <li>price</li>
     * <li>stock</li>
     * </ul>
     */
    public function match($products){

        // get products
        $productsTab = (is_object($products) && $products instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection) ? $this->_buildArrayFromCollection($product) : $products;

        // build package
        $helperOffer = Mage::Helper('Cdiscount/Package_Offers');
        $helperOffer->setMatchingEan(true);
        $res = $helperOffer->buildPackage($productsTab);

        // send package
        $helper = Mage::Helper('Cdiscount/Services');
        $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeMatchingEAN);
        $url = Mage::Helper('Cdiscount/Url')->getOfferPackageUrl($res['id']);
        $helper->submitOfferPackage($url);

    }

    /**
     * Build array from collection (matching EAN from grid)
     *
     * @param collection $products
     * @return array $retour
     */
    protected function _buildArrayFromCollection($products){

        $retour = array();

        foreach($products as $item){

           

        }

        return $retour;

    }

}
