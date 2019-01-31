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
class MDN_MarketPlace_Helper_Product extends Mage_Core_Helper_Abstract {

    /**
     * Get product stock availibility
     *
     * @param product $product
     * @return integer
     */
    public function getStockToExport($product, $mp = null) {               
        
        $stock = 0;
        $cost = $product->getcost();
        
        if($product->getmp_custom_stock()){
            // get custom stock attribute value
            $stock = $product->getmp_custom_stock();

        }else{
  
            if($product->getstatus() == 2){
                $stock = 0;
            }
            else{
                $p = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getentity_id());

                if(Mage::Helper('MarketPlace')->isErpInstalled()){
                    
                    $pas = Mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($product->getentity_id(), 'pa_product_id');
                    
                    if ($pas->getId())
                        $stock = $pas->getpa_available_qty();
                    else			
                        $stock = $p->getQty();
                                        
                }else{
                    $stock = $p->getQty();
                }
                
                // check force quantity
                if ($product->getmp_force_qty() != '') {
                    $stock = $product->getmp_force_qty();
                }
                if ($product->getmp_exclude() == 1 || $p->getis_in_stock() == 0) {
                    $stock = 0;
                }

            }             
        }

        // margin filter
        $margin = Mage::registry('mp_country')->getParam('margin_min');

        if($margin != ''){

            $price = str_replace(",",".",$this->getPriceToExport($product));
            $cost = str_replace(",",".",$cost);

            $product_margin = round(($price - $cost) / $price * 100, 2);

            if($product_margin < $margin && $product->getmp_force_export() == 0){

                $stock = 0;

            }
        }
        
        $product->setData('stock_to_export',$stock);
        Mage::dispatchEvent('marketplace_before_export_stock', array('product' => $product));
		
