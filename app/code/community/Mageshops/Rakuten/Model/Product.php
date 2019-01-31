<?php

/**
 * @category    Mageshops
 * @package     Mageshops_Rakuten
 * @license     http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2014 - 2015
 * @author      Taras Kapushchak & Viktors Stepucevs & Kristaps Rjabovs with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Rakuten Product model class
 */
class Mageshops_Rakuten_Model_Product extends Mageshops_Rakuten_Model_Abstract
{

    protected $_errorSavingProduct = false;
    protected $_synchronizedCategoryIds = array();
    protected $_synchronizedCategoryPaths = array();
    protected $_productsToSynchronize = array();
    protected $_synchronizationError = false;
    protected $_rakutenProducts = array();

    /**
     * If sync is being continued in another process, this indicates from which product to start
     * @var int
     */
    protected $_startFrom = 0;

    /**
     * If sync is being continued in another process, this indicates whether to continue from simple or variant products.
     * @var bool
     */
    protected $_continueSimple = true;

    protected function _getSynchronizedCategoriesData()
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();

        $categoryIds = array();
        $categoryPaths = array();

        foreach ($collection as $category) {

            $currentCategoryId = $category->getId();
            $categoryIds[] = $currentCategoryId;
            $categoryId = $currentCategoryId;

            $data = array();
            $data[] = $category->getName();
            while ($categoryId) {
                $_parentCategory = Mage::getModel('catalog/category')->load($categoryId)->getParentCategory();
                $categoryId = $_parentCategory->getId();
                if ($categoryId == 1) {
                    $categoryId = null;
                    continue;
                }
                $data[] = $_parentCategory->getName();
            }
            $data = array_reverse($data);
            $data = implode(' > ', $data);

            $categoryPaths[$currentCategoryId] = $data;
        }

        $categoryIds = array_unique($categoryIds);
        $this->_synchronizedCategoryIds = $categoryIds;
        $this->_synchronizedCategoryPaths = $categoryPaths;

