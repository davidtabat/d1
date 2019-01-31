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
class MDN_MarketPlace_Model_Mysql4_Data extends Mage_Core_Model_Mysql4_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        $this->_init('MarketPlace/Data', 'mp_id');
    }
 
    /**
     * get mp product id
     * 
     * @param string $mp_reference
     * @param string $marketplace
     * @return int 
     */
    public function getMpProductId($mp_reference, $marketplace) {

        $resourceModel = mage::getResourceModel('MarketPlace/Data_collection');
        $resourceModel->getSelect()->reset();

        $sql = $resourceModel
                        ->getSelect()
                        ->from(array('market_place_data' => $resourceModel->getTable('MarketPlace/Data')), array('mp_product_id' => 'market_place_data.mp_product_id'))
                        ->where('market_place_data.mp_reference = ? ', $mp_reference)
                        ->where('market_place_data.mp_marketplace_id = ? ', $marketplace);

        $mp_product_id = $resourceModel->getConnection()->fetchOne($sql);

        return $mp_product_id;
    }

    /**
     * Update status
     * 
     * @param int $productId
     * @param string $marketplace
     * @param string $status
     * @return int 
     */
    public function updateStatus($productId, $marketplace, $status) {

        // load record
        $obj = mage::getModel('MarketPlace/Data')
                        ->getCollection()
                        ->addFieldToFilter('mp_marketplace_id', $marketplace)
                        ->addFieldToFilter('mp_product_id', $productId);

        // update if product in marketplace_data
        if ($obj->count() > 0) {

            $obj = $obj->getFirstItem();
            $obj->setmp_marketplace_status($status);
            $obj->save();
        } else {
            // else create a new one
            mage::getModel('MarketPlace/Data')
                    ->setmp_marketplace_id($marketplace)
                    ->setmp_marketplace_status($status)
                    ->setmp_product_id($productId)
                    ->save();
        }

        return 0;
    }

}