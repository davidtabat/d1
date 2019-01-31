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
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */

class MDN_MarketPlace_Model_Category extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Category');
    }

    /**
     * Update association in database
     * 
     * @param string $marketplace
     * @param int $categoryId
     * @param string $value
     * @return boolean true
     */
    public function updateAssociation($marketPlace, $categoryId, $value) {
        //try to load the association
        $item = mage::getModel('MarketPlace/Category')
                ->getCollection()
                ->addFieldToFilter('mpc_marketplace_id', $marketPlace)
                ->addFieldToFilter('mpc_category_id', $categoryId)
                ->getFirstItem();

        //delete old item
        if ($item->getId())
            $item->delete();

        //insert record if value is set
        if ($value != '') {
            $category = mage::getModel('catalog/category')->load($categoryId);

            $newItem = mage::getModel('MarketPlace/Category')
                    ->setmpc_marketplace_id($marketPlace)
                    ->setmpc_category_id($categoryId)
                    ->setmpc_association_data($value)
                    ->setmpc_category_path($category->getPath())
                    ->save();
        }

        return true;
    }

    /**
     * Return association value between category & marketplace
     * 
     * @param int $categoryId
     * @param string $marketplace
     * @return string $value
     */
    public function getAssociationValue($categoryId, $marketPlace) {
        $item = mage::getModel('MarketPlace/Category')
                ->getCollection()
                ->addFieldToFilter('mpc_marketplace_id', $marketPlace)
                ->addFieldToFilter('mpc_category_id', $categoryId)
                ->getFirstItem();

        //unserialize
        $value = $item->getmpc_association_data();
        if ($value != '')
            $value = mage::helper('MarketPlace/Serializer')->unserializeObject($value);

        return $value;
    }

    /**
     * Get Magento category
     * 
     * @param int $mp_cat_id
     * @param string $mp
     * @return mixed null|int 
     */
    public function getMagentoCategory($mp_cat_id, $mp) {

        $id = Mage::helper('MarketPlace/Serializer')->serializeObject($mp_cat_id);

        $item = mage::getModel('MarketPlace/Category')
                ->getCollection()
                ->addFieldToFilter('mpc_association_data', $id)
                ->addFieldToFilter('mpc_marketplace_id', $mp)
                ->getFirstItem();

        return ($item->getmpc_category_id() == "") ? null : $item->getmpc_category_id();
    }

}