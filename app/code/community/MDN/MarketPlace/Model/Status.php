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
 * @copyright  Copyright (c) 2012 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_Status extends Mage_Core_Model_Abstract {

    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Status');
    }

    /**
     * Update
     * 
     * @param MDN_MarketPlace_Model_Data $mp_data
     * @param string $country
     * @param string $status
     * @param int $delay
     * @return int 0
     */
    public function update($mp_data, $country, $status, $delay = null) {

        $obj = $this->getCollection()
                ->addFieldToFilter('mps_marketplace_id', $mp_data->getmp_marketplace_id())
                ->addFieldToFilter('mps_product_id', $mp_data->getmp_product_id())
                ->addFieldToFilter('mps_country', $country)
                ->getFirstItem();

        if ($obj->getmps_id()) {
            // product update
            $obj->setmps_status($status)
                    ->save();
        } else {
            // product creation !
            $obj = $this->setmps_marketplace_id($mp_data->getmp_marketplace_id())
                    ->setmps_product_id($mp_data->getmp_product_id())
                    ->setmps_country($country)
                    ->setmps_status($status)
                    ->save();

            // add product on countries with same language
            /* $helper = Mage::Helper(ucfirst($mp_data->getmp_marketplace_id().'/Internationalization'));
              $countries = $helper->getCountriesWithSameLanguages($country);
              foreach($countries as $country){
              $status_obj = $this->setmps_marketplace_id($mp_data->getmp_marketplace_id())
              ->setmps_product_id($mp_data->getmp_product_id())
              ->setmps_country($country)
              ->setmps_status($status)
              ->save();
              } */
        }

        if ($delay !== null) {
            $obj->setmps_delay($delay)
                    ->save();
        }

        return 0;
    }

    /**
     * Is created
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $marketplace
     * @param string $country
     * @return boolean 
     */
    public function isCreated($product, $marketplace, $country) {

        $obj = $this->getCollection()
                ->addFieldToFilter('mps_product_id', $product->getentity_id())
                ->addFieldToFilter('mps_marketplace_id', $marketplace)
                ->addFieldToFilter('mps_country', $country)
                ->getFirstItem();

        return ($obj->getmps_id() && $obj->getmps_status() == MDN_MarketPlace_Helper_ProductCreation::kStatusCreated);
    }

}
