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

class MDN_Cdiscount_Helper_ProductCreation extends MDN_MarketPlace_Helper_ProductCreation {

    const kIntegrated = 'Integrated';

    /**
     * Mass product creation
     *
     * @param request $request
     */
    public function massProductCreation($request) {

        $country = Mage::registry('mp_country');

        $products = Mage::getModel('catalog/product')->getCollection()
                                                      ->setStoreId($country->getParam('store_id'))
                                                      ->addAttributeToSelect('*')
                                                      ->addFieldToFilter('entity_id', array('in' => $request->getPost('product_ids')));

        $res = Mage::Helper('Cdiscount/Package_Products')->buildPackage($products);
        $helper = Mage::Helper('Cdiscount/Services');
        $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeProductCreation);
        $url = Mage::getUrl('Cdiscount/Package/download', array('type' => 'products', 'filename' => $res['id']));
        $helper->submitProductPackage($url);

        // update status
        Mage::getModel('MarketPlace/Data')->updateStatus($res['submitted'], strtolower(Mage::helper('Cdiscount')->getMarketPlaceName()), self::kStatusPending);
    }



    /**
     * Import created products
     *
     * @return string
     */
    public function importCreatedProducts() {

        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        $integrated = array();
        $failed = array();
        
        if (!$account->getParam('seller_product_reference')) {
            throw new Exception('Please select seller product id in account configuration');
        }

        $collection = Mage::getModel('MarketPlace/Feed')->getCollection()
                ->addFieldToFilter('mp_marketplace_id ', Mage::Helper('Cdiscount')->getMarketPlaceName())
                ->addFieldToFilter('mp_type', array('in', array(MDN_Cdiscount_Helper_Feed::kFeedTypeProductCreation, MDN_Cdiscount_Helper_Feed::kFeedTypeMatchingEAN)))
                ->addFieldToFilter('mp_status', array('in', array(MDN_Cdiscount_Helper_Feed::kFeedSubmitted, MDN_Cdiscount_Helper_Feed::kFeedInProgress)));

        foreach ($collection as $feed) {

            if ($feed->getmp_content() == "" || $feed->getmp_feed_id() == "-")
                continue;

            $integrated = array();
            $failed = array();
            // get package submission result
            // retrieve package id
            $xml = new DomDocument();
            $xml->loadXML($feed->getContent());

            //get result
            $nodesPrefix = 'Offer';
            $sellerReferenceNodeName = 'SellerProductId';
            switch($feed->getmp_type())
            {
                case MDN_Cdiscount_Helper_Feed::kFeedTypeProductCreation:
                    $res = Mage::Helper('Cdiscount/Services')->getProductPackageSubmissionResult($feed->getmp_feed_id());
                    $nodesPrefix = 'Product';
                    $sellerReferenceNodeName = 'SKU';
                    break;
                default:
                    $res = Mage::Helper('Cdiscount/Services')->getOfferPackageSubmissionResult($feed->getmp_feed_id());
                    break;
            }
            Mage::helper('Cdiscount')->magentoLog('Check product creation for feed #'.$feed->getmp_feed_id());

            $xml->loadXML($res['content']);

            $packageIntegrationStatus = $xml->getElementsByTagName('PackageIntegrationStatus')->item(0)->nodeValue;
            Mage::helper('Cdiscount')->magentoLog('feed #'.$feed->getmp_feed_id().' integration status is '.$packageIntegrationStatus);
            if ($packageIntegrationStatus != 'Integrated' && $packageIntegrationStatus != 'Rejected') {

                $feed->setmp_status(MDN_Cdiscount_Helper_Feed::kFeedInProgress)->save();
            } else {

                foreach ($xml->getElementsByTagName($nodesPrefix.'ReportLog') as $log) {

                    $id = '';
                    switch ($account->getParam('seller_product_reference')) {
                        case 'sku':
                            $sku = $log->getElementsByTagName($sellerReferenceNodeName)->item(0)->nodeValue;
                            $id = Mage::getModel('Catalog/product')->getIdBySku($sku);
                            break;
                        case 'id':
                        default :
                            $id = $log->getElementsByTagName($sellerReferenceNodeName)->item(0)->nodeValue;
                            break;
                    }

                    Mage::helper('Cdiscount')->magentoLog('feed #'.$feed->getmp_feed_id().' : check result for product #'.$id);

                    $logMessage = $log->getElementsByTagName('LogMessage')->item(0)->nodeValue;

                    if ($log->getElementsByTagName($nodesPrefix.'IntegrationStatus')->item(0)->nodeValue == self::kIntegrated) {
                        $integrated[] = $id;
                        $tmp = explode('|', $logMessage);
                        $mp_reference = $tmp[2];
                        Mage::getModel('MarketPlace/Data')->updateStatus($id, Mage::registry('mp_country')->getId(), self::kStatusCreated, $tmp[2]);
                    } else {
                        $failed[] = $id;
                        Mage::getModel('MarketPlace/Data')->updateStatus($id, Mage::registry('mp_country')->getId(), self::kStatusInError);
                        Mage::getModel('MarketPlace/Data')->addMessage($id, $logMessage, Mage::registry('mp_country')->getId());
                    }
                }

                // update feed status
                if (count($failed) > 0)
                    $feed->setmp_status(MDN_Cdiscount_Helper_Feed::kFeedError)
                            ->save();
                else
                    $feed->setmp_status(MDN_Cdiscount_Helper_Feed::kFeedDone)
                            ->save();
            }
        }

        return Mage::Helper('Cdiscount')->__('%s products added, %s products failed', count($integrated), count($failed));
    }

    /**
     * Get catalog as csv (create products manually....)
     *
     * @return string $retour
     */
    public function getCatalogAsCsv() {

        $retour = '';

        $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addAttributeToSelect('reserved_qty')
                ->addAttributeToSelect('weight')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('updated_at')
                ->addAttributeToSelect('free_shipping')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('cost')
                ->addAttributeToSelect('manufacturer')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('short_description')
                ->addAttributeToFilter('type_id', 'simple')
                ->addFieldToFilter('attribute_set_id', array('nin' => array(174)))
                ->joinTable(
                'MarketPlace/Data', 'mp_product_id=entity_id', array(
            'mp_id' => 'mp_id',
            'mp_reference' => 'mp_reference',
            'mp_exclude' => 'mp_exclude',
            'mp_force_qty' => 'mp_force_qty',
            'mp_delay' => 'mp_delay',
            'mp_free_shipping' => 'mp_free_shipping',
            'mp_marketplace_status' => 'mp_marketplace_status',
            'mp_force_export' => 'mp_force_export',
                //'mp_last_update' => 'mp_last_update'
                ), "mp_marketplace_id='cdiscount'
                                and mp_reference <> ''
                                and mp_marketplace_status = '" . MDN_MarketPlace_Helper_ProductCreation::kStatusCreated . "'", 'inner'
        );

        $collection->getSelect()->limit(5000);

        foreach ($collection as $item) {

            if ($item->getimage() != "" && !preg_match('#no_selection#i', $item->getimage())) {
                $url = Mage::getBaseUrl('media') . 'catalog/product' . $item->getimage();
            } elseif ($item->getsmall_image() != "" && !preg_match('#no_selection#i', $item->getsmall_image())) {
                $url = Mage::getBaseUrl('media') . 'catalog/product' . $item->getsmall_image();
            } else {
                $url = '';
            }

            $categoryIds = $item->getCategoryIds();

            if (count($categoryIds) == 0)
                continue;

            $mainCat = Mage::getModel('catalog/category')->load($categoryIds[count($categoryIds) - 1]);

            $retour .= '"' . $item->getsku() . '";';
            $retour .= '"' . Mage::helper('MarketPlace/Barcode')->getBarcodeForProduct($item) . '";';
            $retour .= '"' . $item->getAttributeText('manufacturer') . '";';
            $retour .= '"' . $mainCat->getname() . '";';
            $retour .= '"' . str_replace('"', "'", strip_tags($item->getname())) . '";';
            $retour .= '"' . str_replace('"', "'", strip_tags($item->getdescription())) . '";';
            $retour .= '"' . $url . '"' . "\n";
        }

        return $retour;
    }

    /**
     * Is product filre ok ?
     * <ul>
     * <li>Catalog extract from Cdiscount</li>
     * <li>Product integration resukt</li>
     * </ul>
     * 
     * @param array $lines
     * @return boolean
     */
    public function isProductFileOk($lines) {

        $tmp = explode(';', $lines[0]);
        return (count($tmp) >= 4 || $tmp[0] == 'EAN') ? true : false;
    }

    /**
     * Import products
     * 
     * @param array $lines
     * @return string
     * @throws Exception
     */
    public function importProducts($lines) {

        $tmp = array();
        $error = false;
        $errorMessage = '';
        $unavailableProducts = array();
        $nbr = 0;
        $errors = array();

        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        for ($i = 0; $i < count($lines); $i++) {

            // skip first line
            if ($i == 0)
                continue;

            $line = str_replace('"', '', $lines[$i]);

            $infos = explode(';', $line);

            try {

                // import du rapport d'integration au format EAN;REF;SKU
                if(count($infos) == 3){
                    
                    if(trim($infos[0]) == '')
                        continue;
				
                    // test le type d'id utilise (sku ou product_id)
                    $idType = $account->getParam('seller_product_reference');
                    
                    switch($idType){
                        
                        case 'sku':
                            
                            $id = Mage::getModel('catalog/product')->getIdBySku(trim($infos[2]));
                            
                            break;
                        
                        case 'id':
                            
                            $id = trim($infos[2]);
                            
                            break;
                        
                    }                
                    $product = Mage::getModel('catalog/product')->load($id);
                    
                    $id = (!$product->getId()) ? false : $product->getentity_id();
                    $mp_reference = $infos[1];  
                    
                    if ($id === false) {
                        $unavailableProducts[] = $infos[2];
                        continue;
                    }                     
                    
                }else{

                    // import extract from cdiscount backoffice

                    $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration('cdiscount');
                    $mp_reference_index = $config->getcatalogExtractReferenceIndex();
                    $seller_product_reference_index = $config->getcatalogExtractSkuIndex();

                    $id = ($account->getParam('seller_product_reference') == 'sku') ? Mage::getModel('catalog/product')->getIdBySku($infos[$seller_product_reference_index]) : $infos[$seller_product_reference_index];

                    if ($id === false && $account->getParam('seller_product_reference') == 'sku') {

                        // try to add 0...
                        $id = Mage::getModel('catalog/product')->getIdBySku('0' . $infos[$seller_product_reference_index]);

                        if ($id === false) {
                            $unavailableProducts[] = $infos[$seller_product_reference_index];
                            continue;
                        }
                    }
                    
					$mp_reference = $infos[$mp_reference_index];
					
                }

                if (!$mp_reference)
                    throw new Exception('Reference is empty for product id '.$id);

                $add = mage::getModel('MarketPlace/Data')->getCollection()
                        ->addFieldToFilter('mp_marketplace_id', Mage::registry('mp_country')->getId())
                        ->addFieldToFilter('mp_product_id', $id)
                        ->getFirstItem();

                // check if product exists in database
                if ($add->getmp_id()) {

                    $add->setmp_marketplace_status(self::kStatusCreated)
                            ->setmp_reference($mp_reference)
                            ->save();
                } else {

                    // if not add it !
                    $add = mage::getModel('MarketPlace/Data');
                    $add->setmp_marketplace_id(Mage::registry('mp_country')->getId());
                    $add->setmp_exclude('0');
                    $add->setmp_product_id($id);
                    $add->setmp_reference($mp_reference);
                    $add->setmp_marketplace_status(self::kStatusCreated);
                    $add->save();
                }

                $nbr++;
            } catch (Exception $e) {
                $errors[] = 'Error : '.$e->getMessage();
                $error = true;
                $errorMessage .= $e->getMessage();
            }
        }

        $result = array();
        $result[] = $nbr.' products added, '.count($errors).' errors, '.count($unavailableProducts).' products not available';
        foreach($unavailableProducts as $unavailableProduct)
        {
            $result[] = 'Sku "'.$unavailableProduct.'" does not exist in magento';
            $error = true;
        }
        foreach($errors as $error)
        {
            $result[] = $error;
        }

        if ($error == true)
        {
            throw new Exception(implode('<br>', $result));
        }
        else
            return implode('<br>', $result);
    }

    /**
     * Is cdiscount allow generate product feed ?
     *
     * @return boolean true
     */
    public function allowGenerateProductFeed() {
        return true;
    }

    /**
     * Generate product feed
     */
    public function generateProductFeed($request) {

        $retour = '';
        $ids = $request->__get('product_ids');
        $submitted = array();
        $configObj = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject();
        $errorCategorie = false;
        $errorBrand = false;
        $error = false;
        $errorMessage = '';

        $country = Mage::registry('mp_country');
        $storeId = $country->getParam('store_id');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());

        if (count($ids) > 0) {         

            foreach ($ids as $id) {

                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($id);

                if ($product) {

                    if ($product->getimage() != "" && !preg_match('#no_selection#i', $product->getimage())) {
                        $url = Mage::getBaseUrl('media') . 'catalog/product' . $product->getimage();
                    } elseif ($product->getsmall_image() != "" && !preg_match('#no_selection#i', $product->getsmall_image())) {
                        $url = Mage::getBaseUrl('media') . 'catalog/product' . $product->getsmall_image();
                    } else {
                        $url = '';
                    }

                    // category
                    $category = Mage::Helper('Cdiscount/Category')->getCategoryData($product);

                    if (!array_key_exists('code', $category)) {

                        Mage::getModel('MarketPlace/Data')->addMessage($product->getentity_id(), 'Categorie non associee', Mage::registry('mp_country')->getId(), true);
                        if($errorCategorie === false){
                            $errorCategorie = true;
                            $errorMessage .= Mage::Helper('Cdiscount')->__('Some product(s) can not be added because off category association')."\n";
                        }
                        continue;
                    }
                    
                    // brand
                    $manufacturerAttribute = $configObj->getmp_manufacturer_attribute();
                    if($manufacturerAttribute == ""){
                        throw new Exception('Manufacturer attribute is not defined in system > configuration > marketplace');
                    }
                    $cdiscountBrand = Mage::getModel('MarketPlace/Brands')->getBrandForManufacturer('cdiscount', $product->getData($manufacturerAttribute));
                    if (!$cdiscountBrand) {

                        Mage::getModel('MarketPlace/Data')->addMessage($product->getentity_id(), Mage::Helper('Cdiscount')->__('Brand not associated : %s', $product->getAttributeText($manufacturerAttribute)), Mage::registry('mp_country')->getId(), true);
                        if($errorBrand === false){
                            $errorBrand = true;
                            $errorMessage .= Mage::Helper('Cdiscount')->__('Some product(s) can not be added because off product brand')."\n";
                        }                        
                        continue;
                    }

                    // new line
                    switch ($account->getParam('seller_product_reference')) {
                        case 'sku':
                            $retour .= '"' . $product->getsku() . '";'; // prendre sku
                            break;
                        case 'id':
                        default :
                            $retour .= '"' . $id . '";'; // prendre le id
                            break;
                    }

                    $retour .= '"' . Mage::helper('MarketPlace/Barcode')->getBarcodeForProduct($product) . '";'; // code ean
                    $retour .= '"Standard";'; // nature du produit Standard / Variant
                    $retour .= '"' . $cdiscountBrand->getmpb_code() . '";'; // marque
                    $retour .= '"";'; // marque a créér
                    $retour .= '"' . str_replace('"', "'", strip_tags(substr($product->getname(), 0, 30))) . '";'; // libelle court
                    $retour .= '"' . str_replace('"', "'", strip_tags(substr($product->getname(), 0, 50))) . '";'; // libelle long
                    $retour .= '"' . str_replace(array('"', "\n", "\r"), array("'", '', ''), strip_tags(substr($product->getdescription(), 0, 5000))) . '";'; // description 						
                    $retour .= '"' . $url . '";'; // image
                    $retour .= '"' . $category['code'] . '";'; // code category
                    $retour .= '"' . $category['niveau1'] . '";'; // niveau 1
                    $retour .= '"' . $category['niveau2'] . '";'; // niveau 2
                    $retour .= '"' . $category['niveau3'] . '";'; // niveau 3
                    $retour .= '"' . $category['niveau4'] . '";'; // niveau 4
                    $retour .= '""' . "\n"; // mentions legales...
                    
                    $submitted[] = $id;
                    
                }
            }

            Mage::getModel('MarketPlace/Data')->updateStatus($submitted, Mage::registry('mp_country')->getId(), MDN_MarketPlace_Helper_ProductCreation::kStatusPending);
        }                
        
        $error = ($errorCategorie === true || $errorBrand === true) ? true : false;
        
        return array(
            'content' => utf8_decode($retour),
            'error' => $error,
            'errorMessage' => $errorMessage
        );
        
    }

    /**
     * Get product file type
     *
     * @return string
     */
    public function getproductFileType() {
        return 'csv';
    }

    /**
     * Is marketplace allow matching EAN ?
     *
     * @return boolean
     */
    public function allowMatchingEan() {
        return true;
    }

    /**
     *
     */
    public function autoSubmit()
    {
        throw new Exception('This method is not implemented yet');
    }

}