        return $this;
    }

    public function getProductsToSynchronize($startFrom = false)
    {
        $this->setOffset($startFrom);
        $variantHelper = Mage::helper('rakuten/variant');

        if (!$this->_productsToSynchronize) {
            $helper = Mage::helper('rakuten');

            if ($helper->productsFromCategories()) {
                if (!$categoryIds = $this->_synchronizedCategoryIds) {
                    $collection = Mage::getModel('catalog/category')->getCollection()
                        ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToSelect('name')
                        ->addIsActiveFilter();

                    $categoryIds = array();

                    foreach ($collection as $category) {
                        $categoryIds[] = $category->getId();
                    }

                    $categoryIds = array_unique($categoryIds);
                }

                $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->distinct(true)
                    ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
                    ->addAttributeToFilter('category_id', array('in' => $categoryIds))
                    ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                    ->addAttributeToFilter('type_id', $variantHelper->getAllowedProductTypes())
                    ->addOrder('entity_id', Varien_Data_Collection_Db::SORT_ORDER_ASC);
                $collection->getSelect()->group('e.entity_id');
            } else {
                $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                    ->addAttributeToFilter('type_id', $variantHelper->getAllowedProductTypes())
                    ->addOrder('entity_id', Varien_Data_Collection_Db::SORT_ORDER_ASC);
            }

            if ($startFrom != false) {
                $collection->getSelect()->limit(10, $startFrom);
            }

            $this->_productsToSynchronize = $collection;
        }

        return $this->_productsToSynchronize;
    }

    public function setOffset($offset, $continueSimple)
    {
        $this->_startFrom = $offset;
        $this->_continueSimple = $continueSimple;
    }

    public function getAllRakutenProducts()
    {
        $helper = Mage::helper('rakuten');

        if ($helper->getCreateCsv()) {
            return $this;
        }

        $page = 1;
        $perPage = 100;

        try {
            do {
                $pages = $this->getRakutenProducts($page, $perPage);
                $helper->setState($this->__('Fetching product list from Rakuten.'), (($pages == 0) ? 1 : ($page / $pages)));

                if ($message = $helper->checkLowResources()) {
                    $helper->setState($message, 0);
                    $this->_synchronizationError = true;
                    return $this;
                }
            } while ($page++ < $pages);

            $helper->syncLog($helper->__('Product list was fetched from your Rakuten account.'));
        } catch (Exception $e) {
            $helper->syncExceptionLog($e);
        }

        return $this;
    }

    public function getRakutenProducts($page = 1, $perPage = 100)
    {
        $helper = Mage::helper('rakuten');

        $rakuten_request = $helper->getProductRequestUrl();
        $params = array(
            'key' => $helper->getAPIKey(),
            'page' => $page,
            'per_page' => $perPage,
        );
        $request = $helper->callAPI($rakuten_request, $params);

        $pages = $this->_saveRakutenProducts($request);

        $helper->syncLog($helper->__('Page %s of %s was fetched from your Rakuten account.', $page, $pages));

        return $pages;
    }

    protected function _saveRakutenProducts($request)
    {
        $xml = $request->getAnswer();
        $rakuten = simplexml_load_string($xml);

        if ($rakuten === false) {
            $rakuten->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR_NON_XML)->save();
            throw new Exception($this->__('Error while parsing returned XML.'));
        }

        $pages = 1;

        $success = $rakuten->success;
        if ($success) {

            $pages = (int)$rakuten->products->paging->pages;

            foreach ($rakuten->products->product as $product) {

                $this->_rakutenProducts[(string)$product->product_art_no] = array();

                if (!empty($product->images)) {
                    foreach ($product->images->image as $image) {
                        $this->_rakutenProducts[(string)$product->product_art_no][] = (string)$image->comment;
                    }
                }
            }
        }

        return $pages;
    }

    protected function _getRakutenProduct($sku)
    {
        $helper = Mage::helper('rakuten');

        $rakuten_request = $helper->getProductRequestUrl();
        $params = array(
            'key' => $helper->getAPIKey(),
            'search' => $sku,
            'search_field' => 'product_art_no',
            'per_page' => 100,
        );
        $request = $helper->callAPI($rakuten_request, $params);

        $xml = $request->getAnswer();
        $rakuten = simplexml_load_string($xml);

        if ($rakuten === false) {
            $rakuten->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR_NON_XML)->save();
            throw new Exception($this->__('Error while parsing returned XML.'));
        }

        $success = $rakuten->success;
        if ($success) {
            return $rakuten;
        }

        return false;
    }

    protected function _getRakutenProducts($page = 1, $per_page = 100)
    {
        $helper = Mage::helper('rakuten');

        $rakuten_request = $helper->getProductRequestUrl();
        $params = array(
            'key' => $helper->getAPIKey(),
            'page' => $page,
            'per_page' => $per_page,
        );
        $request = $helper->callAPI($rakuten_request, $params);

        $xml = $request->getAnswer();
        $rakuten = simplexml_load_string($xml);

        if ($rakuten === false) {
            $rakuten->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR_NON_XML)->save();
            throw new Exception($this->__('Error while parsing returned XML.'));
        }

        $success = $rakuten->success;
        if ($success) {
            return $rakuten;
        }

        return false;
    }

    /**
     * Removes all products from Rakuten and from local database
     * 
     * @return void
     */
    public function clearAll()
    {
        while ($rakuten = $this->_getRakutenProducts()) {
            if ($rakuten->products->paging->total == 0) {
                break;
            }

            foreach ($rakuten->products->product as $product) {
                $this->deleteRakutenProduct($product->product_art_no);
            }
        }

        // todo: this must be optimized!
        $productIds = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('entity_id')
            ->getAllIds();

        Mage::getSingleton('catalog/product_action')
            ->updateAttributes($productIds, array('rakuten_sync' => 0), 0)
            ->updateAttributes($productIds, array('rakuten_id' => null), 0)
            ->updateAttributes($productIds, array('rakuten_status' => null), 0)
            ->updateAttributes($productIds, array('rakuten_variants' => null), 0)
            ->updateAttributes($productIds, array('rakuten_variant_labels' => null), 0);
    }

    public function syncAllToRakuten($pages = false)
    {
        /** @var Mageshops_Rakuten_Helper_Data $helper */
        $helper = Mage::helper('rakuten');

        if ($pages != false && $pages[0] == 1) {

            $this->_getSynchronizedCategoriesData();

            if ($helper->getCreateCsv()) {
                return $this->_createCsv();
            }

            $helper->syncLog($helper->__('Product synchronization started.'));

            // Delete unneeded products
            $rakutenCollection = Mage::getModel('catalog/product')->getCollection()
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter('rakuten_status', array('eq' => 'to_delete'))
                ->addAttributeToFilter('rakuten_sync', array('neq' => 1));

            $size = $rakutenCollection->getSize();
            $i = 0;
            foreach ($rakutenCollection as $rakutenProduct) {
                $sku = $rakutenProduct->getSku();

                try {
                    if (isset($this->_rakutenProducts[$sku])) {
                        $this->deleteRakutenProduct($sku);
                    }

                    $productIds = array($rakutenProduct->getId());
                    Mage::getSingleton('catalog/product_action')
                        ->updateAttributes($productIds, array('rakuten_id' => null), 0)
                        ->updateAttributes($productIds, array('rakuten_status' => null), 0)
                        ->updateAttributes($productIds, array('rakuten_variants' => null), 0)
                        ->updateAttributes($productIds, array('rakuten_variant_labels' => null), 0);
                } catch (Exception $e) {
                    $helper->syncLog($helper->__('Error while deleting product [%s]: %s', $sku, $e->getMessage()));
                    $helper->syncExceptionLog($e);
                }

                $helper->setState($this->__('Cleaning products at Rakuten.'), ++$i / $size);

                if ($message = $helper->checkLowResources()) {
                    $helper->setState($message, 0);
                    $this->_synchronizationError = true;
                    return $this;
                }
            }
        }

        // Simple product sync
        if ($this->_continueSimple === true) {
            $this->syncProducts(true);
        }

        // Variant product sync
        $this->syncProducts(false);

        $helper->syncLog($helper->__('Product synchronization finished.'));

        $helper->clearForceResave();

        return $this;
    }

    /**
     * Fetch and prepare description field for adding to rakuten product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    private function _prepareDescription(Mage_Catalog_Model_Product $product)
    {
        $description = Mage::helper('rakuten')->getMappedValue($product, 'description');
        if (strcmp($description, strip_tags($description)) == 0) {
            $description = nl2br($description);
        }
        $description = str_replace(array("\n", "\r"), '', $description);
        $tags = '<a><b><blockquote><br><caption><col><colgroup><div><em><font><h1><h2><h3><h4><h5><h6><hr><i><img>';
        $tags .= '<label><li><link><param><object><ol><p><q><s><small><span><strike><strong><style>';
        $tags .= '<table><tbody><td><th><thead><title><tr><u><ul>';
        $description = strip_tags($description, $tags);

        if ($description == '') {
            $description = Mage::helper('rakuten')->__('No description');
        }

        return $description;
    }

    private function _addRakutenImage($options = array())
    {
        $helper = Mage::helper('rakuten');
        $rakutenRequest = $helper->getUrl('addProductImage');

        $params = $helper->getRequestParams();
        $params = array_merge($params, $options);

        $request = $helper->callAPI($rakutenRequest, $params);
        $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);
        $imId = (string)$rakuten->image_id;

        return $imId;
    }

    private function _deleteRakutenImage($imageId)
    {
        if (empty($imageId)) {
            return $this;
        }

        $helper = Mage::helper('rakuten');
        $rakutenRequest = $helper->getUrl('deleteProductImage');

        $params = $helper->getRequestParams();
        $params['image_id'] = $imageId;

        $request = $helper->callAPI($rakutenRequest, $params);
        $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);

        return $this;
    }

    /**
     * Adding product image to rakuten
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int|null $rakutenId
     * @return $this
     */
    protected function _rakutenAddProductImages(Mage_Catalog_Model_Product $product, $rakutenId = null)
    {
        $imgCount = 0;
        $productArtNo = Mage::helper('rakuten')->getMappedValue($product, 'product_art_no');

        $defaultImgUrl = Mage::helper('rakuten')->getMappedValue($product, 'default_image');
        if (!$defaultImgUrl || $defaultImgUrl == 'no_selection') {
            $defaultImgUrl = false;
        }

        $rakutenImages = $this->_getRakutenImageIds($productArtNo);
        $addImages = array();

        $product->getResource()->getAttribute('media_gallery')->getBackend()->afterLoad($product);

        $images = $product->getMediaGallery('images');
        if (is_array($images)) {
            foreach ($images as $image) {
                $params = array();

                if (!file_exists($product->getMediaConfig()->getMediaPath($image['file']))) {
                    continue;
                }

                if (($key = array_search($image['value_id'], $rakutenImages)) !== false) {
                    unset($rakutenImages[$key]);
                    if (++$imgCount >= 10) {
                        break;
                    }
                    continue;
                }

                if ($image['file'] == $defaultImgUrl) {
                    $params['default'] = 1;
                    $defaultImgUrl = false;
                } elseif ($image['disabled'] || ($defaultImgUrl && $imgCount >= 9)) {
                    continue;
                }

                $params['product_art_no'] = $productArtNo;
                $params['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                $params['comment'] = $image['value_id'];

                $addImages[] = $params;

                if (++$imgCount >= 10) {
                    break;
                }
            }
        }

        // Clean unneeded images first to clean space if needed
        foreach ($rakutenImages as $imageId) {
            $this->_deleteRakutenImage($imageId);
        }

        // Upload all new images to Rakuten
        foreach ($addImages as $params) {
            $this->_addRakutenImage($params);
        }

        return $this;
    }

    private function _getRakutenImageIds($productArtNo)
    {
        $ids = array();

        if (isset($this->_rakutenProducts[$productArtNo])) {
            foreach ($this->_rakutenProducts[$productArtNo] as $image) {
                $ids[] = $image;
            }
        }

        return $ids;
    }

    /**
     * Assign categories for rakuten product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    protected function _addProductCategories(Mage_Catalog_Model_Product $product)
    {
        $helper = Mage::helper('rakuten');
        $rakutenRequest = $helper->getAddProductToShopCategoryRequestUrl();
        $resource = Mage::getResourceModel('catalog/category');

        $categoryIds = $product->getCategoryIds();

        foreach ($categoryIds as $categoryId) {

            $rakutenSync = $resource->getAttributeRawValue($categoryId, 'rakuten_sync', Mage::app()->getStore());
            $rakutenCategoryId = $resource->getAttributeRawValue($categoryId, 'rakuten_category_id', Mage::app()->getStore());

            if ($rakutenSync && $rakutenCategoryId) {
                $params = $helper->getRequestParams();
                $params['shop_category_id'] = $rakutenCategoryId;
                $params['external_shop_category_id'] = $categoryId;
                $params['product_art_no'] = $helper->getMappedValue($product, 'product_art_no');

                $request = $helper->callAPI($rakutenRequest, $params);
                $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);
            }
        }

        return $this;
    }

    private function _getShippingGroup(Mage_Catalog_Model_Product $product)
    {
        $shippingGroup = (int)$product->getRakutenShippingGroup();
        if ($shippingGroup < 1 || $shippingGroup > 20) {
            $shippingGroup = 1;
        }

        return $shippingGroup;
    }

    protected function _editProduct(Mage_Catalog_Model_Product $product, $rakutenId = false)
    {
        $helper = Mage::helper('rakuten/variant');

        if ($helper->isVariant($product)) {
            $rakutenId = $this->_editVariantProduct($product, $rakutenId);
        } else if ($helper->isSimple($product)) {
            $rakutenId = $this->_editBaseProduct($product, $rakutenId);
        } else {
            $rakutenId = false;
        }

        return $rakutenId;
    }

    protected function _getRakutenPrice($product, $price)
    {
        $rakutenAddon = trim($product->getRakutenCustomPrice());
        $rakutenAddon = str_replace(' ', '', $rakutenAddon);
        if (substr($rakutenAddon, -1) === '%') {
            $rakutenAddon = floatval(trim($rakutenAddon, '%'));
            $price = $price + $price * $rakutenAddon / 100.0;
        } else {
            $price = $price + floatval($rakutenAddon);
        }
        return number_format(Mage::helper('tax')->getPrice($product, $price, true), 2, '.', '');
    }

    private function _getDelivery(Mage_Catalog_Model_Product $product)
    {
        $delivery = (int)$product->getRakutenDelivery();
        if (in_array($delivery, array(0, 3, 5, 7, 10, 15, 20, 30, 40, 50, 60))) {
            return $delivery;
        }
        return 0;
    }

    protected function _editBaseProduct(Mage_Catalog_Model_Product $product, $rakutenId = false, $simple = true)
    {
        $helper = Mage::helper('rakuten');
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        $params = $helper->getRequestParams();

        $helper->syncLog($helper->__('Status: "%s"', $product->getRakutenStatus()));

        if ($product->getRakutenStatus() == 'exported') {
            $rakutenRequest = $helper->getEditProductRequestUrl();
        } else {
            $rakutenRequest = $helper->getAddProductRequestUrl();
        }

        $productArtNo = $helper->getMappedValue($product, 'product_art_no');
        $producer = $helper->getMappedValue($product, 'producer');
        $name = $helper->prepareName($product);
        
        $configPolicy = ($product->getRakutenStockPolicy() == 1 || $product->getRakutenStockPolicy() === null) ? 1 : 0;
        $configHomepage = ($product->getRakutenHomepage() == 1 || $product->getRakutenHomepage() === null) ? 1 : 0;
            
        $params['product_art_no'] = (strlen($productArtNo) > 50) ? substr($productArtNo, 0, 50) : $productArtNo;
        $params['producer'] = (strlen($producer) > 30) ? substr($producer, 0, 30) : $producer;
        $params['name'] = (strlen($name) > 100) ? substr($name, 0, 100) : $name;
        $params['stock_policy'] = $configPolicy;
        $params['homepage'] = $configHomepage;
        $params['min_order_qty'] = (int)$stock->getMinSaleQty();
        $params['description'] = $this->_prepareDescription($product);
        $params['shipping_group'] = (int)$this->_getShippingGroup($product);
        $params['visible'] = ($product->getRakutenVisible() == 0) ? 0 : 1;
        $params['homepage'] = ($helper->isProductNew($product)) ? 1 : 0;

        if ($tax = $helper->getRakutenTax($product->getTaxClassId())) {
            $params['tax'] = $tax;
        }

        if ($rakutenDefaultCategoryId = $product->getRakutenDefaultCategoryId()) {
            $params['rakuten_category_id'] = $rakutenDefaultCategoryId;
        }

        if ($simple === true || $product->getRakutenStatus() != 'exported') {

            $params['price'] = $this->_getRakutenPrice($product, $helper->getMappedValue($product, 'price'));
            $params['stock'] = (int)$stock->getQty();
            $params['available'] = $product->getStatus() == 1 ? 1 : 0;
            $params['delivery'] = $this->_getDelivery($product);
            $params['isbn'] = $helper->getMappedValue($product, 'isbn');
            $params['ean'] = $helper->getMappedValue($product, 'ean');
            $params['stock_policy'] = (int)$product->getRakutenStockPolicy();
            
            // If product is out of stock and stock policy is set to yes
            if ($params['stock_policy'] == 1 && $params['stock'] == 0) {
            	$params['available'] = 0;
                $params['stock_policy'] = 1;
            }
            
            $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');

            if ($specialPrice === false) {
                $specialPrice = $product->getFinalPrice();
            }

            if ($specialPrice) {
                $params['price_reduced'] = $this->_getRakutenPrice($product, $specialPrice);

                if ($params['price'] <= $params['price_reduced']) {
                    unset($params['price_reduced']);
                }
            }

            if ($basePrice = $this->_getBasePrice($product)) {
                $params['baseprice_volume'] = $basePrice['amount'];
                $params['baseprice_unit'] = $basePrice['unit'];
            }
        }

        $request = $helper->callAPI($rakutenRequest, $params);
        $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);

        if (!$rakutenId) {
            $rakutenId = (int)$rakuten->product_id;
        }

        try {
            $this->_addProductCategories($product);
            $this->_rakutenAddProductImages($product, $rakutenId);
        } catch (Exception $e) {
            $this->_errorSavingProduct = true;
            $helper->syncExceptionLog($e);
        }

        return $rakutenId;
    }

    private function _getBasePrice(Mage_Catalog_Model_Product $product)
    {
        $helper = Mage::helper('rakuten');
        $basePriceAmount = $helper->getMappedValue($product, 'baseprice_volume');
        $basePriceUnit = $helper->getMappedValue($product, 'baseprice_unit');

        if (empty($basePriceAmount) || empty($basePriceUnit)) {
            return false;
        }

        $units = array(
            'KG' => 'kg',
            'G' => 'g',
            'L' => 'l',
            'ML' => 'ml',
            'M' => 'm',
            'CM' => 'cm',
        );

        if (!isset($units[$basePriceUnit])) {
            return false;
        }

        if ($basePriceAmount == round($basePriceAmount)) {
            $basePriceAmount = number_format($basePriceAmount, 1);
        }

        return array(
            'unit' => $units[$basePriceUnit],
            'amount' => $basePriceAmount,
        );
    }

    /**
     * Get's all possible variations of bundled product
     *
     * @param $attributeOptions array - child products options
     * @return array - all possible options
     * @throws Exception
     */
    private function _bundledVariations($attributeOptions, Mage_Catalog_Model_Product $product)
    {
        /* var $product Mage_Catalog_Model_Product */
        $optionalItems = array();
        $helper = Mage::helper('rakuten');
        $optionalItemsCombinations = array();
        $session = Mage::getSingleton('core/session');
        $session->unsetData('bundle_prefix');

        // Get all attributes assign to bundled product
        foreach ($attributeOptions as $variant) {
            if (!isset($optionalItems[$variant['attribute_id']])) {
                $optionalItems[$variant['attribute_id']] = array();
            }
            foreach ($variant['values'] as $sku => $attribute) {
                $optionalItems[$variant['attribute_id']][] = $attribute;
            }
        }

        // Merge them and make all possible combinations
        $dataSet = new Mageshops_Rakuten_Helper_Dataset($optionalItems);
        $optionalItemsCombination = $dataSet->getNoRepeatCombinations();
        $bundlePrefix = rand(0, 100000);
        $session->setData('bundle_prefix', $bundlePrefix);
        // Refactor all posible variations
        foreach ($optionalItemsCombination as $k => $items) {
            $resultingItem = array(
                'sku' => array(),
                'price' => 0,
                'values' => array(),
                'stock' => array(),
                'available' => array()
            );

            $bundlePrefix = $session->getData('bundle_prefix');
            $resultingItem['sku'][] = $helper->getBundledPrefix() . $bundlePrefix;
            $bundlePrefix++;
            $session->setData('bundle_prefix', $bundlePrefix);
            $i = 1;
            foreach ($items as $item) {
                $resultingItem['sku'][] = $item['sku'];
                $resultingItem['stock'][] = $item['stock'];
                $resultingItem['available'][] = $item['available'];
                $resultingItem['name'][] = $item['label'];
                $resultingItem['price'] += (float)$item['pricing_value'];
                $resultingItem['values'][$i]['type'] = $this->_mapVariantDefinition($item['variant_label']);
                $resultingItem['values'][$i]['value'] = $item['label'];
                $i++;
            }

            // If price is equal zero, price is dynamic, otherwise is fixed
            if ($product->getPrice() !== 0) {
                $resultingItem['price'] = $product->getPrice();
            }

            $resultingItem['sku'] = implode('|', $resultingItem['sku']);
            $resultingItem['name'] = implode('+', $resultingItem['name']);
            $resultingItem['stock'] = min($resultingItem['stock']);
            $resultingItem['available'] = min($resultingItem['available']);
            $optionalItemsCombinations[$k] = $resultingItem;
        }

        return $optionalItemsCombinations;
    }

    private function _updateVariants(Mage_Catalog_Model_Product $product, $attributeOptions)
    {
        $helper = Mage::helper('rakuten');
        $helperVariant = Mage::helper('rakuten/variant');
        $productTypeId = $product->getTypeId();

        $usedProducts = $helperVariant->getUsedProducts($product);
        if (count($usedProducts) <= 0) {
            throw new Exception($helper->__('Product should contain child products. It will not be saved.'));
        }

        if (!$variantSkus = Mage::helper('core')->jsonDecode($product->getRakutenVariants())) {
            $variantSkus = array();
        }
        $rakutenVariants = array();

        // If product is configurable or grouped
        if ($productTypeId != 'bundle') {
            foreach ($usedProducts as $childProduct) {
                $sku = $helper->getMappedValue($childProduct, 'product_art_no');

                if (($key = array_search($sku, $variantSkus)) !== false) {
                    $rakutenRequest = $helper->getEditProductMultiVariantRequestUrl();
                    unset($variantSkus[$key]);
                } else {
                    $rakutenRequest = $helper->getAddProductMultiVariantRequestUrl();
                }

                $params = $helper->getRequestParams();
                $params['product_art_no'] = $helper->getMappedValue($product, 'product_art_no');
                $params['variant_art_no'] = $sku;
                $params['available'] = $childProduct->getStatus() == 1 ? 1 : 0;
                $params['delivery'] = $this->_getDelivery($product);

                $params['stock'] = $helperVariant->getStock($product, $childProduct);

                if ($product->getRakutenStockPolicy()) {
                    if ($params['stock'] <= 0) {
                        $params['available'] = 0;
                        $params['stock_policy'] = 1;
                    }
                }

                $price = (float)$helper->getMappedValue($product, 'price');
                $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');

                if ($helperVariant->isSimple($product)) {
                    $i = 1;
                    foreach ($attributeOptions as $variant) {
                        $attributeValue = $childProduct->getData('variant_' . $variant['attribute_code']);
                        $params['variation' . $i . '_type'] = $this->_mapVariantDefinition($variant['label']);
                        $params['variation' . $i . '_value'] = $variant['values'][$attributeValue]['label'];
                        if ($variant['values'][$attributeValue]['is_percent']) {
                            $price *= 1.0 + ((float)$variant['values'][$attributeValue]['pricing_value'] / 100.0);
                        } else {
                            $price += (float)$variant['values'][$attributeValue]['pricing_value'];
                        }
                        if ($specialPrice) {
                            if ($variant['values'][$attributeValue]['is_percent']) {
                                $specialPrice *= 1.0 + ((float)$variant['values'][$attributeValue]['pricing_value'] / 100.0);
                            } else {
                                $specialPrice += (float)$variant['values'][$attributeValue]['pricing_value'];
                            }
                        }
                        $i++;
                    }
                } else {
                    $i = 1;
                    foreach ($attributeOptions as $variant) {
                        $attributeValue = $childProduct->getData($variant['attribute_code']);
                        $params['variation' . $i . '_type'] = $this->_mapVariantDefinition($variant['label']);
                        $params['variation' . $i . '_value'] = $variant['values'][$attributeValue]['label'];

                        if ($helper->getVariantPriceSimple()) {
                            $price = (float)$helper->getMappedValue($childProduct, 'price');
                        } else {
                            $price += (float)$variant['values'][$attributeValue]['pricing_value'];
                        }
                        if ($specialPrice) {
                            if ($helper->getVariantPriceSimple()) {
                                $specialPrice = (float)$helper->getMappedValue($childProduct, 'price_reduced');
                            } else {
                                $specialPrice += (float)$variant['values'][$attributeValue]['pricing_value'];
                            }
                        }
                        $i++;
                    }
                }

                $params['price'] = $this->_getRakutenPrice($product, $price);

                $params['price_reduced'] = 0;
                if ($specialPrice) {
                    $params['price_reduced'] = $this->_getRakutenPrice($product, $specialPrice);
                    if ($params['price'] <= $params['price_reduced']) {
                        unset($params['price_reduced']);
                    }
                }

                if ($basePrice = $this->_getBasePrice($product)) {
                    $params['baseprice_volume'] = $basePrice['amount'];
                    $params['baseprice_unit'] = $basePrice['unit'];
                }

                $request = $helper->callAPI($rakutenRequest, $params);

                // If is not successful, product already exists, try to edit
                if (!$this->isSuccessfulRequest($request)) {
                    $rakutenRequest = $helper->getEditProductMultiVariantRequestUrl();
                    $request = $helper->callAPI($rakutenRequest, $params);
                }

                $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);

                $rakutenVariants[] = $sku;
            }
        } else {
            $optionalItemsCombinations = $this->_bundledVariations($attributeOptions, $product);

            foreach ($optionalItemsCombinations as $childVariation) {
                $sku = $childVariation['sku'];

                if (($key = array_search($sku, $variantSkus)) !== false) {
                    $rakutenRequest = $helper->getEditProductMultiVariantRequestUrl();
                    unset($variantSkus[$key]);
                } else {
                    $rakutenRequest = $helper->getAddProductMultiVariantRequestUrl();
                }

                $params = $helper->getRequestParams();
                $params['product_art_no'] = $helper->getMappedValue($product, 'product_art_no');
                $params['variant_art_no'] = $sku;
                $params['available'] = $childVariation['available'];
                $params['delivery'] = 0;
                $params['stock'] = $childVariation['stock'];

                if ($product->getRakutenStockPolicy()) {
                    if ($params['stock'] <= 0) {
                        $params['available'] = 0;
                        $params['stock_policy'] = 1;
                    }
                }
                $price = (float)$helper->getMappedValue($product, 'price');

                $i = 1;
                foreach ($childVariation['values'] as $variant) {
                    $params['variation' . $i . '_type'] = $childVariation['values'][$i]['type'];
                    $params['variation' . $i . '_value'] = $childVariation['values'][$i]['value'];
                    $price = $childVariation['price'];
                    $i++;
                }

                $params['price'] = $this->_getRakutenPrice($product, $price);

                if ($basePrice = $this->_getBasePrice($product)) {
                    $params['baseprice_volume'] = $basePrice['amount'];
                    $params['baseprice_unit'] = $basePrice['unit'];
                }

                $request = $helper->callAPI($rakutenRequest, $params);

                // If is not successful, product already exists, try to edit
                if (!$this->isSuccessfulRequest($request)) {
                    $rakutenRequest = $helper->getEditProductMultiVariantRequestUrl();
                    $request = $helper->callAPI($rakutenRequest, $params);
                }

                $rakutenVariants[] = $childVariation['sku'];
            }
        }

        $product->setData('rakuten_variants', Mage::helper('core')->jsonEncode($rakutenVariants))
            ->getResource()
            ->saveAttribute($product, 'rakuten_variants');

        foreach ($variantSkus as $variantSku) {
            $rakutenRequest = $helper->getUrl('deleteProductVariant');
            $params = $helper->getRequestParams();
            $params['variant_art_no'] = $variantSku;

            $request = $helper->callAPI($rakutenRequest, $params);
            $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);
        }

        return $this;
    }

    protected function _editVariantProduct(Mage_Catalog_Model_Product $product, $initialRakutenId = false)
    {
        $helper = Mage::helper('rakuten');
        $vHelper = Mage::helper('rakuten/variant');

        $attributeOptions = $vHelper->getProductOptions($product);

        /** @var Mageshops_Rakuten_Model_Rakuten_Product $rakutenProduct */
        $rakutenId = $this->_editBaseProduct($product, $initialRakutenId, false);

        $rakutenVariantLabels = Mage::helper('core')->jsonDecode($product->getRakutenVariantLabels());
        $variantLabels = array();

        if (empty($rakutenVariantLabels) && count($attributeOptions) > 0) {

            $params = $helper->getRequestParams();
            $params['product_art_no'] = (string)$helper->getMappedValue($product, 'product_art_no');

            $i = 1;
            foreach ($attributeOptions as $variant) {
                $params['variant_' . $i] = (string)$this->_mapVariantDefinition($variant['label']);
                $variantLabels[] = (string)$this->_mapVariantDefinition($variant['label']);
                $i++;
            }

            // If rakuten id is set do edit
            $rakutenRequest = $helper->getAddProductVariantDefinitionRequestUrl();
            $request = $helper->callAPI($rakutenRequest, $params);

            // If is not successful, product already exists
            if (!$this->isSuccessfulRequest($request)) {
                $rakutenRequest = $helper->getEditProductVariantDefinitionRequestUrl();
                $request = $helper->callAPI($rakutenRequest, $params);
            }

            // Check answer
            $helper->checkAnswer($request, $params, $rakutenRequest);

            $product->setData('rakuten_variant_labels', Mage::helper('core')->jsonEncode($variantLabels))
                ->getResource()
                ->saveAttribute($product, 'rakuten_variant_labels');
        }

        $this->_updateVariants($product, $attributeOptions);


        return $rakutenId;
    }

    protected function _mapVariantDefinition($value)
    {
        $len = Mage::helper('core/string')->strlen($value);
        if ($len > 20) {
            $helper = Mage::helper('rakuten');
            $mappings = $helper->getVariantDefinitionMappings();
            $code = $helper->mapCode($value);
            if (!empty($mappings[$code])) {
                return $mappings[$code];
            } else {
                $helper->addVariantDefinitionMapping($value);
                $helper->syncLog($helper->__('Some variant definitions need to be mapped. "%s"', $value));
                throw new Exception($helper->__('Some variant definitions need to be mapped.'));
            }
        }

        return $value;
    }

    protected function _setProductHash($product, $productHash)
    {
        $helper = Mage::helper('rakuten');
        $params = $helper->getRequestParams();
        $params['product_art_no'] = $helper->getMappedValue($product, 'product_art_no');
        $params['comment'] = $productHash;

        $rakutenRequest = $helper->getEditProductRequestUrl();
        $request = $helper->callAPI($rakutenRequest, $params);
        $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);

        return $this;
    }

    public function syncProductToRakuten($product)
    {
        $helper = Mage::helper('rakuten');
        $error = false;

        if (!$product instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($product);
            if (!$product) {
                throw new Exception($helper->__('Cannot load product %s', $product));
            }
        }

        $productArtNo = $helper->getMappedValue($product, 'product_art_no');

        if (Mage::helper('rakuten/variant')->isChild($product)) {
            $helper->syncLog($helper->__('Simple product %s belongs to configurable. Skipped.', $productArtNo));
            unset($helper);
            return false;
        }

        $productRakutenId = $product->getRakutenId();

        try {
            $rakutenId = $this->_editProduct($product, $productRakutenId);
        } catch (Exception $e) {
            // try to recreate product from scratch once more in case of conflicting data
            $helper->syncLog($helper->__('Trying to recreate product [%s]: %s', $productArtNo, $e->getMessage()));
            $error = true;
        }

        if ($error == true) {
            if (isset($this->_rakutenProducts[$productArtNo])) {
                try {
                    $this->deleteRakutenProduct($productArtNo);
                } catch (Exception $e) {
                    $helper->syncLog($helper->__('Unable to delete product [%s]: %s', $productArtNo, $e->getMessage()));
                }
            }

            try {
                $productId = $product->getId();
                $productIds = array($productId);
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($productIds, array('rakuten_id' => null), 0)
                    ->updateAttributes($productIds, array('rakuten_status' => 'to_export'), 0)
                    ->updateAttributes($productIds, array('rakuten_variants' => null), 0)
                    ->updateAttributes($productIds, array('rakuten_variant_labels' => null), 0);
                $product = Mage::getModel('catalog/product')->load($productId);

                $rakutenId = $this->_editProduct($product, false);
                $product->setData('rakuten_status', 'exported')
                    ->getResource()
                    ->saveAttribute($product, 'rakuten_status');
            } catch (Exception $e) {
                $helper->syncLog($helper->__('Unable to recreate product [%s]: %s', $productArtNo, $e->getMessage()));
            }
        }

        if (isset($rakutenId) && $productRakutenId != $rakutenId) {
            $product->setData('rakuten_id', $rakutenId)
                ->getResource()
                ->saveAttribute($product, 'rakuten_id');
        }
        if (isset($rakutenId)) {
            $helper->syncLog($helper->__('Product %s was synchronized to your Rakuten account.', $productArtNo));
            return $rakutenId;
        } else {
            $helper->syncLog($helper->__('Product %s was not synchronized to your Rakuten account.', $productArtNo));
        }
    }

    public function deleteRakutenProduct($rakutenSku)
    {
        if (!empty($rakutenSku)) {

            $helper = Mage::helper('rakuten');
            $params = $helper->getRequestParams();

            $params['product_art_no'] = (string)$rakutenSku;

            $rakutenRequest = $helper->getDeleteProductRequestUrl();

            $request = $helper->callAPI($rakutenRequest, $params);
            $rakuten = $helper->checkAnswer($request, $params, $rakutenRequest);

            $helper->syncLog($helper->__('Product %s was deleted from your Rakuten account.', $rakutenSku));
        }

        return $this;
    }

    protected function _createCsv()
    {
        $helper = Mage::helper('rakuten');

        // Synchronize needed products
        $collection = $this->getProductsToSynchronize();

        $rows = array();
        $rows[] = $this->_getCsvHeader();
        foreach ($collection as $product) {
            try {
                $item = $this->_getCsvRows($product);
                if ($item !== false) {
                    $rows = array_merge($rows, $item);
                }
            } catch (Exception $e) {
                $helper->syncLog($helper->__('Error while adding product [%s] to csv: %s', $helper->getMappedValue($product, 'product_art_no'), $e->getMessage()));
                $helper->syncExceptionLog($e);
            }
            if ($message = $helper->checkLowResources()) {
                $helper->setState($message, 0);
                $this->_synchronizationError = true;
                return $this;
            }
        }

        $fileName = Mage::getBaseDir('media') . '/rakuten_products.csv';
        $csv = new Varien_File_Csv();
        $csv->setDelimiter(';');
//        $csv->setEnclosure('"');
        $csv->saveData($fileName, $rows);

        return $this;
    }

    protected function _getCsvRows(Mage_Catalog_Model_Product $product)
    {
        $helper = Mage::helper('rakuten');
        $vHelper = Mage::helper('rakuten/variant');
        $rows = array();

        if (Mage::helper('rakuten/variant')->isChild($product)) {
            $helper->syncLog($helper->__('Simple product %s belongs to configurable. Skipped.', $helper->getMappedValue($product, 'product_art_no')));
            return false;
        }

        $sku = $helper->getMappedValue($product, 'product_art_no');

        $rakutenProduct = Mage::getModel('rakuten/rakuten_product')->load($sku, 'sku');
        $rakutenId = $rakutenProduct->getRakutenId();

        if ($vHelper->isVariant($product)) {
            $rows = array_merge($rows, $this->_addVariantProduct($product, $rakutenId));
        } else if ($vHelper->isSimple($product)) {
            $rows[] = $this->_addSimpleProduct($product, $rakutenId);
        } else {
            return false;
        }

        return $rows;
    }

    protected function _addSimpleProduct(Mage_Catalog_Model_Product $product, $rakutenId)
    {
        $helper = Mage::helper('rakuten');

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        $available = $product->getStatus() == 1 ? 1 : 0;
        if ($stockPolicy = $product->getRakutenStockPolicy()) {
            if ($stock->getQty() <= 0) {
                $available = 0;
//                $stockPolicy = 1;
            }
        }


        $price = $this->_getRakutenPrice($product, $helper->getMappedValue($product, 'price'));
        $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');
        if ($specialPrice === false) {
            $specialPrice = $product->getFinalPrice();
        }
        if ($specialPrice) {
            $specialPrice = $this->_getRakutenPrice($product, $specialPrice);
            if ($price <= $specialPrice) {
                $specialPrice = null;
            }
        }

        $categoryIds = $product->getCategoryIds();
        $categoryPaths = $this->_synchronizedCategoryPaths;
        $categories = array();

        foreach ($categoryIds as $categoryId) {
            if (isset($categoryPaths[$categoryId])) {
                $categories[] = $categoryPaths[$categoryId];
            }
        }

        if (count($categories) == 0) {
            $categories[] = $helper->__('Default Category');
        }


        $row = array(
            'id' => $rakutenId,
            'variante_zu_id' => null,
            'varianten' => null,
            'varianten_sortierung' => null,
            'artikelnummer' => $helper->getMappedValue($product, 'product_art_no'),
            'mpn' => $product->getMpn(),
            'isbn_ean' => $helper->getMappedValue($product, 'isbn'),
            'produktname' => $helper->prepareName($product),
            'preis' => $price,
            'reduzierter_preis' => $specialPrice,
            'bezug_reduzierter_preis' => 'UVP',
            'versandgruppe' => $this->_getShippingGroup($product),
            'grundpreis_einheit' => null,
            'grundpreis_inhalt' => null,
            'mwst_klasse' => 1,
            'bestandsverwaltung_aktiv' => (int)$stockPolicy,
            'lagerbestand' => (int)$stock->getQty(),
            'lieferzeit' => $this->_getDelivery($product),
            'mindestbestellmenge' => (int)$stock->getMinSaleQty(),
            'staffelung' => null,
            'produkt_bestellbar' => $available,
            'sichtbar' => ($product->getRakutenVisible() === 0) ? 0 : 1,
            'connect_deaktiviert' => 0,
            'hersteller' => $helper->getMappedValue($product, 'producer'),
            'beschreibung' => $this->_prepareDescription($product),
            'deklarationen' => null,
            'kategorien' => implode('||', $categories),
            'rakuten_kategorie' => $product->getRakutenDefaultCategoryId(),
            'cross_selling_titel' => null,
            'cross_selling_produkt_ids' => null,
            'bild1' => null,
            'bild2' => null,
            'bild3' => null,
            'bild4' => null,
            'bild5' => null,
            'bild6' => null,
            'bild7' => null,
            'bild8' => null,
            'bild9' => null,
            'bild10' => null,
            'marketing_url' => null,
            'seo_title' => $helper->prepareName($product, 70, 'meta_title'),
            'seo_keywords' => $helper->prepareName($product, 170, 'meta_keyword'),
            'seo_description' => $helper->prepareName($product, 170, 'meta_description'),
        );

        if ($basePrice = $this->_getBasePrice($product)) {
            $row['grundpreis_einheit'] = $basePrice['unit'];
            $row['grundpreis_inhalt'] = $basePrice['amount'];
        }

        if ($tax = $helper->getRakutenTax($product->getTaxClassId())) {
            $row['mwst_klasse'] = $tax;
        }

        if ($row['lagerbestand'] < 0) {
            $row['lagerbestand'] = 0;
        }

        $images = $this->_getImages($product);
        foreach ($images as $idx => $image) {
            $row['bild' . $idx] = $image;
        }

        return $row;
    }

    protected function _getImages(Mage_Catalog_Model_Product $product)
    {
        $imgCount = 1;
        $addImages = array();

        $defaultImgUrl = Mage::helper('rakuten')->getMappedValue($product, 'default_image');
        if (!$defaultImgUrl || $defaultImgUrl == 'no_selection') {
            $defaultImgUrl = false;
        } else {
            $addImages[$imgCount] = $product->getMediaConfig()->getMediaUrl($defaultImgUrl);
            $imgCount++;
        }

        $product->getResource()->getAttribute('media_gallery')->getBackend()->afterLoad($product);

        $images = $product->getMediaGallery('images');
        if (is_array($images)) {
            foreach ($images as $image) {

                if (!file_exists($product->getMediaConfig()->getMediaPath($image['file']))) {
                    continue;
                }

                if ($image['file'] == $defaultImgUrl || $image['disabled']) {
                    continue;
                }

                $addImages[$imgCount] = $product->getMediaConfig()->getMediaUrl($image['file']);

                if (++$imgCount >= 10) {
                    break;
                }
            }
        }

        return $addImages;
    }

    protected function _getCsvHeader()
    {
        $header = array(
            'id' => 'id',
            'variante_zu_id' => 'variante_zu_id',
            'varianten' => 'varianten',
            'varianten_sortierung' => 'varianten_sortierung',
            'artikelnummer' => 'artikelnummer',
            'mpn' => 'mpn',
            'isbn_ean' => 'isbn_ean',
            'produktname' => 'produktname',
            'preis' => 'preis',
            'reduzierter_preis' => 'reduzierter_preis',
            'bezug_reduzierter_preis' => 'bezug_reduzierter_preis',
            'versandgruppe' => 'versandgruppe',
            'grundpreis_einheit' => 'grundpreis_einheit',
            'grundpreis_inhalt' => 'grundpreis_inhalt',
            'mwst_klasse' => 'mwst_klasse',
            'bestandsverwaltung_aktiv' => 'bestandsverwaltung_aktiv',
            'lagerbestand' => 'lagerbestand',
            'lieferzeit' => 'lieferzeit',
            'mindestbestellmenge' => 'mindestbestellmenge',
            'staffelung' => 'staffelung',
            'produkt_bestellbar' => 'produkt_bestellbar',
            'sichtbar' => 'sichtbar',
            'connect_deaktiviert' => 'connect_deaktiviert',
            'hersteller' => 'hersteller',
            'beschreibung' => 'beschreibung',
            'deklarationen' => 'deklarationen',
            'kategorien' => 'kategorien',
            'rakuten_kategorie' => 'rakuten_kategorie',
            'cross_selling_titel' => 'cross_selling_titel',
            'cross_selling_produkt_ids' => 'cross_selling_produkt_ids',
            'bild1' => 'bild1',
            'bild2' => 'bild2',
            'bild3' => 'bild3',
            'bild4' => 'bild4',
            'bild5' => 'bild5',
            'bild6' => 'bild6',
            'bild7' => 'bild7',
            'bild8' => 'bild8',
            'bild9' => 'bild9',
            'bild10' => 'bild10',
            'marketing_url' => 'marketing_url',
            'seo_title' => 'seo_title',
            'seo_keywords' => 'seo_keywords',
            'seo_description' => 'seo_description',
        );

        return $header;
    }

    protected function _addVariantProduct(Mage_Catalog_Model_Product $product, $rakutenId)
    {
        $helper = Mage::helper('rakuten');
        $vHelper = Mage::helper('rakuten/variant');
        $productTypeId = $product->getTypeId();
        $rows = array();

        $usedProducts = $vHelper->getUsedProducts($product);

        if (count($usedProducts) <= 0) {
            $helper->syncLog($helper->__('Configurable product does not contain child products. It will not be saved. (%s)', $product->getSku()));
            return $rows;
        }

        if ($rakutenId == null) {
            $rakutenId = '#' . $product->getId();
        }

        $baseRow = $this->_addSimpleProduct($product, $rakutenId);
        $baseRow['mpn'] = null;
        $baseRow['isbn_ean'] = null;
        $baseRow['preis'] = null;
        $baseRow['reduzierter_preis'] = null;
        $baseRow['bezug_reduzierter_preis'] = null;
        $baseRow['grundpreis_einheit'] = null;
        $baseRow['grundpreis_inhalt'] = null;
        $baseRow['lagerbestand'] = null;
        $baseRow['lieferzeit'] = null;
        $mindestbestellmenge = $baseRow['mindestbestellmenge'];
        $baseRow['mindestbestellmenge'] = null;
        $baseRow['produkt_bestellbar'] = null;

        $attributeOptions = Mage::helper('rakuten/variant')->getProductOptions($product);

        $j = 1;
        if ($productTypeId != 'bundle') {
            foreach ($usedProducts as $childProduct) {

                $price = (float)$helper->getMappedValue($product, 'price');
                $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');

                $variantDefinition = array();
                $variantValue = array();
                if ($vHelper->isSimple($product)) {
                    $i = 1;
                    foreach ($attributeOptions as $variant) {
                        $attributeValue = $childProduct->getData('variant_' . $variant['attribute_code']);
                        $variantDefinition[] = $this->_mapVariantDefinition($variant['label']);
                        $variantValue[] = $variant['values'][$attributeValue]['label'];
                        if ($variant['values'][$attributeValue]['is_percent']) {
                            $price *= 1.0 + ((float)$variant['values'][$attributeValue]['pricing_value'] / 100.0);
                        } else {
                            $price += (float)$variant['values'][$attributeValue]['pricing_value'];
                        }
                        if ($specialPrice) {
                            if ($variant['values'][$attributeValue]['is_percent']) {
                                $specialPrice *= 1.0 + ((float)$variant['values'][$attributeValue]['pricing_value'] / 100.0);
                            } else {
                                $specialPrice += (float)$variant['values'][$attributeValue]['pricing_value'];
                            }
                        }
                        $i++;
                    }
                } else {
                    $i = 1;
                    foreach ($attributeOptions as $variant) {
                        $attributeValue = $childProduct->getData($variant['attribute_code']);
                        $variantDefinition[] = $this->_mapVariantDefinition($variant['label']);
                        $variantValue[] = $variant['values'][$attributeValue]['label'];
                        if ($helper->getVariantPriceSimple()) {
                            $price = (float)$helper->getMappedValue($childProduct, 'price');
                        } else {
                            $price += (float)$variant['values'][$attributeValue]['pricing_value'];
                        }
                        if ($specialPrice) {
                            if ($helper->getVariantPriceSimple()) {
                                $specialPrice = (float)$helper->getMappedValue($childProduct, 'price_reduced');
                            } else {
                                $specialPrice += (float)$variant['values'][$attributeValue]['pricing_value'];
                            }
                        }
                        $i++;
                    }
                }


                if ($j == 1) {
                    $baseRow['varianten'] = implode('|', $variantDefinition);
                    $rows[] = $baseRow;
                }


                $price = $this->_getRakutenPrice($product, $price);

                if ($specialPrice) {
                    $specialPrice = $this->_getRakutenPrice($product, $specialPrice);
                    if ($price <= $specialPrice) {
                        $specialPrice = 0;
                    }
                }


                $childRow = array(
                    'id' => null,
                    'variante_zu_id' => $rakutenId,
                    'varianten' => implode('|', $variantValue),
                    'varianten_sortierung' => $j,
                    'artikelnummer' => $helper->getMappedValue($childProduct, 'product_art_no'),
                    'mpn' => $childProduct->getMpn(),
                    'isbn_ean' => $helper->getMappedValue($childProduct, 'isbn'),
                    'produktname' => null,
                    'preis' => $price,
                    'reduzierter_preis' => $specialPrice,
                    'bezug_reduzierter_preis' => 'UVP',
                    'versandgruppe' => null,
                    'grundpreis_einheit' => null,
                    'grundpreis_inhalt' => null,
                    'mwst_klasse' => null,
                    'bestandsverwaltung_aktiv' => null,
                    'lagerbestand' => (int)$vHelper->getStock($product, $childProduct),
                    'lieferzeit' => $this->_getDelivery($product),
                    'mindestbestellmenge' => $mindestbestellmenge,
                    'staffelung' => null,
                    'produkt_bestellbar' => $childProduct->getStatus() == 1 ? 1 : 0,
                    'sichtbar' => null,
                    'connect_deaktiviert' => null,
                    'hersteller' => null,
                    'beschreibung' => null,
                    'deklarationen' => null,
                    'kategorien' => null,
                    'rakuten_kategorie' => null,
                    'cross_selling_titel' => null,
                    'cross_selling_p rodukt_ids' => null,
                    'bild1' => null,
                    'bild2' => null,
                    'bild3' => null,
                    'bild4' => null,
                    'bild5' => null,
                    'bild6' => null,
                    'bild7' => null,
                    'bild8' => null,
                    'bild9' => null,
                    'bild10' => null,
                    'marketing_url' => null,
                    'seo_title' => null,
                    'seo_keywords' => null,
                    'seo_description' => null,
                );

                if ($basePrice = $this->_getBasePrice($product)) {
                    $childRow['grundpreis_einheit'] = $basePrice['unit'];
                    $childRow['grundpreis_inhalt'] = $basePrice['amount'];
                }

                if ($childRow['lagerbestand'] < 0) {
                    $childRow['lagerbestand'] = 0;
                }


                $rows[] = $childRow;


                $j++;
            }
        } else {
            $optionalItemsCombinations = $this->_bundledVariations($attributeOptions, $product);
            foreach ($optionalItemsCombinations as $childVariation) {
                $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');
                $variantDefinition = array();
                $variantValue = array();
                $i = 1;
                foreach ($childVariation['values'] as $variant) {
                    $variantDefinition[] = $childVariation['values'][$i]['type'];
                    $variantValue[] = $childVariation['values'][$i]['value'];
                    $i++;
                }

                if ($j == 1) {
                    $baseRow['varianten'] = implode('|', $variantDefinition);
                    $rows[] = $baseRow;
                }


                $price = $this->_getRakutenPrice($product, $childVariation['price']);

                if ($specialPrice) {
                    $specialPrice = $this->_getRakutenPrice($product, $specialPrice);
                    if ($price <= $specialPrice) {
                        $specialPrice = 0;
                    }
                }


                $childRow = array(
                    'id' => null,
                    'variante_zu_id' => $rakutenId,
                    'varianten' => implode('|', $variantValue),
                    'varianten_sortierung' => $j,
                    'artikelnummer' => $childVariation['sku'],
                    'mpn' => $product->getMpn(),
                    'isbn_ean' => 0,
                    'produktname' => null,
                    'preis' => $price,
                    'reduzierter_preis' => $specialPrice,
                    'bezug_reduzierter_preis' => 'UVP',
                    'versandgruppe' => null,
                    'grundpreis_einheit' => null,
                    'grundpreis_inhalt' => null,
                    'mwst_klasse' => null,
                    'bestandsverwaltung_aktiv' => null,
                    'lagerbestand' => $childVariation['stock'],
                    'lieferzeit' => $this->_getDelivery($product),
                    'mindestbestellmenge' => $mindestbestellmenge,
                    'staffelung' => null,
                    'produkt_bestellbar' => $childVariation['available'],
                    'sichtbar' => null,
                    'connect_deaktiviert' => null,
                    'hersteller' => null,
                    'beschreibung' => null,
                    'deklarationen' => null,
                    'kategorien' => null,
                    'rakuten_kategorie' => null,
                    'cross_selling_titel' => null,
                    'cross_selling_p rodukt_ids' => null,
                    'bild1' => null,
                    'bild2' => null,
                    'bild3' => null,
                    'bild4' => null,
                    'bild5' => null,
                    'bild6' => null,
                    'bild7' => null,
                    'bild8' => null,
                    'bild9' => null,
                    'bild10' => null,
                    'marketing_url' => null,
                    'seo_title' => null,
                    'seo_keywords' => null,
                    'seo_description' => null,
                );

                if ($basePrice = $this->_getBasePrice($product)) {
                    $childRow['grundpreis_einheit'] = $basePrice['unit'];
                    $childRow['grundpreis_inhalt'] = $basePrice['amount'];
                }

                if ($childRow['lagerbestand'] < 0) {
                    $childRow['lagerbestand'] = 0;
                }


                $rows[] = $childRow;


                $j++;
            }
        }

        return $rows;
    }

    public function syncAllStockPriceToRakuten()
    {
        /** @var Mageshops_Rakuten_Helper_Data $helper */
        $helper = Mage::helper('rakuten');
        $helper->syncLog($helper->__('Product synchronization started.'));

        $rakutenCollection = Mage::getModel('rakuten/rakuten_product')->getCollection();

        // Synchronize needed products
        $collection = $this->getProductsToSynchronize();

        $size = $collection->getSize();
        $i = 0;
        foreach ($collection as $product) {
            $rakutenItem = $rakutenCollection->getItemByColumnValue('sku', $product->getSku());
            if ($rakutenItem && $rakutenItem->getRakutenId()) {
                try {
                    $this->syncStockPriceToRakuten($product, $rakutenItem);
                } catch (Exception $e) {
                    $helper->syncLog(
                        $helper->__(
                            'Error while updating product [%s]: %s', $helper->getMappedValue($product, 'product_art_no'), $e->getMessage()
                        )
                    );
                    $helper->syncExceptionLog($e);
                }
            }

            $helper->setState($this->__('Exporting products to Rakuten.'), ++$i / $size);

            if ($message = $helper->checkLowResources()) {
                $helper->setState($message, 0);
                $this->_synchronizationError = true;
                return $this;
            }
        }

        $helper->syncLog($helper->__('Product synchronization finished.'));

        return $this;
    }

    public function syncStockPriceToRakuten(Mage_Catalog_Model_Product $product, $rakutenItem)
    {
        $vHelper = Mage::helper('rakuten/variant');

        if ($vHelper->isVariant($product)) {
            $this->_updateVariantProduct($product, $rakutenItem);
        } else if ($vHelper->isSimple($product)) {
            $this->_updateSimpleProduct($product, $rakutenItem);
        }

        return $this;
    }

    protected function _updateSimpleProduct(Mage_Catalog_Model_Product $product, Mageshops_Rakuten_Model_Rakuten_Product $rakutenItem)
    {
        $helper = Mage::helper('rakuten');
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        $params = $helper->getRequestParams();

        $rakutenRequest = $helper->getEditProductRequestUrl();

        $params['product_art_no'] = $helper->getMappedValue($product, 'product_art_no');
        $params['stock_policy'] = (int)$product->getRakutenStockPolicy();
        $params['visible'] = ($product->getRakutenVisible() === 0) ? 0 : 1;

        $params['price'] = $this->_getRakutenPrice($product, $helper->getMappedValue($product, 'price'));
        $params['stock'] = (int)$stock->getQty();
        $params['available'] = $product->getStatus() == 1 ? 1 : 0;
        if ($product->getRakutenStockPolicy()) {
            if ($stock->getQty() <= 0) {
                $params['available'] = 0;
                $params['stock_policy'] = 1;
            }
        }

        $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');
        if ($specialPrice === false) {
            $specialPrice = $product->getFinalPrice();
        }
        if ($specialPrice) {
            $params['price_reduced'] = $this->_getRakutenPrice($product, $specialPrice);
            if ($params['price'] <= $params['price_reduced']) {
                unset($params['price_reduced']);
            }
        }

        if ($this->_productChanged($params, $rakutenItem)) {
            $request = $helper->callAPI($rakutenRequest, $params);
            $helper->checkAnswer($request, $params, $rakutenRequest);
        }

        return $this;
    }

    /**
     * Update stock management
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param Mageshops_Rakuten_Model_Rakuten_Product $rakutenItem
     * @return \Mageshops_Rakuten_Model_Product
     * @throws Exception
     */
    protected function _updateVariantProduct(Mage_Catalog_Model_Product $product, Mageshops_Rakuten_Model_Rakuten_Product $rakutenItem)
    {

        $helper = Mage::helper('rakuten');
        $helperVariant = Mage::helper('rakuten/variant');

        $usedProducts = $helperVariant->getUsedProducts($product);
        if (count($usedProducts) <= 0) {
            throw new Exception($helper->__('Configurable product does not contain child products. It will not be saved.'));
        }

        $attributeOptions = Mage::helper('rakuten/variant')->getProductOptions($product);

        foreach ($usedProducts as $childProduct) {

            $sku = $helper->getMappedValue($childProduct, 'product_art_no');
            $rakutenRequest = $helper->getEditProductMultiVariantRequestUrl();

            $params = $helper->getRequestParams();
            $params['product_art_no'] = $helper->getMappedValue($product, 'product_art_no');
            $params['variant_art_no'] = $sku;
            $params['available'] = $childProduct->getStatus() == 1 ? 1 : 0;

            $params['stock'] = $helperVariant->getStock($product, $childProduct);

            if ($product->getRakutenStockPolicy()) {
                if ($params['stock'] <= 0) {
                    $params['available'] = 0;
                    $params['stock_policy'] = 1;
                }
            }

            $price = (float)$helper->getMappedValue($product, 'price');
            $specialPrice = (float)$helper->getMappedValue($product, 'price_reduced');

            if ($helperVariant->isSimple($product)) {
                $i = 1;
                foreach ($attributeOptions as $variant) {
                    $attributeValue = $childProduct->getData('variant_' . $variant['attribute_code']);
//                    $params['variation' . $i . '_type'] = $this->_mapVariantDefinition($variant['label']);
//                    $params['variation' . $i . '_value'] = $variant['values'][$attributeValue]['label'];
                    if ($variant['values'][$attributeValue]['is_percent']) {
                        $price *= 1.0 + ((float)$variant['values'][$attributeValue]['pricing_value'] / 100.0);
                    } else {
                        $price += (float)$variant['values'][$attributeValue]['pricing_value'];
                    }
                    if ($specialPrice) {
                        if ($variant['values'][$attributeValue]['is_percent']) {
                            $specialPrice *= 1.0 + ((float)$variant['values'][$attributeValue]['pricing_value'] / 100.0);
                        } else {
                            $specialPrice += (float)$variant['values'][$attributeValue]['pricing_value'];
                        }
                    }
                    $i++;
                }
            } else {
                $i = 1;
                foreach ($attributeOptions as $variant) {
                    $attributeValue = $childProduct->getData($variant['attribute_code']);
//                    $params['variation' . $i . '_type'] = $this->_mapVariantDefinition($variant['label']);
//                    $params['variation' . $i . '_value'] = $variant['values'][$attributeValue]['label'];
                    if ($helper->getVariantPriceSimple()) {
                        $price = (float)$helper->getMappedValue($childProduct, 'price');
                    } else {
                        $price += (float)$variant['values'][$attributeValue]['pricing_value'];
                    }
                    if ($specialPrice) {
                        if ($helper->getVariantPriceSimple()) {
                            $specialPrice = (float)$helper->getMappedValue($childProduct, 'price_reduced');
                        } else {
                            $specialPrice += (float)$variant['values'][$attributeValue]['pricing_value'];
                        }
                    }
                    $i++;
                }
            }


            $params['price'] = $this->_getRakutenPrice($product, $price);

            if ($specialPrice) {
                $params['price_reduced'] = $this->_getRakutenPrice($product, $specialPrice);
                if ($params['price'] <= $params['price_reduced']) {
                    unset($params['price_reduced']);
                }
            }

            if ($this->_variantChanged($params, $rakutenItem)) {
                $request = $helper->callAPI($rakutenRequest, $params);
                $helper->checkAnswer($request, $params, $rakutenRequest);
            }
        }

        return $this;
    }

    /**
     * Checks if associated product is changed
     * 
     * @param array $params
     * @return boolean
     */
    protected function _variantChanged($params)
    {
        $variant = Mage::getModel('rakuten/rakuten_product_variant')->load($params['variant_art_no']);

        if ($variant && $variant->getVariantId()) {

            if ($params['price'] != $variant->getPrice()) {
                return true;
            }
            if ($params['special_price'] != $variant->getPrice()) {
                return true;
            }

            if ($params['stock'] != $variant->getQty()) {
                return true;
            }

            if ($params['available'] != $variant->getStatus()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if simple product has changes
     * 
     * @param array $params
     * @param Object $rakutenItem
     * @return boolean
     */
    protected function _productChanged($params, $rakutenItem)
    {
        if ($params['price'] != $rakutenItem->getPrice()) {
            return true;
        }
        if ($params['special_price'] != $rakutenItem->getPrice()) {
            return true;
        }

        if ($params['stock'] != $rakutenItem->getQty()) {
            return true;
        }

        if ($params['available'] != $rakutenItem->getStatus()) {
            return true;
        }

        return false;
    }

    public function getSynchronizationError()
    {
        return $this->_synchronizationError;
    }

    /**
     * Synchronizes products to Rakuten
     *
     * @param bool $simple Indicates whether to sync simple or configurable/bundled products
     * @return $this
     */
    private function syncProducts($simple)
    {
        $this->_continueSimple = $simple;
        $method = $simple ? 'getSimpleProductsToSynchronize' : 'getVariantProductsToSynchronize';
        $syncMessage = '%s of %s ' . ($simple ? 'simple' : 'variant') . ' products have been processed.';
        /** @var Mageshops_Rakuten_Helper_Data $helper */
        $helper = Mage::helper('rakuten');
        $productSyncHelper = Mage::helper('rakuten/product');

        $size = $productSyncHelper->$method(false, false, false)->getSize();
        if ($size === 0) {
            return $this;
        }

        $page = 10;
        $current = $this->_startFrom;
        while ($current < $size) {
            $collection = $productSyncHelper->$method($page, $current);
            foreach ($collection as $product) {
                try {
                    if ($this->syncProductToRakuten($product)) {
                        $product->setData('rakuten_status', 'exported')
                            ->getResource()
                            ->saveAttribute($product, 'rakuten_status');
                    }
                } catch (Exception $e) {
                    $helper->syncLog($helper->__('Error while updating product [%s]: %s', $helper->getMappedValue($product, 'product_art_no'), $e->getMessage()));
                    $helper->syncExceptionLog($e);
                }

                $current++;
                // If result is false, synchronization process is cancelled
                if ($helper->setState($helper->__($syncMessage, $current, $size), $current / $size) == false) {
                    die;
                }

                $helper->continueIfTooLong($current, $simple);

                if ($message = $helper->checkLowResources()) {
                    $helper->setState($message, 0);
                    $productSyncHelper->_synchronizationError = true;
                    return $this;
                }
            }
        }

        $this->_startFrom = 0;
        return $this;
    }

    /**
     * Checks response from Rakuten
     * @param Mageshops_Rakuten_Model_Rakuten_Request $request
     */
    private function isSuccessfulRequest(Mageshops_Rakuten_Model_Rakuten_Request $request)
    {
        $xmlAnswer = $request->getAnswer();
        $rakutenAnswer = new SimpleXMLElement($xmlAnswer);

        return (int)$rakutenAnswer->success !== -1;
    }

}