        return ($product->getData('stock_to_export') > 0) ? (int)$product->getData('stock_to_export') : 0;
    }

    /**
     * get delay to export
     *
     * @param product $product
     * @return int
     */
    public function getDelayToExport($product) {
        $mp_delay = $product->getmp_delay();
        return ($mp_delay !== NULL && $mp_delay >= 0) ? $mp_delay : Mage::registry('mp_country')->getParam('default_shipment_delay');
    }

    /**
     * Get product price (including tax)
     *
     * @param product $product
     * @return float
     *
     * @see getPrice()
     */
    public function getPriceToExport($product) {
        
        $price = $this->getPriceBeforeEvent($product);
		
        // dispatch event for updating price
        $product->setData('price_to_export', $price);
        Mage::dispatchEvent('marketplace_before_export_price', array('product'=>$product));
        $price = $product->getData('price_to_export');
		
        $price = round($price, 2);
		
        $price = str_replace(".", ",", $price);
        
        return $price;
    }
    
    /**
     * Get price before event
     * 
     * @param Varien_Object $product
     * @return float $price 
     */
    public function getPriceBeforeEvent($product){

        $taxRate = Mage::Helper('MarketPlace/Taxes')->getTaxRate();  
        $coef = str_replace(',','.',Mage::registry('mp_country')->getParam('price_coef'));

        if($product->getmp_custom_price()){
            // get custom attribute value
            $price = $product->getmp_custom_price();
            // check coef
            // apply price coeff            
            if($coef){
                $price *= $coef;
            }
            // check if it include tax
            if(Mage::registry('mp_country')->getParam('is_price_attribute_including_tax') == 0){
                // add taxes
                $price = str_replace(",", ".", $price);
                $price = round($price, 2);
                $taxCoef = 1 + $taxRate / 100;
                $price = $price * $taxCoef; 
            }
            
        }else{
        
            $price = $this->getPrice($product);
            // check coef
            if($coef){
                $price *= $coef;
            }
            //compute price with taxes
            if (!mage::getStoreConfig('tax/calculation/price_includes_tax')) {
                $price = str_replace(",", ".", $price);
                $price = round($price, 2);
                $taxCoef = 1 + $taxRate / 100;
                $price = $price * $taxCoef;            
            }
        }                
        
        $price = str_replace(",", ".", $price);

        return $price;
        
    }

    /**
     * Get price
     *
     * @param product $product
     * @return float
     */
    public function getPrice($product) {

        $price = $product->getprice();

        $store = Mage::getModel('core/store')->load(Mage::registry('mp_country')->getParam('store_id'));

        if($product->getspecial_price()){

            $price = (Mage::app()->getLocale()->isStoreDateInInterval($store, $product->getspecial_from_date(), $product->getspecial_to_date())) ? $product->getspecial_price() : $price;

        }

        return $price;
    }

    /**
     * Build sql query
     * 
     * @param int $marketplaceId
     * @return string $sql 
     * @todo : au niveau des prix (price, special, special_from, special_to, il faut verifier si on doit utiliser les prix par storeview ou de facon globale.
     *  -  
     */
    protected function _getSql($marketplaceId){
        
        $sql = '';        
        $prefix = Mage::getConfig()->getTablePrefix();
        $entityTypeId = Mage::getModel('eav/config')->getEntityType('catalog_product')->getId();
        $storeId = Mage::registry('mp_country')->getParam('store_id');
        
        // get attributes id
        $priceAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'price')->getId();
        $specialPriceAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'special_price')->getId();
        $specialFromDateAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'special_from_date')->getId();
        $specialToDateAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'special_to_date')->getId();
        $weightAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'weight')->getId();
        $nameAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'name')->getId();
        $imageAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'image')->getId();
        $smallImageAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'small_image')->getId();
        //$freeShippingAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'free_shipping')->getId();
        $statusAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'status')->getId();
        $costAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'cost')->getId();    
        
        // global configuration items
        $marketplace = Mage::registry('mp_country')->getAssociatedMarketplace();
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration($marketplace);       
        
        // barcode
        $generalConfig = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject();
        $barcodeAttrId = null;
        $barcodeAttrName = $generalConfig->getmp_barcode_attribute();
        //echo 'barcode : '.$barcodeAttrName;die();
        if($barcodeAttrName != ''){
            $barcodeAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', $barcodeAttrName)->getId();        
        }
        
        // price & stock attributes
        $customPriceAttrId = null;
        $customPriceAttrTable = null;
        $customPriceAttrName = Mage::registry('mp_country')->getParam('price_attribute');
        $customStockAttrId = null;
        $customStockAttrName = $config->getstockAttribute();

        // try to retrieve custom price attribute id
        if($customPriceAttrName)
        {
            $customPriceAttr = Mage::getModel('eav/config')->getAttribute('catalog_product', $customPriceAttrName);
            switch($customPriceAttr->getbackend_type())
            {
                case 'decimal':
                    $customPriceAttrTable = 'catalog_product_entity_decimal';
                    break;
                default:
                    $customPriceAttrTable = 'catalog_product_entity_varchar';
                    break;
            }
            $customPriceAttrId = $customPriceAttr->getId();
        }
        // try to retrieve custom stock attribute id
        if($customStockAttrName)
            $customStockAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', $customStockAttrName)->getId();

        // build query
        $sql = 'SELECT';
        // try to add custom price value
        if($customPriceAttrId !== null)
            $sql .= ' IFNULL(cp.value, cpd.value) as mp_custom_price,';
        // try to add custom stock value
        if($customStockAttrId !== null)
            $sql .= ' IFNULL(cs.value, csd.value) as mp_custom_stock,';        
        // try to add barcode attribute
        if($barcodeAttrId !== null)
            $sql .= ' ba.value as '.$barcodeAttrName.',';    
        
        $sql .= '   IFNULL(pr.value, dpr.value) AS price,
                    p.entity_id AS Id,
                    p.entity_id AS entity_id,
                    p.entity_id AS product_id,
                    p.attribute_set_id AS attribute_set_id,
                    IFNULL(spr.value, dspr.value) AS special_price,
                    sprf.value AS special_from_date,
                    sprt.value AS special_to_date,
                    w.value AS weight,
                    IFNULL(n.value, nd.value) AS name,
                    i.value AS image,
                    si.value AS small_image,
                    IFNULL(s.value, sd.value) AS status,
                    c.value AS cost,
                    p.sku AS sku,
                    mp.mp_marketplace_id AS mp_marketplace_id,
                    mp.mp_product_id AS mp_product_id,
                    mp.mp_reference AS mp_reference,
                    mp.mp_marketplace_status AS mp_marketplace_status,
                    mp.mp_exclude AS mp_exclude,
                    mp.mp_force_qty AS mp_force_qty,
                    mp.mp_delay AS mp_delay,
                    mp.mp_free_shipping AS mp_free_shipping,
                    mp.mp_force_export AS mp_force_export,
                    mp.mp_last_update AS mp_last_update,
                    mp.mp_message AS mp_message,
                    mp.mp_id AS mp_id                        
                FROM '.$prefix.'catalog_product_entity AS p
                LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS pr ON (pr.attribute_id = '.$priceAttrId.') AND (pr.entity_type_id = '.$entityTypeId.') AND (pr.entity_id = p.entity_id) AND (pr.store_id = '.$storeId.')
                LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS spr ON (spr.attribute_id = '.$specialPriceAttrId.') AND (spr.entity_type_id = '.$entityTypeId.') AND (spr.entity_id = p.entity_id) AND (spr.store_id = '.$storeId.')
                LEFT JOIN '.$prefix.'catalog_product_entity_datetime AS sprf ON (sprf.attribute_id = '.$specialFromDateAttrId.') AND (sprf.entity_type_id = '.$entityTypeId.') AND (sprf.entity_id = p.entity_id) AND (sprf.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_datetime AS sprt ON (sprt.attribute_id = '.$specialToDateAttrId.') AND (sprt.entity_type_id = '.$entityTypeId.') AND (sprt.entity_id = p.entity_id) AND (sprt.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS dpr ON (dpr.attribute_id = '.$priceAttrId.') AND (dpr.entity_type_id = '.$entityTypeId.') AND (dpr.entity_id = p.entity_id) AND (dpr.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS dspr ON (dspr.attribute_id = '.$specialPriceAttrId.') AND (dspr.entity_type_id = '.$entityTypeId.') AND (dspr.entity_id = p.entity_id) AND (dspr.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS w ON (w.attribute_id = '.$weightAttrId.') AND (w.entity_type_id = '.$entityTypeId.') AND (w.entity_id = p.entity_id) AND (w.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS n ON (n.attribute_id = '.$nameAttrId.') AND (n.entity_type_id = '.$entityTypeId.') AND (n.entity_id = p.entity_id) AND (n.store_id = '.$storeId.')
                LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS nd ON (nd.attribute_id = '.$nameAttrId.') AND (nd.entity_type_id = '.$entityTypeId.') AND (nd.entity_id = p.entity_id) AND (nd.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS i ON (i.attribute_id = '.$imageAttrId.') AND (i.entity_type_id = '.$entityTypeId.') AND (i.entity_id = p.entity_id) AND (i.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS si ON (si.attribute_id = '.$smallImageAttrId.') AND (si.entity_type_id = '.$entityTypeId.') AND (si.entity_id = p.entity_id) AND (si.store_id = 0)                
                LEFT JOIN '.$prefix.'catalog_product_entity_int AS s ON (s.attribute_id = '.$statusAttrId.') AND (s.entity_type_id = '.$entityTypeId.') AND (s.entity_id = p.entity_id) AND (s.store_id = '.$storeId.')
                LEFT JOIN '.$prefix.'catalog_product_entity_int AS sd ON (sd.attribute_id = '.$statusAttrId.') AND (sd.entity_type_id = '.$entityTypeId.') AND (sd.entity_id = p.entity_id) AND (sd.store_id = 0)
                LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS c ON (c.attribute_id = '.$costAttrId.') AND (c.entity_type_id = '.$entityTypeId.') AND (c.entity_id = p.entity_id) AND (c.store_id = 0)';                
        
        // add barcode attribute
        if($barcodeAttrId !== null){
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS ba ON (ba.attribute_id = '.$barcodeAttrId.') AND (ba.entity_type_id = '.$entityTypeId.') AND (ba.entity_id = p.entity_id) AND (ba.store_id = 0)';                
        }
        
        // try to add custom price attribute
        if($customPriceAttrId !== null){
            $sql .= ' LEFT JOIN '.$prefix.$customPriceAttrTable.' AS cp ON (cp.attribute_id = '.$customPriceAttrId.') AND (cp.entity_type_id = '.$entityTypeId.') AND (cp.entity_id = p.entity_id) AND (cp.store_id = '.$storeId.')';
            $sql .= ' LEFT JOIN '.$prefix.$customPriceAttrTable.' AS cpd ON (cpd.attribute_id = '.$customPriceAttrId.') AND (cpd.entity_type_id = '.$entityTypeId.') AND (cpd.entity_id = p.entity_id) AND (cpd.store_id = 0)';
        }
        // try to add custom stock attribute
        if($customStockAttrId !== null){
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS cs ON (cs.attribute_id = '.$customStockAttrId.') AND (cs.entity_type_id = '.$entityTypeId.') AND (cs.entity_id = p.entity_id) AND (cs.store_id = '.$storeId.')';
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_varchar AS csd ON (csd.attribute_id = '.$customStockAttrId.') AND (csd.entity_type_id = '.$entityTypeId.') AND (csd.entity_id = p.entity_id) AND (csd.store_id = 0)';
        }        
        
       $sql .= ' INNER JOIN '.$prefix.'market_place_data AS mp ON (mp.mp_product_id = p.entity_id)
                WHERE (p.type_id IN ("simple", "virtual", "downloadable"))
                AND (mp.mp_marketplace_id = "'.$marketplaceId.'")                                
                AND (
                        (   
                            mp.mp_marketplace_status IN ("'.MDN_MarketPlace_Helper_ProductCreation::kStatusCreated.'", "' . MDN_MarketPlace_Helper_ProductCreation::kStatusPending . '")
                            AND (mp.mp_reference IS NOT NULL)
                            AND (mp.mp_reference != "")    
                        )OR(
                            mp_marketplace_status = "'.MDN_MarketPlace_Helper_ProductCreation::kStatusIncomplete.'"
                        )
               )';

        return $sql;
        
    }
    
    /**
     * Create collection from query
     *  
     * @param array $res
     * @return Varien_Data_Collection $collection
     */
    protected function _createCollection($res){        
        
        $collection = new Varien_Data_Collection();

        $size = count($res);
        for($i = 0; $i < $size; $i++){
            $item = new Varien_Object();
            foreach($res[$i] as $k => $v)
                $item->setData($k, $v);   
            
            $collection->addItem($item);
        }   
        
        return $collection;
        
    }
    
    /**
     * get products from request
     * 
     * @param string $marketplace
     * @param mixed $request
     * @return Varien_Data_Collection 
     */
    public function getProductsFromRequest($marketplace, $request){
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $ids = (is_array($request)) ? $request : $request->getPost('product_ids');
        
        $sql = $this->_getSql($marketplace);
        $sql .= ' AND (mp.mp_product_id IN (' . implode(",", $ids) . '))'; 
        
        $res = $read->fetchAll($sql);
        
        return $this->_createCollection($res);
                
    }
    
    /**
     * Get product collection to export
     *
     * @param int $marketplaceId
     * @return Varien_Data_Collection
     */
    public function getProductsToExport($marketplaceId) {
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = $this->_getSql($marketplaceId);
        
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration(Mage::registry('mp_country')->getAssociatedMarketplace());        
        
        if(!$config->getmax_to_export()){
            $mp = Mage::registry('mp_country')->getAssociatedMarketplace();
            throw new Exception($this->__('Please set max product to export limit in <a href="'.Mage::Helper('adminhtml')->getUrl('MarketPlace/Configuration/index', array('mp' => $mp, 'type' => 'main')).'">'.$mp.'</a> configuration'));
        }
        
        $sql .= ' AND (mp.mp_last_update < p.updated_at)';
        $sql .= ' ORDER BY p.updated_at DESC
                LIMIT 0,'.$config->getmax_to_export();
        
        $res = $read->fetchAll($sql);
        
        // create collection
        return $this->_createCollection($res);                                            
        
    }

    /**
     * Is product has special price
     * 
     * @param object $product
     * @return boolean 
     */
    public function hasSpecialPrice($product) {

        $hasSpecialPrice = false;

        if ($product->getspecial_price() != '') {

            $hasSpecialPrice = true;

            $fromdate = $product->getspecial_from_date();

            if ($fromdate != '')
                if (strtotime($fromdate) > time())
                    $hasSpecialPrice = false;

            $todate = $product->getspecial_to_date();

            if ($todate != '')
                if (strtotime($todate) < time())
                    $hasSpecialPrice = false;
        }

        return $hasSpecialPrice;
    }

    /**
     * Format string
     * 
     * @param string $txt
     * @return string 
     */
    public function formatExportedTxt($txt){
        $txt = strip_tags($txt);
        $txt = preg_replace('/"/',"'",$txt);
        return $txt;
    }

    /**
     * Get product from barcode
     * 
     * @param int $ean
     * @return object
     */
    public function getProductFromBarcode($ean){

        $p = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_barcode_attribute())
                    ->addFieldToFilter(Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_barcode_attribute(), $ean);
                    
        return ($p->count() > 0) ? $p->getFirstItem() : null;

    }
    
    /**
     * Get product description
     * 
     * @param object $product 
     * @return string $retour
     */
    public function getDescription($product){
        
        $retour = '';
        
        $data = array(
            'text_description' => array(
                'heading' => 'additional_textheading',
                'content' => 'text_description'
            ),
            'product_advantages' => array(
                'heading' => 'advantages_heading',
                'content' => 'product_advantages'
            ),
            'product_accessories' => array(
                'heading' => 'accessories_heading',
                'content' => 'product_accessories'
            ),
            'product_characteristics' => array(
                'heading' => 'characteristics_heading',
                'content' => 'product_characteristics'
            ),
            'product_standards' => array(
                'heading' => 'productstandards_heading',
                'content' => 'product_standards'
            ),
            'adjustleft_text' => array(
                'heading' => 'adjustleft_heading',
                'content' => 'adjustleft_text'
            ),
            'adjustleft_text2' => array(
                'heading' => 'adjustleft_heading2',
                'content' => 'adjustleft_text2'
            ),
            'adjustright_text' => array(
                'heading' => 'adjustright_heading',
                'content' => 'adjustright_text'
            )
        );
            
        if($product->getdescription() != '.'){
            $retour .= ($product->getdescription_heading() != "") ? '<h2>'.$product->getdescription_heading().'</h2>' : "";
            $retour .= $product->getdescription();
        }else{
            $retour .= ($product->getshortdescription_heading() != '') ? '<h2>'.$product->getshortdescription_heading().'</h2>' : "";
            $retour .= $product->getshort_description();
        }

        foreach($data as $k => $array){
            
            $getHeading = 'get'.$array['heading'];
            $getContent = 'get'.$array['content'];
            $retour .= ($product->$getHeading() != '') ? '<h2>'.$product->$getHeading().'</h2>' : "";
            $retour .= ($product->$getContent() != '') ? $product->$getContent() : "";
            
        }

        return $retour;
        
    }

}
