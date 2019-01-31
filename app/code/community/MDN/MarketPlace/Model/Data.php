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
class MDN_MarketPlace_Model_Data extends Mage_Core_Model_Abstract {

    const kUpdateStatusOk = 'ok';
    const kUpdateStatusError = 'error';
    const kUpdateStatusWaiting = 'waiting';
    
    /**
     * Get update statuses as combo
     * 
     * @return array $retour 
     */
    public function getUpdateStatusesAsCombo(){
        
        $retour = array(
            self::kUpdateStatusOk => Mage::Helper('MarketPlace')->__('OK'),
            self::kUpdateStatusError => Mage::helper('MarketPlace')->__('ERROR'),
            self::kUpdateStatusWaiting => Mage::Helper('MarketPlace')->__('WAITING')
        );
        
        return $retour;
        
    }
    
    /**
     * Set last update status
     * 
     * @param int $productId
     * @param int $countryId
     * @param string $status 
     */
    public function setLastUpdateStatus($productId, $countryId, $status){
        
        $item = $this->getCollection()
                    ->addFieldToFilter('mp_product_id', $productId)
                    ->addFieldToFilter('mp_marketplace_id', $countryId)
                    ->getFirstItem();
        
        if($item->getId()){
            
            // reset update if error, will submitted again next time
            if($status == self::kUpdateStatusError)
                $item->setmp_last_update("1900-01-01");
            
            $item->setmp_update_status($status)
                    ->save();
            
        }
        
    }
    
    /**
     * Construtor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Data');
    }

    /**
     * Get available products number
     *
     * @param int $marketplaceId
     * @return int
     */
    public function getAvailableProductsNumber($marketplaceId) {

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();

        $select = $read->select()
                        ->from($prefix . 'market_place_data', array('mp_product_id'))
                        ->where('mp_marketplace_id = ?', $marketplaceId)
                        ->where('mp_reference IS NOT NULL');

        $results = $select->query()->fetchAll();

        return count($results);
    }

    /**
     * Is product created
     *
     * @param object $product
     * @param int $marketplaceId
     * @return boolean
     */
    public function isCreated($product, $marketplaceId) {

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();

        $select = $read->select()
                        ->from($prefix . 'market_place_data', array('mp_marketplace_status'))
                        ->where('mp_product_id = ?', $product->getid())
                        ->where('mp_marketplace_id = ?', $marketplaceId);

        $res = $select->query()->fetchAll();

        return (count($res) > 0) && ($res[0]['mp_marketplace_status'] == "created");
    }

    /**
     * Is product pending
     *
     * @param object $product
     * @param int $marketplaceId
     * @return boolean
     */
    public function isPending($product, $marketplaceId) {

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();

        $select = $read->select()
                        ->from($prefix . 'market_place_data', array('mp_marketplace_status'))
                        ->where('mp_product_id = ?', $product->getid())
                        ->where('mp_marketplace_id = ?', $marketplaceId);

        $res = $select->query()->fetchAll();

        return (count($res) > 0) && ($res[0]['mp_marketplace_status'] == "pending");
    }

    /**
     * Is product not created
     *
     * @param object $product
     * @param int $marketplaceId
     * @return boolean
     */
    public function isNotCreated($product, $marketplaceId) {

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();

        $select = $read->select()
                        ->from($prefix . 'market_place_data', array('mp_marketplace_status'))
                        ->where('mp_product_id = ?', $product->getid())
                        ->where('mp_marketplace_id = ?', $marketplaceId);

        $res = $select->query()->fetchAll();

        return (count($res) > 0) && ($res[0]['mp_marketplace_status'] == NULL);
    }

