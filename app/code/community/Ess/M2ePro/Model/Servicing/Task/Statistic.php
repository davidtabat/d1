<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Statistic extends Ess_M2ePro_Model_Servicing_Task
{
    const RUN_INTERVAL = 604800; // 1 week

    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'statistic';
    }

    //########################################

    /**
     * @return bool
     */
    public function isAllowed()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();

        $lastRun = $cacheConfig->getGroupValue('/servicing/statistic/', 'last_run');

        if ($this->getInitiator() === Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER ||
            $lastRun === null ||
            Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastRun) + self::RUN_INTERVAL) {
            $cacheConfig->setGroupValue(
                '/servicing/statistic/', 'last_run',
                Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            return true;
        }

        return false;
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array(
            'statistics' => array(
                'server'    => $this->getServerRequestPart(),
                'magento'   => $this->getMagentoRequestPart(),
                'extension' => $this->getExtensionRequestPart(),
            ),
        );
    }

    public function processResponseData(array $data)
    {
        return null;
    }

    //########################################

    protected function fillUpDataByMethod(array &$data, $method)
    {
        try {
            if (is_callable(array($this, $method))) {
                $this->$method($data);
            }
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
        }
    }

    //########################################

    protected function getServerRequestPart()
    {
        $data = array();

        $this->fillUpDataByMethod($data, 'appendServerSystemInfo');
        $this->fillUpDataByMethod($data, 'appendServerPhpInfo');
        $this->fillUpDataByMethod($data, 'appendServerMysqlInfo');

        return $data;
    }

    // ---------------------------------------

    protected function appendServerSystemInfo(&$data)
    {
        $data['name'] = Mage::helper('M2ePro/Client')->getSystem();
    }

    protected function appendServerPhpInfo(&$data)
    {
        $phpSettings = Mage::helper('M2ePro/Client')->getPhpSettings();

        $data['php']['version']            = Mage::helper('M2ePro/Client')->getPhpVersion();
        $data['php']['server_api']         = Mage::helper('M2ePro/Client')->getPhpApiName();
        $data['php']['memory_limit']       = $phpSettings['memory_limit'];
        $data['php']['max_execution_time'] = $phpSettings['max_execution_time'];
    }

    protected function appendServerMysqlInfo(&$data)
    {
        $mySqlSettings = Mage::helper('M2ePro/Client')->getMysqlSettings();

        $data['mysql']['version']         = Mage::helper('M2ePro/Client')->getMysqlVersion();
        $data['mysql']['api']             = Mage::helper('M2ePro/Client')->getMysqlApiName();
        $data['mysql']['database_name']   = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $data['mysql']['table_prefix']    = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $data['mysql']['connect_timeout'] = $mySqlSettings['connect_timeout'];
        $data['mysql']['wait_timeout']    = $mySqlSettings['wait_timeout'];
    }

    //########################################

    protected function getMagentoRequestPart()
    {
        $data = array();

        $this->fillUpDataByMethod($data, 'appendMagentoSystemInfo');

        $this->fillUpDataByMethod($data, 'appendMagentoModulesInfo');
        $this->fillUpDataByMethod($data, 'appendMagentoStoresInfo');

        $this->fillUpDataByMethod($data, 'appendMagentoAttributesInfo');
        $this->fillUpDataByMethod($data, 'appendMagentoProductsInfo');
        $this->fillUpDataByMethod($data, 'appendMagentoOrdersInfo');

        return $data;
    }

    // ---------------------------------------

    protected function appendMagentoSystemInfo(&$data)
    {
        $data['info']['edition'] = Mage::helper('M2ePro/Magento')->getEditionName();
        $data['info']['version'] = Mage::helper('M2ePro/Magento')->getVersion();

        $data['settings']['compilation']   = defined('COMPILER_INCLUDE_PATH');
        $data['settings']['cache_backend'] = Mage::helper('M2ePro/Client_Cache')->getBackend();
        $data['settings']['secret_key']    = Mage::helper('M2ePro/Magento')->isSecretKeyToUrl();
    }

    protected function appendMagentoModulesInfo(&$data)
    {
        foreach (Mage::getConfig()->getNode('modules')->asArray() as $module => $moduleData) {
            $data['modules'][$module] = array(
                'name'    => $module,
                'version' => isset($moduleData['version']) ? $moduleData['version'] : null,
                'status'  => (isset($moduleData['active']) && $moduleData['active'] === 'true')
            );
        }
    }

    protected function appendMagentoStoresInfo(&$data)
    {
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                foreach ($group->getStores() as $store) {
                    $data['stores'][$website->getName()][$group->getName()][] = $store->getName();
                }
            }
        }
    }

    protected function appendMagentoAttributesInfo(&$data)
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')->addVisibleFilter();
        $data['attributes']['amount'] = $collection->getSize();

        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityTypeId);
        $data['attribute_sets']['amount'] = $collection->getSize();

        $collection = Mage::getResourceModel('catalog/category_collection');
        $data['categories']['amount'] = $collection->getSize();
    }

    protected function appendMagentoProductsInfo(&$data)
    {
        $resource = Mage::getSingleton('core/resource');

        // Count of Products
        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('catalog_product_entity'),
                array(
                         'count' => new Zend_Db_Expr('COUNT(*)'),
                         'type'  => 'type_id'
                  )
            )
              ->group('type_id')
              ->query();

        $data['products']['total'] = 0;

        while ($row = $queryStmt->fetch()) {
            $data['products']['total'] += (int)$row['count'];
            $data['products']['types'][$row['type']]['amount'] = (int)$row['count'];
        }

        // ---------------------------------------

        // QTY / Stock Availability {simple}
        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                array(
                      'stock_item' => Mage::helper('M2ePro/Module_Database_Structure')
                          ->getTableNameWithPrefix('cataloginventory_stock_item')
                  ),
                array(
                      'min_qty'     => new Zend_Db_Expr('MIN(stock_item.qty)'),
                      'max_qty'     => new Zend_Db_Expr('MAX(stock_item.qty)'),
                      'avg_qty'     => new Zend_Db_Expr('AVG(stock_item.qty)'),
                      'count'       => new Zend_Db_Expr('COUNT(*)'),
                      'is_in_stock' => 'stock_item.is_in_stock'
                  )
            )
            ->joinLeft(
                array(
                      'catalog_product' => Mage::helper('M2ePro/Module_Database_Structure')
                          ->getTableNameWithPrefix('catalog_product_entity')
                  ),
                'stock_item.product_id = catalog_product.entity_id',
                array()
            )
              ->where('catalog_product.type_id = ?', 'simple')
              ->group('is_in_stock')
              ->query();

        $data['products']['qty']['min'] = 0;
        $data['products']['qty']['max'] = 0;
        $data['products']['qty']['avg'] = 0;

        $data['products']['stock_availability']['min'] = 0;
        $data['products']['stock_availability']['out'] = 0;

        while ($row = $queryStmt->fetch()) {
            $data['products']['qty']['min'] += (int)$row['min_qty'];
            $data['products']['qty']['max'] += (int)$row['max_qty'];
            $data['products']['qty']['avg'] += (int)$row['avg_qty'];

            (int)$row['is_in_stock'] == 1 ? $data['products']['stock_availability']['min'] += (int)$row['count']
                                          : $data['products']['stock_availability']['out'] += (int)$row['count'];
        }

        // Prices {simple}
        $result = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                      ->getTableNameWithPrefix('catalog/product_index_price'),
                array(
                     'min_price' => new Zend_Db_Expr('MIN(price)'),
                     'max_price' => new Zend_Db_Expr('MAX(price)'),
                     'avg_price' => new Zend_Db_Expr('AVG(price)')
                 )
            )
              ->where('website_id = ?', Mage::app()->getWebsite(true)->getId())
              ->query()
              ->fetch();

        $data['products']['price']['min'] = round($result['min_price'], 2);
        $data['products']['price']['max'] = round($result['max_price'], 2);
        $data['products']['price']['avg'] = round($result['avg_price'], 2);
        // ---------------------------------------
    }

    protected function appendMagentoOrdersInfo(&$data)
    {
        $resource = Mage::getSingleton('core/resource');

        // Count of Orders
        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('sales_flat_order'),
                array(
                     'count'  => new Zend_Db_Expr('COUNT(*)'),
                     'status' => 'status'
                 )
            )
              ->group('status')
              ->query();

        $data['orders']['total'] = 0;

        while ($row = $queryStmt->fetch()) {
            $data['orders']['total'] += (int)$row['count'];
            $data['orders']['statuses'][$row['status']]['amount'] = (int)$row['count'];
        }

        // ---------------------------------------

        $collection = Mage::getResourceModel('sales/order_invoice_collection');
        $data['invoices']['amount'] = $collection->getSize();

        $collection = Mage::getResourceModel('sales/order_shipment_collection');
        $data['shipments']['amount'] = $collection->getSize();

        $collection = Mage::getResourceModel('sales/order_creditmemo_collection');
        $data['credit_memos']['amount'] = $collection->getSize();

        $collection = Mage::getResourceModel('sales/order_payment_transaction_collection');
        $data['transactions']['amount'] = $collection->getSize();
    }

    //########################################

    protected function getExtensionRequestPart()
    {
        $data = array();

        $this->fillUpDataByMethod($data, 'appendExtensionSystemInfo');
        $this->fillUpDataByMethod($data, 'appendExtensionM2eProUpdaterModuleInfo');

        $this->fillUpDataByMethod($data, 'appendExtensionTablesInfo');
        $this->fillUpDataByMethod($data, 'appendExtensionSettingsInfo');

        $this->fillUpDataByMethod($data, 'appendExtensionMarketplacesInfo');
        $this->fillUpDataByMethod($data, 'appendExtensionAccountsInfo');

        $this->fillUpDataByMethod($data, 'appendExtensionListingsInfo');
        $this->fillUpDataByMethod($data, 'appendExtensionListingsProductsInfo');
        $this->fillUpDataByMethod($data, 'appendExtensionListingsOtherInfo');

        $this->fillUpDataByMethod($data, 'appendExtensionPoliciesInfo');
        $this->fillUpDataByMethod($data, 'appendExtensionOrdersInfo');

        $this->fillUpDataByMethod($data, 'appendExtensionLogsInfo');

        return $data;
    }

    // ---------------------------------------

    protected function appendExtensionSystemInfo(&$data)
    {
        $data['info']['version'] = Mage::helper('M2ePro/Module')->getVersion();
    }

    protected function appendExtensionM2eProUpdaterModuleInfo(&$data)
    {
        $updaterModule = (array)Mage::getConfig()->getModuleConfig('Ess_M2eProUpdater');

        $updaterData['installed'] = (int)$updaterModule;

        if ($updaterData['installed']) {
            $updaterData['status'] = (int)json_decode($updaterModule['active']);
            $updaterData['version'] = empty($updaterModule['version']) ? '' : $updaterModule['version'];
        }

        $data['info']['m2eproupdater_module'] = $updaterData;
    }

    protected function appendExtensionTablesInfo(&$data)
    {
        $helper = Mage::helper('M2ePro/Module_Database_Structure');
        $data['info']['tables'] = array();

        foreach ($helper->getMySqlTables() as $tableName) {
            $data['info']['tables'][$tableName] = array(
                'size'   => $helper->getDataLength($tableName),
                'amount' => $helper->getCountOfRecords($tableName),
            );
        }
    }

    protected function appendExtensionSettingsInfo(&$data)
    {
        $settings = array();
        $conf = Mage::helper('M2ePro/Module')->getConfig();

        $settings['products_show_thumbnails']    = $conf->getGroupValue('/view/', 'show_products_thumbnails');
        $settings['block_notices_show']          = $conf->getGroupValue('/view/', 'show_block_notices');
        $settings['manage_stock_backorders']     = $conf->getGroupValue('/product/force_qty/', 'mode');
        $settings['manage_stock_backorders_qty'] = $conf->getGroupValue('/product/force_qty/', 'value');
        $settings['price_convert_mode']          = $conf->getGroupValue('/magento/attribute/', 'price_type_converting');
        $settings['inspector_mode']              = $conf->getGroupValue('/listing/product/inspector/', 'mode');

        $settings['logs_clearing'] = array();
        $settings['channels']      = array();

        $logsTypes = array(
            Ess_M2ePro_Model_Log_Clearing::LOG_LISTINGS,
            Ess_M2ePro_Model_Log_Clearing::LOG_OTHER_LISTINGS,
            Ess_M2ePro_Model_Log_Clearing::LOG_SYNCHRONIZATIONS,
            Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS
        );
        foreach ($logsTypes as $logType) {
            $settings['logs_clearing'][$logType] = array(
                'mode' => $conf->getGroupValue('/logs/clearing/'.$logType.'/', 'mode'),
                'days' => $conf->getGroupValue('/logs/clearing/'.$logType.'/', 'days')
            );
        }

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $settings['channels'][$component]['enabled'] = $conf->getGroupValue('/component/'.$component.'/', 'mode');
        }

        $configData = $conf->getCollection()->toArray();
        $settings['config'] = $configData['items'];

        $data['settings'] = $settings;
    }

    protected function appendExtensionMarketplacesInfo(&$data)
    {
        $data['marketplaces'] = array();

        $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        /** @var Ess_M2ePro_Model_Marketplace $item */
        foreach ($collection->getItems() as $item) {
            $data['marketplaces'][$item->getComponentMode()][$item->getNativeId()] = $item->getTitle();
        }
    }

    protected function appendExtensionAccountsInfo(&$data)
    {
        $data['accounts'] = array();

        $collection = Mage::getModel('M2ePro/Account')->getCollection();

        /** @var Ess_M2ePro_Model_Account $item */
        foreach ($collection->getItems() as $item) {
            $tempInfo = array();
            $childItem = $item->getChildObject();

            if ($item->isComponentModeEbay()) {

                /** @var Ess_M2ePro_Model_Ebay_Account $childItem */
                $tempInfo['is_production'] = $childItem->isModeProduction();
                $tempInfo['feedbacks_synch'] = $childItem->isFeedbacksReceive();
            }

            if ($item->isComponentModeAmazon()) {

                /** @var Ess_M2ePro_Model_Amazon_Account $childItem */
                $tempInfo['marketplace'] = $childItem->getMarketplace()->getTitle();
            }

            $tempInfo['other_listings_synch'] = $childItem->isOtherListingsSynchronizationEnabled();

            $data['accounts'][$item->getComponentMode()][$item->getTitle()] = $tempInfo;
        }
    }

    protected function appendExtensionListingsInfo(&$data)
    {
        $resource = Mage::getSingleton('core/resource');

        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_listing'),
                array(
                         'count'          => new Zend_Db_Expr('COUNT(*)'),
                         'component'      => 'component_mode',
                         'marketplace_id' => 'marketplace_id',
                         'account_id'     => 'account_id',
                         'store_id'       => 'store_id'
                  )
            )
            ->group(
                array(
                          'component_mode',
                          'marketplace_id',
                          'account_id',
                          'store_id'
                )
            )
              ->query();

        $data['listings']['total'] = 0;

        $availableComponents = Mage::helper('M2ePro/Component')->getComponents();
        foreach ($availableComponents as $nick) {
            $data['listings'][$nick]['total'] = 0;
        }

        $helper = Mage::helper('M2ePro/Component');
        while ($row = $queryStmt->fetch()) {
            if (!in_array($row['component'], $availableComponents)) {
                continue;
            }

            $data['listings']['total'] += (int)$row['count'];
            $data['listings'][$row['component']]['total'] += (int)$row['count'];

            $markTitle = $helper->getCachedUnknownObject('Marketplace', $row['marketplace_id'])
                                ->getTitle();

            $accountTitle = $helper->getCachedUnknownObject('Account', $row['account_id'])
                                   ->getTitle();

            $storePath = Mage::helper('M2ePro/Magento_Store')->getStorePath($row['store_id']);

            if (!isset($data['listings'][$row['component']]['marketplaces'][$markTitle])) {
                $data['listings'][$row['component']]['marketplaces'][$markTitle] = 0;
            }

            if (!isset($data['listings'][$row['component']]['accounts'][$accountTitle])) {
                $data['listings'][$row['component']]['accounts'][$accountTitle] = 0;
            }

            if (!isset($data['listings']['stores'][$storePath])) {
                $data['listings']['stores'][$storePath] = 0;
            }

            $data['listings'][$row['component']]['marketplaces'][$markTitle] += (int)$row['count'];
            $data['listings'][$row['component']]['accounts'][$accountTitle] += (int)$row['count'];
            $data['listings']['stores'][$storePath] += (int)$row['count'];
        }
    }

    protected function appendExtensionListingsProductsInfo(&$data)
    {
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $queryStmt = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_listing'),
                array(
                    'component'      => 'component_mode',
                    'marketplace_id' => 'marketplace_id',
                    'account_id'     => 'account_id',
                    'products_count' => 'products_total_count'
                )
            )
            ->query();

        $productTypes = array(
            Ess_M2ePro_Model_Magento_Product::TYPE_SIMPLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_CONFIGURABLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_BUNDLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_GROUPED,
            Ess_M2ePro_Model_Magento_Product::TYPE_DOWNLOADABLE,
            Ess_M2ePro_Model_Magento_Product::TYPE_VIRTUAL
        );

        $data['listings_products']['total'] = 0;

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $componentName) {
            $data['listings_products'][$componentName]['total'] = 0;

            foreach ($productTypes as $productType) {
                $select = Mage::getSingleton('core/resource')->getConnection('core_read')
                    ->select()
                    ->from(
                        array(
                            'lp' => $structureHelper->getTableNameWithPrefix('m2epro_listing_product')
                        ),
                        array('count(*)')
                    )
                    ->where('component_mode = ?', $componentName)
                    ->joinLeft(
                        array(
                            'cpe' => $structureHelper->getTableNameWithPrefix('catalog_product_entity')
                        ),
                        'lp.product_id = cpe.entity_id',
                        array()
                    )
                    ->where('type_id = ?', $productType);

                if ($componentName === Ess_M2ePro_Helper_Component_Amazon::NICK ||
                    $componentName === Ess_M2ePro_Helper_Component_Walmart::NICK) {
                    $tableComponentLp = $structureHelper->getTableNameWithPrefix(
                        'm2epro_'.$componentName.'_listing_product'
                    );

                    $select->joinLeft(
                        array('clp' => $tableComponentLp),
                        'lp.id = clp.listing_product_id',
                        array()
                    )
                    ->where('variation_parent_id IS NULL');
                }

                $data['listings_products'][$componentName]['products']['type'][$productType] = array(
                    'amount' => Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($select)
                );
            }
        }

        foreach ($productTypes as $productType) {
            $amount = 0;
            foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
                $amount += $data['listings_products'][$component]['products']['type'][$productType]['amount'];
            }

            $data['listings_products']['products']['type'][$productType] = array(
                'amount' => $amount
            );
        }

        while ($row = $queryStmt->fetch()) {
            if (!in_array($row['component'], Mage::helper('M2ePro/Component')->getComponents())) {
                continue;
            }

            $data['listings_products']['total'] += (int)$row['products_count'];
            $data['listings_products'][$row['component']]['total'] += (int)$row['products_count'];

            $markTitle = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
                'Marketplace', $row['marketplace_id']
            )->getTitle();

            $accountTitle = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
                'Account', $row['account_id']
            )->getTitle();

            if (!isset($data['listings_products'][$row['component']]['marketplaces'][$markTitle])) {
                $data['listings_products'][$row['component']]['marketplaces'][$markTitle] = 0;
            }

            if (!isset($data['listings_products'][$row['component']]['accounts'][$accountTitle])) {
                $data['listings_products'][$row['component']]['accounts'][$accountTitle] = 0;
            }

            $data['listings_products'][$row['component']]['marketplaces'][$markTitle] += (int)$row['products_count'];
            $data['listings_products'][$row['component']]['accounts'][$accountTitle] += (int)$row['products_count'];
        }
    }

    protected function appendExtensionListingsOtherInfo(&$data)
    {
        $resource = Mage::getSingleton('core/resource');

        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_listing_other'),
                array(
                         'count'          => new Zend_Db_Expr('COUNT(*)'),
                         'component'      => 'component_mode',
                         'marketplace_id' => 'marketplace_id',
                         'account_id'     => 'account_id',
                )
            )
            ->group(
                array(
                          'component_mode',
                          'marketplace_id',
                          'account_id'
                )
            )
              ->query();

        $data['listings_other']['total'] = 0;

        $availableComponents = Mage::helper('M2ePro/Component')->getComponents();
        foreach ($availableComponents as $nick) {
            $data['listings_other'][$nick]['total'] = 0;
        }

        $helper = Mage::helper('M2ePro/Component');
        while ($row = $queryStmt->fetch()) {
            if (!in_array($row['component'], $availableComponents)) {
                continue;
            }

            $data['listings_other']['total'] += (int)$row['count'];
            $data['listings_other'][$row['component']]['total'] += (int)$row['count'];

            $markTitle = $helper->getCachedUnknownObject('Marketplace', $row['marketplace_id'])
                                ->getTitle();

            $accountTitle = $helper->getCachedUnknownObject('Account', $row['account_id'])
                                   ->getTitle();

            if (!isset($data['listings_other'][$row['component']]['marketplaces'][$markTitle])) {
                $data['listings_other'][$row['component']]['marketplaces'][$markTitle] = 0;
            }

            if (!isset($data['listings_other'][$row['component']]['accounts'][$accountTitle])) {
                $data['listings_other'][$row['component']]['accounts'][$accountTitle] = 0;
            }

            $data['listings_other'][$row['component']]['marketplaces'][$markTitle] += (int)$row['count'];
            $data['listings_other'][$row['component']]['accounts'][$accountTitle] += (int)$row['count'];
        }
    }

    protected function appendExtensionPoliciesInfo(&$data)
    {
        $this->_appendComponentPolicyInfo('selling_format', 'amazon', $data);
        $this->_appendComponentPolicyInfo('synchronization', 'amazon', $data);
        $this->_appendComponentPolicyInfo('description', 'amazon', $data);
        $this->_appendComponentPolicyInfo('product_tax_code', 'amazon', $data);
        $this->_appendComponentPolicyInfo('shipping', 'amazon', $data);

        $this->_appendComponentPolicyInfo('selling_format', 'ebay', $data);
        $this->_appendComponentPolicyInfo('synchronization', 'ebay', $data);
        $this->_appendComponentPolicyInfo('description', 'ebay', $data);
        $this->_appendComponentPolicyInfo('payment', 'ebay', $data);
        $this->_appendComponentPolicyInfo('shipping', 'ebay', $data);
        $this->_appendComponentPolicyInfo('return', 'ebay', $data);
        $this->_appendComponentPolicyInfo('category', 'ebay', $data);
        $this->_appendComponentPolicyInfo('other_category', 'ebay', $data);

        $this->_appendComponentPolicyInfo('selling_format', 'walmart', $data);
        $this->_appendComponentPolicyInfo('synchronization', 'walmart', $data);
        $this->_appendComponentPolicyInfo('description', 'walmart', $data);
        $this->_appendComponentPolicyInfo('category', 'walmart', $data);
    }

    protected function appendExtensionOrdersInfo(&$data)
    {
        $resource = Mage::getSingleton('core/resource');

        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_order'),
                array(
                         'count'          => new Zend_Db_Expr('COUNT(*)'),
                         'component'      => 'component_mode',
                         'marketplace_id' => 'marketplace_id',
                         'account_id'     => 'account_id',
                )
            )
            ->group(
                array(
                          'component_mode',
                          'marketplace_id',
                          'account_id'
                )
            )
              ->query();

        $data['orders']['total'] = 0;

        $availableComponents = Mage::helper('M2ePro/Component')->getComponents();
        foreach ($availableComponents as $nick) {
            $data['orders'][$nick]['total'] = 0;
        }

        $helper = Mage::helper('M2ePro/Component');
        while ($row = $queryStmt->fetch()) {
            if (!in_array($row['component'], $availableComponents)) {
                continue;
            }

            $data['orders']['total'] += (int)$row['count'];
            $data['orders'][$row['component']]['total'] += (int)$row['count'];

            $markTitle = $helper->getCachedUnknownObject('Marketplace', $row['marketplace_id'])
                                ->getTitle();

            $accountTitle = $helper->getCachedUnknownObject('Account', $row['account_id'])
                                   ->getTitle();

            if (!isset($data['orders'][$row['component']]['marketplaces'][$markTitle])) {
                $data['orders'][$row['component']]['marketplaces'][$markTitle] = 0;
            }

            if (!isset($data['orders'][$row['component']]['accounts'][$accountTitle])) {
                $data['orders'][$row['component']]['accounts'][$accountTitle] = 0;
            }

            $data['orders'][$row['component']]['marketplaces'][$markTitle] += (int)$row['count'];
            $data['orders'][$row['component']]['accounts'][$accountTitle] += (int)$row['count'];
        }

        // Orders types eBay
        $result = $resource->getConnection('core_read')
               ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_ebay_order'),
                array('count' => new Zend_Db_Expr('COUNT(*)'))
            )
               ->where('checkout_status = ?', Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED)
               ->query()
               ->fetchColumn();

        $data['orders']['ebay']['types']['checkout'] = (int)$result;

        $result = $resource->getConnection('core_read')
               ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_ebay_order'),
                array('count' => new Zend_Db_Expr('COUNT(*)'))
            )
               ->where('shipping_status = ?', Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED)
               ->query()
               ->fetchColumn();

        $data['orders']['ebay']['types']['shipped'] = (int)$result;

        $result = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_ebay_order'),
                array('count' => new Zend_Db_Expr('COUNT(*)'))
            )
              ->where('payment_status = ?', Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)
              ->query()
              ->fetchColumn();

        $data['orders']['ebay']['types']['paid'] = (int)$result;
        // ---------------------------------------

        // Orders types Amazon
        $queryStmt = $resource->getConnection('core_read')
               ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_amazon_order'),
                array(
                          'count'  => new Zend_Db_Expr('COUNT(*)'),
                          'status' => 'status'
                )
            )
               ->group(array('status'))
               ->query();

        $statuses = array(
            Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING             => 'pending',
            Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED           => 'unshipped',
            Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY   => 'shipped_partially',
            Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED             => 'shipped',
            Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE       => 'unfulfillable',
            Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED            => 'canceled',
            Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED => 'invoice_uncorfirmed'
        );

        while ($row = $queryStmt->fetch()) {
            $status = $statuses[(int)$row['status']];

            if (!isset($data['orders']['amazon']['types'][$status])) {
                $data['orders']['amazon']['types'][$status] = 0;
            }

            $data['orders']['amazon']['types'][$status] += (int)$row['count'];
        }

        // ---------------------------------------
    }

    protected function appendExtensionLogsInfo(&$data)
    {
        $data['logs']['total'] = 0;

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $nick) {
            $data['logs'][$nick]['total'] = 0;
        }

        $data = $this->_appendLogsInfoByType('listings', 'm2epro_listing_log', $data);
        $data = $this->_appendLogsInfoByType('synchronization', 'm2epro_synchronization_log', $data);
        $data = $this->_appendLogsInfoByType('orders', 'm2epro_order_log', $data);
        $data = $this->_appendLogsInfoByType('other_listings', 'm2epro_listing_other_log', $data);
    }

    //########################################

    protected function _appendLogsInfoByType($type, $tableName, $data)
    {
        $resource = Mage::getSingleton('core/resource');

        $queryStmt = $resource->getConnection('core_read')
              ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName),
                array(
                         'count'     => new Zend_Db_Expr('COUNT(*)'),
                         'component' => 'component_mode'
                )
            )
              ->group('component_mode')
              ->query();

        $data['logs']['types'][$type] = 0;

        $availableComponents = Mage::helper('M2ePro/Component')->getComponents();
        foreach ($availableComponents as $nick) {
            $data['logs'][$nick]['types'][$type] = 0;
        }

        while ($row = $queryStmt->fetch()) {
            if (!in_array($row['component'], $availableComponents)) {
                continue;
            }

            $data['logs']['total'] += (int)$row['count'];
            $data['logs']['types'][$type] += (int)$row['count'];

            $data['logs'][$row['component']]['total'] += (int)$row['count'];
            $data['logs'][$row['component']]['types'][$type] += (int)$row['count'];
        }

        return $data;
    }

    protected function _appendComponentPolicyInfo($template, $component, &$data)
    {
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');
        $tableName = $structureHelper->getTableNameWithPrefix('m2epro_' . $component .'_template_'. $template);

        $queryStmt = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from($tableName, array('count' => new Zend_Db_Expr('COUNT(*)')))
            ->query();

        $data['policies'][$component][$template]['count'] = (int)$queryStmt->fetchColumn();

        if ($component === Ess_M2ePro_Helper_Component_Ebay::NICK &&
            !in_array($template, array('category', 'other_category')))
        {
            $queryStmt = Mage::getSingleton('core/resource')->getConnection('core_read')
                ->select()
                ->from(
                    $structureHelper->getTableNameWithPrefix('m2epro_ebay_listing_product'),
                    array('count(*)')
                )
                ->where("template_{$template}_mode != ?", Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT)
                ->query();

            $data['policies'][$component][$template]['is_custom_for_listing_products'] = (int)$queryStmt->fetchColumn();
        }
    }

    //########################################
}
