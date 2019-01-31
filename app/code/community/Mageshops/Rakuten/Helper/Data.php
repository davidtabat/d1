<?php

/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */
class Mageshops_Rakuten_Helper_Data extends Mageshops_Market_Helper_Data
{

    protected static $_categoryRequest = 'http://webservice.rakuten.de/merchants/categories/getShopCategories';
    protected static $_productRequest = 'http://webservice.rakuten.de/merchants/products/getProducts';
    protected static $_addProductRequest = 'http://webservice.rakuten.de/merchants/products/addProduct';
    protected static $_addProductVariantDefinitionRequest = 'http://webservice.rakuten.de/merchants/products/addProductVariantDefinition';
    protected static $_addProductVariantRequest = 'http://webservice.rakuten.de/merchants/products/addProductVariant';
    protected static $_addProductMultiVariantRequest = 'http://webservice.rakuten.de/merchants/products/addProductMultiVariant';
    protected static $_addProductToShopCategory = 'http://webservice.rakuten.de/merchants/products/addProductToShopCategory';
    protected static $_editProductRequest = 'http://webservice.rakuten.de/merchants/products/editProduct';
    protected static $_editProductVariantDefinitionRequest = 'http://webservice.rakuten.de/merchants/products/editProductVariantDefinition';
    protected static $_editProductVariantRequest = 'http://webservice.rakuten.de/merchants/products/editProductVariant';
    protected static $_editProductMultiVariantRequest = 'http://webservice.rakuten.de/merchants/products/editProductMultiVariant';
    protected static $_deleteProductRequest = 'http://webservice.rakuten.de/merchants/products/deleteProduct';
    protected static $_addShopCategoryRequest = 'http://webservice.rakuten.de/merchants/categories/addShopCategory';
    protected static $_editShopCategoryRequest = 'http://webservice.rakuten.de/merchants/categories/editShopCategory';
    protected static $_deleteShopCategoryRequest = 'http://webservice.rakuten.de/merchants/categories/deleteShopCategory';
    protected static $_url = array(
        'addProductImage' => 'http://webservice.rakuten.de/merchants/products/addProductImage',
        'deleteProductImage' => 'http://webservice.rakuten.de/merchants/products/deleteProductImage',
        'deleteProductVariant' => 'http://webservice.rakuten.de/merchants/products/deleteProductVariant',
        'getOrders' => 'http://webservice.rakuten.de/merchants/orders/getOrders',
        'setOrderShipped' => 'http://webservice.rakuten.de/merchants/orders/setOrderShipped',
        'setOrderCancelled' => 'http://webservice.rakuten.de/merchants/orders/setOrderCancelled',
    );
    protected static $_rakutenDir = null;
    protected static $_defaultWebsiteId = null;
    protected static $_mappings = null;
    protected static $_variantMappings = null;
    protected static $_csv = null;
    protected static $_resources = null;

    protected static $_startTime = null;
    protected static $_timeLimit = 50;
    protected static $_baseSyncScriptUrl = 'MageshopsRakuten/sync.php';

    public function callAPI($url, $data = null)
    {
        $rakutenRequest = Mage::getModel('rakuten/rakuten_request');

        $rakutenRequest->addData(
            array(
                'url' => $url,
                'params' => Mage::helper('core')->jsonEncode($data),
                'started' => time(),
                'tries' => 1,
                'status' => Mageshops_Rakuten_Model_Rakuten_Request::STATUS_STARTED,
            )
        );

        if (isset($data['external_shop_category_id'])) {
            $rakutenRequest->setElementId($data['external_shop_category_id']);
        }
        if (isset($data['product_art_no'])) {
            $rakutenRequest->setElementId($data['product_art_no']);
        }
        if (isset($data['order_no'])) {
            $rakutenRequest->setElementId($data['order_no']);
        }

        if ($this->syncLogDatabase()) {
            $rakutenRequest->save();
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        $answer = curl_exec($curl);

        curl_close($curl);

        $rakutenRequest->setId($rakutenRequest->getId());
        $rakutenRequest->setFinished(time());

        if ($answer === false) {
            $rakutenRequest->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR);
        } else {
            $rakutenRequest->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_FINISHED);
            $rakutenRequest->setAnswer($answer);
        }