    /**
     * Update status
     *
     * @param mixed array | int $ids
     * @param int $marketplaceId
     * @param string $status
     * @param string $mp_reference
     */
    public function updateStatus($ids, $marketplaceId, $status, $mp_reference = null, $message = '') {

        if (!is_array($ids))
            $ids = array($ids);
        
        foreach ($ids as $id) {

            if($id == '' || $id === null)
                continue;

            $status = ($status == Mage::helper('MarketPlace/ProductCreation')->getStatusNotCreated()) ? new Zend_Db_Expr('null') : $status;

            $p = mage::getResourceModel('MarketPlace/Data_collection')
                            ->addFieldToFilter('mp_product_id', $id)
                            ->addFieldToFilter('mp_marketplace_id', $marketplaceId)
                            ->getFirstItem();
            
            if ($p->getmp_id()) {

                $p->setmp_marketplace_status($status)
                    ->setmp_reference($mp_reference);

            } else {

                $p = mage::getModel('MarketPlace/Data');
                $p->setmp_product_id($id)
                        ->setmp_marketplace_status($status)
                        ->setmp_marketplace_id($marketplaceId)
                        ->setmp_exclude(0)
                        ->setmp_reference($mp_reference);

            }

            if($message != '')
                $p->setmp_message($message);   
            
            // reset error message when product is successfully created
            if($status == MDN_MarketPlace_Helper_ProductCreation::kStatusCreated)
                $p->setmp_message('');
                                    
            $p->save();    

        }
    }
    
    /**
     * Before save
     * 
     * @return type 
     */
    protected function _beforeSave(){        
        
        return parent::_beforeSave();
        
    }

    /**
     * Is free shipping enable for product
     *
     * @param object $product
     * @param string $mp
     * @return boolean
     */
    public function hasFreeShipping($product, $mp) {

        $obj = $this->getCollection()
                ->addFieldToFilter('mp_marketplace_id', $mp)
                ->addFieldToFilter('mp_product_id', $product->getid())
                ->getFirstItem();

        return ($obj->getmp_free_shipping() == 1);
    }

    /**
     * Get available products
     *
     * @param int $marketplaceId
     * @return collection
     */
    public function getAvailableProducts($marketplaceId) {

        $products = $this->getCollection()
                ->addFieldToFilter('mp_marketplace_id', $marketplaceId)
                ->addFieldToFilter('mp_marketplace_status', MDN_MarketPlace_Helper_ProductCreation::kStatusCreated);

        return $products;
    }

    /**
     * Force export
     *
     * @param int $id
     * @param int $mp
     * @return <type>
     */
    public function forceExport($id, $mp){

       $obj = $this->getCollection()
               ->addFieldToFilter('mp_marketplace_id', $mp)
               ->addFieldToFilter('mp_product_id', $id)
               ->getFirstItem();

       if($obj->getmp_id()){

           $obj->setmp_force_export(1)
                   ->save();

       }else{

           $obj = $this->setmp_product_id($id)
                       ->setmp_marketplace_id($mp)
                       ->setmp_force_export(1)
                       ->save();

       }

       return 0;

    }

    /**
     * Add message
     *
     * @param int $product_id
     * @param string $message
     * @param string $mp
     * @return int 0
     */
    public function addMessage($product_id, $message, $mp, $error = false) {

        $item = Mage::getModel('MarketPlace/Data')->getCollection()
                ->addFieldToFilter('mp_product_id', $product_id)
                ->addFieldToFilter('mp_marketplace_id', $mp)
                ->getFirstItem();

        if ($item->getmp_id()) {

            $item->setmp_message($message);

            if ($error === true)
                $item->setmp_marketplace_status(MDN_MarketPlace_Helper_ProductCreation::kStatusInError);


            $item->save();
        }else {

            $newEntry = Mage::getModel('MarketPlace/Data')
                    ->setmp_product_id($product_id)
                    ->setmp_marketplace_id($mp)
                    ->setmp_message($message);

            if ($error === true)
                $newEntry->setmp_marketplace_status(MDN_MarketPlace_Helper_ProductCreation::kStatusInError);

            $newEntry->save();
        }

        return 0;
    }
    
    /**
     * Delete one product (set it as not created and reset fields
     * 
     * @param int $id
     * @return int 0 
     */
    public function deleteProduct($id){
        
        $item = $this->getCollection()
                    ->addFieldToFilter('mp_product_id', $id)
                    ->addFieldToFilter('mp_marketplace_id', Mage::registry('mp_country')->getId())
                    ->getFirstItem();
        
        if($item->getId()){
            
            $null = new Zend_Db_Expr('null');
            
            $item->setmp_reference('')
                    ->setmp_marketplace_status(MDN_MarketPlace_Helper_ProductCreation::kStatusNotCreated)
                    ->setmp_message('')
                    ->setmp_last_update('1900-01-01')
                    ->setmp_last_delay_sent($null)
                    ->setmp_last_stock_sent($null)
                    ->setmp_last_price_sent($null)
                    ->save();
            
        }
        
        return 0;
        
    }

}