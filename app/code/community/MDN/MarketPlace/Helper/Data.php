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
class MDN_MarketPlace_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Process actions
     * 
     * <ul>
     * <li>Creation</li>
     * <li>Matching</li>
     * <li>Update stock & price</li>
     * <li>Update image</li>
     * <li>Update status</li>
     * <li>Delete</li>
     * </ul>
     * 
     * @param string $action
     * @param int $productId
     * @param int $countryId
     * @return string $message
     * @throws Exception 
     */
    public function process($action, $productId, $countryId) {

        $message = '';
        
        $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
        Mage::register('mp_country', $country);
        $mp = $country->getAssociatedMarketplace();

        switch ($action) {

            case 'match':
                $helper = Mage::Helper(ucfirst($mp) . '/Matching');

                if (Mage::Helper(ucfirst($mp) . '/ProductCreation')->allowMatchingEan()) {

                    // load product
                    $product = Mage::getModel('Catalog/Product')->setStoreId($country->getParam('store_id'))->load($productId);
                    $barcode = Mage::Helper('MarketPlace/Barcode')->getBarcodeForProduct($product);

                    if (Mage::Helper('MarketPlace/Checkbarcode')->checkCode($barcode) === true) {

                        $mathingArray = $helper->buildMatchingArray(array($product));
                        $helper->Match($mathingArray);

                        $message = Mage::Helper('MarketPlace')->__('Matching submitted');
                    } else {

                        Mage::getModel('MarketPlace/Data')->updateStatus(array($productId), $countryId, MDN_MarketPlace_Helper_ProductCreation::kStatusInError);
                        Mage::getModel('MarketPlace/Data')->addMessage($productId, Mage::Helper('MarketPlace')->__('Invalid EAN code'), $countryId);
                        throw new Exception(Mage::Helper('MarketPlace')->__('Invalid EAN code for product ID : %', $productId));
                    }
                } else {

                    $message = Mage::Helper('MarketPlace')->__(ucfirst($mp) . ' is not allowed to process matching EAN');
                    throw new Exception($message);
                }
                break;
            case 'add':
                $request = new Zend_Controller_Request_Http();
                $request->setParam('product_ids', array($productId));
                Mage::Helper(ucfirst($mp) . '/ProductCreation')->massProductCreation($request);
                $message = Mage::Helper('MarketPlace')->__('Product creation submitted');
                break;
            case 'revise':
                $request = new Zend_Controller_Request_Http();
                $request->setParam('product_ids', array($productId));
                Mage::Helper(ucfirst($mp) . '/ProductCreation')->massReviseProducts($request);
                $message = Mage::Helper('MarketPlace')->__('Product data submitted');
                break;
            case 'update':
                Mage::Helper(ucfirst($mp) . '/ProductUpdate')->update(array($productId));
                $message = Mage::Helper('MarketPlace')->__('Stock and price submitted');
                break;
            case 'updateimage':
                Mage::Helper(ucfirst($mp) . '/ProductUpdate')->updateImageFromGrid(array($productId));
                $message = Mage::Helper('MarketPlace')->__('Picture submitted');
                break;
            case 'delete':
                $helper = Mage::Helper(ucfirst($mp) . '/Delete');
                $helper->process(array($productId));
                $message = Mage::Helper('MarketPlace')->__('Product deleted');
                break;
            case 'setascreated':
                $ids = array($productId);
                $status = 'created';
                Mage::getModel('MarketPlace/Data')->updateStatus($ids, $countryId, $status);
                $message = Mage::Helper('MarketPlace')->__('Product status updated as created');
                break;
            case 'setasnotcreated':
                $ids = array($productId);
                $status = 'notCreated';
                Mage::getModel('MarketPlace/Data')->updateStatus($ids, $countryId, $status);
                $message = Mage::Helper('MarketPlace')->__('Product status updated as not created');
                break;
            case 'setaspending':
                $ids = array($productId);
                $status = 'pending';
                Mage::getModel('MarketPlace/Data')->updateStatus($ids, $countryId, $status);
                $message = Mage::Helper('MarketPlace')->__('Product status updated as pending');
                break;
            default:
                throw new Exception(Mage::Helper('MarketPlace')->__('Unknow action %', $action));
                break;
        }
        
        return $message;
    }

    /**
     * Is ERP installed
     * 
     * @return boolean 
     */
    public function isErpInstalled() {

        return (Mage::getStoreConfig('advancedstock/erp/is_installed') == 1) ? true : false;
    }
    
    /**
     * Use ERP barcode ?
     * 
     * @return boolean
     */
    public function useErpBarcode(){
        
        $retour = false;
        
        if($this->isErpInstalled() && Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_use_erp_barcode() == 1)
            $retour = true;
        
        return $retour;
        
    }

    /**
     * Get Customer group ID
     * 
     * @param string $mp
     * @return int 
     */
    public function getCustomerGroupId($mp = '') {

        return Mage::registry('mp_country')->getParam('customer_group_id');
    }

    /**
     * get available marketplaces from config.xml
     *
     * @return array $helpers
     */
    public function getHelpers() {

        $helpers = array();

        $marketplaces = Mage::getConfig()->getNode('marketplaces');
        foreach ($marketplaces->children() as $helper) {
            $helpers[] = (string) $helper;
        }

        return $helpers;
    }

    /**
     * get available marketplaces names
     *
     * @return array $retour
     */
    public function getMarketplacesName() {

        $retour = array();
        $helpers = $this->getHelpers();
        foreach ($helpers as $helper) {
            $tmp = explode("/", $helper);
            $retour[] = $tmp[0];
        }

        return $retour;
    }

    /**
     * get marketplaces for html select
     *
     * @return array $retour
     */
    public function getMarketPlaceOptions() {

        $retour = array();

        $helpers = $this->getHelpers();

        foreach ($helpers as $helper) {

            $tmp = explode("/", $helper);
            $market = $tmp[0];

            $market = strtolower($market);

            $retour[$market] = $market;
        }

        return $retour;
    }

    /**
     * Get helper according to marketplace name
     *
     * @param string $name
     * @return helper
     */
    public function getHelperByName($name) {
        foreach ($this->getHelpers() as $helper) {
            $helper = mage::helper($helper);
            if ($helper->getMarketPlaceName() == $name)
                return $helper;
        }

        throw new Exception('Unable to load market place helper for ' . $name);
    }

    /**
     * get shipping method title
     *
     * @param string $shipping_method
     * @return string
     */
    public function getShippingMethodTitle($shipping_method) {

        if ($shipping_method == "") {
            throw new Exception('Selected shipment method doesn\'t exists anymore. Please check your system configuration');
        }

        $tab = explode('_', $shipping_method);

        $options = array();
        $title = "";

        // get shipment methods
        $carriers = Mage::getStoreConfig('carriers', 0);

        foreach ($carriers as $carrierKey => $item) {
            if ($carrierKey == $tab[0]) {
                $title = mage::getModel($item['model'])->getConfigData('title');
            }
        }

        return $title;
    }

    /**
     * Check if current order has not be imported yet
     *
     * @param string $marketplaceOrderId
     * @return boolean
     */
    public function orderAlreadyImported($marketplaceOrderId) {
        $collection = mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToFilter('marketplace_order_id', $marketplaceOrderId);

        if ($collection->getSize() > 0)
            return true;
        else
            return false;
    }

    /**
     * Save grid
     *
     * @param request $request
     * @param string $mp
     */
    public function save($request, $mp) {

        $data = $request->getPost('data');
        $null = new Zend_Db_Expr('null');

        $fields = array(
            'mp_force_export' => 0,
            'mp_exclude' => $null,
            'mp_force_qty' => $null,
            'mp_reference' => $null,
            'mp_delay' => $null,
            'mp_free_shipping' => $null
        );

        foreach ($data as $productId => $value) {

            $status = 'notCreated';

            foreach ($fields as $field => $default) {

                if (!array_key_exists($field, $value) || $value[$field] == '')
                    $value[$field] = $default;
            }

            if ($value['mp_reference'] != new Zend_Db_Expr('null'))
                $status = 'created';

            //try to load record
            $obj = mage::getModel('MarketPlace/Data')
                    ->getCollection()
                    ->addFieldToFilter('mp_marketplace_id', $mp)
                    ->addFieldToFilter('mp_product_id', $productId)
                    ->getFirstItem();

            $obj->setmp_marketplace_id($mp)
                    ->setmp_product_id($productId)
                    ->setmp_exclude($value['mp_exclude'])
                    ->setmp_reference($value['mp_reference'])
                    ->setmp_force_qty($value['mp_force_qty'])
                    ->setmp_delay($value['mp_delay'])
                    ->setmp_marketplace_status($status)
                    ->setmp_free_shipping($value['mp_free_shipping'])
                    ->setmp_force_export($value['mp_force_export'])
                    ->setmp_last_update('1900-01-01')
                    ->save();
        }
    }

    /**
     * Rename uploaded file (when orders are manually imported)
     *
     * @param filename $uploadFile
     * @param pathname $path
     * @param string $marketplace
     * @return string $newName
     */
    public function renameUploadedFile($uploadFile, $path, $marketplace) {

        $extension = strrchr($uploadFile, '.');
        $newName = 'import' . ucfirst($marketplace) . '-' . date('Y-m-d_H:i:s') . $extension;
        rename($path . $uploadFile, $path . $newName);

        return $newName;
    }
    
    /**
     * Get current country
     * 
     * @param int $id
     * @return MDN_MarketPlace_Model_Countries
     * @todo : implement it !! 
     */
    public function getCurrentCountry($id){
        
        $country = null;
        
        
        
        return $country;
        
    }
    
    /**
     * Return operator
     */
    /*public function getOperator() {
        $session = Mage::getSingleton('adminhtml/session');
        $operatorId = $session->getData($this->_operatorSessionKey);

        if (!$operatorId) {
            $operatorId = Mage::getSingleton('admin/session')->getUser()->getId();
            $this->setOperator($operatorId);
        }

        return $operatorId;
    }*/

    /**
     * Set operator id
     */
    /*public function setOperator($userId) {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setData($this->_operatorSessionKey, $userId);
    }*/

}