        if ($this->syncLogDatabase()) {
            $rakutenRequest->save();
        }

        return $rakutenRequest;
    }

    public function getCreateCsv()
    {
        return Mage::getStoreConfig('nn_market/rakuten_product/create_csv');
    }

    public function getStockPriceCron()
    {
        if (!Mage::getStoreConfig('nn_market/rakuten_product/create_csv')) {
            return 0;
        }
        return Mage::getStoreConfig('nn_market/rakuten_product/stock_price_cron');
    }

    public function getAPIKey()
    {
        return Mage::getStoreConfig('nn_market/rakuten/api_key');
    }

    public function enableCron()
    {
        return Mage::getStoreConfig('nn_market/rakuten/enable_cron');
    }

    public function getBundledPrefix()
    {
        if (Mage::getStoreConfig('nn_market/rakuten_product/sync_bundle_prefix') == '') {
            return 'BR';
        }
        return Mage::getStoreConfig('nn_market/rakuten_product/sync_bundle_prefix');
    }

    public function enableProductSync()
    {
        return Mage::getStoreConfig('nn_market/rakuten_product/enable_sync');
    }

    public function productsFromCategories()
    {
        return Mage::getStoreConfig('nn_market/rakuten_product/products_from_categories');
    }

    public function enableCategorySync()
    {
        if (Mage::getStoreConfig('nn_market/rakuten_product/create_csv')) {
            return 0;
        }
        return Mage::getStoreConfig('nn_market/rakuten_category/enable_sync');
    }

    public function forceCategoryRecreate()
    {
        return Mage::getStoreConfig('nn_market/rakuten_category/force_recreate');
    }

    public function enableOrderSync()
    {
        return Mage::getStoreConfig('nn_market/rakuten_order/enable_sync');
    }

    public function forceResave()
    {
        return Mage::getStoreConfig('nn_market/rakuten/force_resave');
    }

    public function zeroStock()
    {
        return Mage::getStoreConfig('nn_market/rakuten_product/unvailable_on_zero_stock');
    }

    public function clearForceResave()
    {
        if ($this->forceResave()) {
            $config = Mage::getModel('core/config');
            $config->saveConfig('nn_market/rakuten/force_resave', '0');
            Mage::app()->getStore()->resetConfig();
        }

        return $this;
    }

    public function syncLog($msg)
    {
        if (Mage::getStoreConfig('nn_market/rakuten/log')) {
            Mage::log($msg, null, 'rakuten-sync.log');
        }

        return $this;
    }

    public function syncLogDatabase()
    {
        return Mage::getStoreConfig('nn_market/rakuten/log_database');
    }

    public function syncExceptionLog($e)
    {
        if (Mage::getStoreConfig('nn_market/rakuten/log')) {
            Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'rakuten-exception.log');
        }

        return $this;
    }

    public function getRakutenDir($subPath = false)
    {
        if (!self::$_rakutenDir) {
            self::$_rakutenDir = Mage::getBaseDir('var') . '/nn_market/rakuten';
        }

        $path = self::$_rakutenDir;

        if ($subPath) {
            $path .= '/' . $subPath;
        }

        return $path;
    }

    /**
     * Lock sync process
     * 
     * @return \Mageshops_Rakuten_Helper_Data
     */
    public function lockSync()
    {
        $this->setState($this->__('Synchronization process is running'), 1);

        return $this;
    }

    /**
     * Unlock sync process
     * 
     * @return \Mageshops_Rakuten_Helper_Data
     */
    public function unlockSync()
    {
        $syncStatus = $this->getLastSync();

        $syncStatus->setLocked(0);
        $syncStatus->setPercent(1.00);
        $syncStatus->setMessage($this->__('Synchronization finished'));
        $syncStatus->setTime(time());

        // Save to database
        $syncStatus->save();

        return $this;
    }

    /**
     * Checks if sync process is locked, if is locked return 0, otherwise 1
     * 
     * @return int
     */
    public function isLocked()
    {
        $timeout = floatval(Mage::getStoreConfig('nn_market/rakuten/lock_timeout'));
        if ($timeout > 0) {
            $lockHours = floatval($this->checkSync()) / 60.0;
            if ($lockHours > $timeout) {
                $this->unlockSync();
            }
        }

        $lock = $this->getState();

        $data = $lock->getData();
        if (!empty($data) && $lock->getLocked() == 1) {
            return 1;
        }

        return 0;
    }

    public function checkSync()
    {
        $lock = $this->getState();

        $data = $lock->getData();
        if (!empty($data) && $lock->getLocked() == 1) {
            $diff = time() - $lock->getTime();
            return floor($diff / 60) . 'min ' . $diff % 60 . 'sec';
        }

        return false;
    }

    /**
     * Sets flag for cancelling sync process (0 - unlock, 1 - locked, 2 - cancelled)
     * 
     * @return Mageshops_Rakuten_Helper_Data
     */
    public function clearSync()
    {
        $syncStatus = $this->getLastSync();

        $syncStatus->setLocked(2);
        $syncStatus->setPercent(0);
        $syncStatus->setMessage($this->__('Synchronization is cancelled'));

        // Save to database
        $syncStatus->save();

        $this->syncLog($syncStatus->toString());
        return $this;
    }

    /**
     * Sets current state of sync process
     * 
     * @param string $message
     * @param float $percent
     * @return \Mageshops_Rakuten_Helper_Data
     */
    public function setState($message, $percent)
    {
        $syncStatus = $this->getLastSync();

        // If status is 2, sync is cancelled
        if ($syncStatus->getLocked() == 2) {
            $this->unlockSync();
            return false;
        }

        $syncStatus->setLocked(1);
        $syncStatus->setPercent(number_format($percent, 2));
        $syncStatus->setMessage($message);
        $syncStatus->setTime(time());

        // Save to database
        $syncStatus->save();

        $this->syncLog($syncStatus->toString());
        return true;
    }

    /**
     * Returns last sync state, if state doesn't exist, sets
     * information about it
     * 
     * @return int
     */
    public function getState()
    {
        $syncStatus = $this->getLastSync();

        // If there is no information about locking set data
        $data = $syncStatus->getData();
        if (empty($data) || ($syncStatus->getLocked() == 0 && $syncStatus->getPercent() == 0)) {
            $syncStatus->setLocked(0);
            $syncStatus->setTime(time());
            $syncStatus->setPercent(0.0);
            $syncStatus->setMessage($this->__('No synchronization processes are running'));
            $syncStatus->save();
        }

        // If status is finished (locked = 0 and percent = 1), return message
        if (!is_array($syncStatus) && $syncStatus->getLocked() == 0 && $syncStatus->getPercent() == 1) {
            $syncStatus->setPercent(0);
            $syncStatus->save();
        }

        return $syncStatus;
    }

    /**
     * Start batch synchronization for all categories and products
     *
     * @param bool $continue Indicates whether to continue previous process or to try to start from the beginning.
     * @param bool $continueSimple Indicates whether to sync simple or configurable/bundled products
     * @return void
     */
    public function batchSynchronization($continue = false, $startFrom = 0, $continueSimple = true)
    {
        self::$_startTime = time();

        $this->syncLog("Starting background sync: continue: $continue, startFrom: $startFrom, continueSimple: $continueSimple");
        if ($continue) {
            $this->syncProducts($startFrom, $continueSimple);
        } else {
            if ($this->isLocked()) {
                $this->syncLog($this->__('Other synchronization process is running.'));
                $this->setState($this->__('Other synchronization process is running.'), 0);
                return;
            }

            $this->lockSync();

            // If category sync is enabled in config
            if ($this->enableCategorySync()) { // && !$this->getCreateCsv()) {
                try {
                    Mage::getModel('rakuten/category')->syncAllRakutenCategories()->syncAllToRakuten();
                    $this->setState($this->__('Category synchronization finished successfully.'), 1);
                } catch (Exception $e) {
                    $this->syncExceptionLog($e);
                    $this->setState($this->__('Error occurred during synchronization: %s', $e->getMessage()), 0);
                }
            }

            // If product sync is enabled in config
            if ($this->enableProductSync()) {
                $this->syncProducts();
            }
        }

        $this->unlockSync();
    }

    /**
     * @param int $startFrom
     * @param bool|true $continueSimple
     */
    private function syncProducts($startFrom = 0, $continueSimple = true)
    {
        try {
            /** @var Mageshops_Rakuten_Model_Product $model */
            $model = Mage::getModel('rakuten/product');
            $model->setOffset($startFrom, $continueSimple);

            $error = $model->syncAllToRakuten()->getSynchronizationError();

            if ($error == false) {
                $this->setState($this->__('Synchronization finished successfully.'), 1);
            }
        } catch (Exception $e) {
            $this->syncExceptionLog($e);
            $this->setState($this->__('Error occurred during synchronization: %s', $e->getMessage()), 0);
        }
    }

    /**
     * Gets last sync from database
     * 
     * @return Mageshops_Rakuten_Model_Rakuten_Synchronization
     */
    public function getLastSync()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $tableName = $setup->getTable('rakuten/rakuten_synchronization');

        $db = Mage::getModel('core/resource')->getConnection('core_read');
        $result = $db->raw_fetchRow("SELECT MAX(`entity_id`) as LastID FROM `{$tableName}`");

        return Mage::getModel('rakuten/rakuten_synchronization')->load($result['LastID']);
    }

    public function getWebsite()
    {
        if (self::$_defaultWebsiteId === null) {
            foreach (Mage::app()->getWebsites() as $_website) {
                if ($_website->getIsDefault()) {
                    self::$_defaultWebsiteId = $_website->getId();
                }
            }
        }
        return self::$_defaultWebsiteId;
    }

    public function getRakutenOrderStore()
    {
        return Mage::getStoreConfig('nn_market/rakuten_order/rakuten_store');
    }

    public function getCategory()
    {
        return Mage::getStoreConfig('nn_market/rakuten/category');
    }

    public function getAttributeSet()
    {
        return Mage::getStoreConfig('nn_market/rakuten/attributeset');
    }

    public function getVariantPriceSimple()
    {
        return Mage::getStoreConfig('nn_market/rakuten_product/variant_price');
    }

    public function getCategoryRequestUrl()
    {
        return self::$_categoryRequest;
    }

    public function getProductRequestUrl()
    {
        return self::$_productRequest;
    }

    public function getAddProductRequestUrl()
    {
        return self::$_addProductRequest;
    }

    public function getEditProductRequestUrl()
    {
        return self::$_editProductRequest;
    }

    public function getDeleteProductRequestUrl()
    {
        return self::$_deleteProductRequest;
    }

    public function getAddProductToShopCategoryRequestUrl()
    {
        return self::$_addProductToShopCategory;
    }

    public function getAddProductVariantDefinitionRequestUrl()
    {
        return self::$_addProductVariantDefinitionRequest;
    }

    public function getEditProductVariantDefinitionRequestUrl()
    {
        return self::$_editProductVariantDefinitionRequest;
    }

    public function getAddProductMultiVariantRequestUrl()
    {
        return self::$_addProductMultiVariantRequest;
    }

    public function getEditProductMultiVariantRequestUrl()
    {
        return self::$_editProductMultiVariantRequest;
    }

    public function getAddProductVariantRequestUrl()
    {
        return self::$_addProductVariantRequest;
    }

    public function getEditProductVariantRequestUrl()
    {
        return self::$_editProductVariantRequest;
    }

    public function getAddShopCategoryRequestUrl()
    {
        return self::$_addShopCategoryRequest;
    }

    public function getEditShopCategoryRequestUrl()
    {
        return self::$_editShopCategoryRequest;
    }

    public function getDeleteShopCategoryRequestUrl()
    {
        return self::$_deleteShopCategoryRequest;
    }

    public function getUrl($urlId)
    {
        if (empty(self::$_url[$urlId])) {
            Mage::throwException($this->__('Can not find API URL for "%s"', $urlId));
        }
        return self::$_url[$urlId];
    }

    public function getRequestParams()
    {
        return array(
            'key' => $this->getAPIKey(),
            'format' => 'xml',
        );
    }

    public function checkAnswer($request, $params = null, $requestUrl = null)
    {
        $xmlAnswer = $request->getAnswer();
        $rakuten = new SimpleXMLElement($xmlAnswer);

        $success = (int)$rakuten->success;

        if ($success < 0) {

            if ($this->syncLogDatabase()) {
                $request->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR)->save();
            }

            $msg = '';
            foreach ($rakuten->errors->error as $error) {
                $msg .= '<li>' . $error->code . ' - ' . $error->message . '</li>';
            }

            if (!empty($msg)) {

                $this->syncLog($params);
                $this->syncLog($requestUrl);
                $this->syncLog($xmlAnswer);

                $msg = '<ul>' . $msg . '</ul>';

                unset($rakuten);
                throw new Exception($msg);
            }
        } elseif ($success == 0) {
            unset($rakuten);
            $this->syncLog($xmlAnswer);
            throw new Exception($this->__('No more info is available'));
        }

        return $rakuten;
    }

    public function convert($str)
    {
        return iconv('ISO-8859-1', 'UTF-8', $str);
    }
    
    /**
     * Update Rakuten categories from API to csv file located in var/nn_market/rakuten
     * 
     * @throws Exception
     */
    public function updateRakutenCategories()
    {
        $file = $this->getRakutenDir('kategorien.csv');

        $dir = dirname($file);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new Exception($this->__('There was an error creating dir for storage. Please check your permissions.'));
            }
        }
        $cats = file_get_contents('http://api.rakuten.de/categories/csv/');
        if ($cats === false) {
            throw new Exception($this->__('There was an error loading default Rakuten categories. Please try to reload page.'));
        }
        file_put_contents($file, $cats);
    }

    public function getDefaultRakutenCategories()
    {
        $file = $this->getRakutenDir('kategorien.csv');
        if (!file_exists($file) || filesize($file) == 0) {
            $this->updateRakutenCategories();
        }

        $row = 1;
        $kategorien = new Varien_Data_Collection();

        if (($handle = fopen($file, 'r')) !== false) {

            while (($data = fgetcsv($handle, 4096, ';')) !== false) {
                if ($row > 1) {
                    $item = new Varien_Object();
                    $item->setId($data[0]);

                    unset($data[0]);
                    unset($data[1]);

                    $data = array_map(array($this, 'convert'), $data);

                    $item->setKategorien($data);

                    $kategorien->addItem($item);
                } else {
                    if (count($data) == 0) {
                        unlink($file);
                        throw new Exception($this->__('Data seem to be corrupted, please reload page.'));
                    }
                }

                ++$row;
            }
            fclose($handle);
        }

        return $kategorien;
    }

    public function getRakutenTax($taxClassId)
    {
        $taxes = array(
            Mage::getStoreConfig('nn_market/rakuten/tax_1') => 1,
            Mage::getStoreConfig('nn_market/rakuten/tax_2') => 2,
            Mage::getStoreConfig('nn_market/rakuten/tax_3') => 3,
            Mage::getStoreConfig('nn_market/rakuten/tax_4') => 4,
            Mage::getStoreConfig('nn_market/rakuten/tax_10') => 10,
            Mage::getStoreConfig('nn_market/rakuten/tax_11') => 11,
            Mage::getStoreConfig('nn_market/rakuten/tax_12') => 12,
        );

        if (isset($taxes[$taxClassId])) {
            return $taxes[$taxClassId];
        }

        return false;
    }

    public function getTaxPercentFromRakutenIdx($idx)
    {
        $taxTable = array(
            '1' => '19',
            '2' => '7',
            '3' => '0',
            '4' => '10.7',
            '10' => '10',
            '11' => '12',
            '12' => '20',
        );

        if (isset($taxTable[$idx])) {
            return $taxTable[$idx];
        }

        return 0;
    }

    /**
     * This method prepares product name for export to rakuten attribute stripping tags
     * and truncating it to max count of characters
     *
     * @param Mage_Catalog_Model_Product $product   Product to take name
     * @param int                        $maxLength Max chars allowed before truncation
     * @param string                     $field     Field name
     * @return string
     */
    public function prepareName(Mage_Catalog_Model_Product $product, $maxLength = 100, $field = 'name')
    {
        $name = $this->getMappedValue($product, $field);
        $name = strip_tags($name);
        if (strlen($name) > $maxLength) {
            $name = substr($name, 0, $maxLength - 3) . '...';
        }
        return $name;
    }

    public function getProductMappings()
    {
        if (!self::$_mappings) {
            self::$_mappings = array(
                'baseprice_unit' => '',
                'baseprice_volume' => '',
                'default_image' => 'image',
                'description' => 'description',
                'ean' => '',
                'isbn' => '',
                'name' => 'name',
                'price' => 'price',
                'price_reduced' => 'special_price',
                'producer' => 'manufacturer',
                'product_art_no' => 'sku',
                'meta_title' => 'meta_title',
                'meta_keyword' => 'meta_keyword',
                'meta_description' => 'meta_description',
            );

            foreach (self::$_mappings as $code => $mapping) {
                $saved = Mage::getStoreConfig('nn_market/rakuten_product_mapping/' . $code);
                if (!empty($saved) || $saved === '') {
                    self::$_mappings[$code] = $saved;
                }
            }
        }

        return self::$_mappings;
    }

    public function getMappedValue($product, $code)
    {
        $mappings = $this->getProductMappings();
        if ($mappings[$code] === '') {
            return false;
        }

        if (!empty($mappings[$code])) {
            $code = $mappings[$code];
        }

        if (is_a($product, 'Mage_Catalog_Model_Product')) {

            $attribute = $product->getResource()->getAttribute($code);

            if ($attribute->getFrontendInput() == 'select') {

                $value = (string)$attribute->getFrontend()->getValue($product);
                if (strtolower($value) == 'no') {
                    $value = '';
                }
            } else {
                $getter = 'get' . $this->underscore2Camelcase($code);
                
                if ($attribute->getIsHtmlAllowedOnFront() == true) {
                    $value = Mage::helper('catalog/output')->productAttribute($product, $product->$getter(), $code);
                } else {
                    $value = $product->$getter();
                }
            }
        } else {

            $value = $product->getData($code);
        }

        return $value;
    }

    public function getVariantDefinitionMappings()
    {
        if (!self::$_variantMappings) {
            self::$_variantMappings = Mage::getStoreConfig('nn_market/rakuten_variant_mappings');
            if (self::$_variantMappings === null) {
                self::$_variantMappings = array();
            }
        }

        return self::$_variantMappings;
    }

    public function addVariantDefinitionMapping($value)
    {
        $code = $this->mapCode($value);
        Mage::getModel('core/config')->saveConfig('nn_market/rakuten_variant_mappings/' . $code, '');
        Mage::app()->getStore()->resetConfig();

        return $this;
    }

    public function mapCode($value)
    {
        $value = preg_replace('/\s+/', '_', $value);
        $value = str_replace('-', '_', $value);
        $value = strtolower($value);

        return $value;
    }

    protected function underscore2Camelcase($str)
    {
        $words = explode('_', strtolower($str));
        $return = '';
        foreach ($words as $word) {
            $return .= ucfirst(trim($word));
        }

        return $return;
    }

    protected function _getMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        $number = (int)$memoryLimit;
        switch (strtoupper(substr($memoryLimit, -1))) {
            case 'K':
                $memoryLimit = $number * 1024;
                break;
            case 'M':
                $memoryLimit = $number * pow(1024, 2);
                break;
            case 'G':
                $memoryLimit = $number * pow(1024, 3);
                break;
            default:
                $memoryLimit = $number;
                break;
        }

        return (int)$memoryLimit;
    }

    /**
     * Checks if current process is running for a long time and if so, starts new process and ends current.
     *
     * @param int $productCount Current progress
     * @param bool $continueSimple Indicates whether currently synchronizing simple or complex products.
     */
    public function continueIfTooLong($productCount = 0, $continueSimple)
    {
        if (time() - self::$_startTime > self::$_timeLimit) {
            $this->startBackgroundProcess(true, $productCount, $continueSimple);
            die;
        }
    }

    public function checkLowResources()
    {
        if (self::$_resources === null) {
            self::$_resources['memory'] = $this->_getMemoryLimit();
            self::$_resources['time'] = ini_get('max_execution_time');
        }

        if (self::$_resources['memory'] < 0 || self::$_resources['memory'] - memory_get_usage(true) < 4 * 1024 * 1024) {
            return $this->__('Process is running out of memory. Stopping.');
        }

        if (!function_exists('getrusage')) {
            return false;
        }

        // this does not work the same on all systems and sometimes gives false results causing sync to stop.
        // disabled because there's a mechanism to continue sync after some time ($_timeLimit variable)
        /*
        $scriptTime = getrusage();
        if (self::$_resources['time'] > 0 && self::$_resources['time'] - 15 < $scriptTime['ru_utime.tv_sec']) {
            return $this->__('Process is reaching system execution time limit. Stopping.');
        }
        */

        return false;
    }

    public function isProductNew($product)
    {
        $newFromDate = $product->getNewsFromDate();
        $newToDate = $product->getNewsToDate();
        $now = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        if (($newFromDate < $now && $newFromDate != NULL) && ($newToDate > $now || $newToDate == '')) {
            return true;
        }
        return false;
    }
    
    /**
     * Method for sync reset, after something went wrong in sync process
     */
    public function resetSynchronizationState()
    {
        $syncStatus = $this->getLastSync();

        // If idle 10 minutes, reset state
        $isIdle = (time() - 600) > $syncStatus->getTime();

        if ($isIdle) {
            $syncStatus->setLocked(0);
            $syncStatus->setPercent(1.00);
            $syncStatus->setMessage($this->__('Synchronization reset'));
            $syncStatus->setTime(time());
        }

        // Save to database
        $syncStatus->save();
    }


    /**
     * Sets background process or continues previous one.
     *
     * @param bool $continue Indicates whether to continue previous process or to try to start from the beginning.
     * @param int $startFrom Last synched item
     * @param bool $continueSimple Indicates whether currently synching simple or complex products
     */
    public function startBackgroundProcess($continue = false, $startFrom = 0, $continueSimple = true)
    {
        $url = self::$_baseSyncScriptUrl;
        if ($continue) {
            $url = self::$_baseSyncScriptUrl . '?continue=true&startFrom=' . $startFrom . '&simple=' . $continueSimple;
        }

        // Set background process
        if (isset($_SERVER['HTTPS'])) {
            $url = $_SERVER['SERVER_ADDR'] . DIRECTORY_SEPARATOR . $url;
        } else {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $url;
        }

        $this->syncLog("Continuing in another process: $url");
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        curl_exec($ch);
        curl_close($ch);
    }
}
