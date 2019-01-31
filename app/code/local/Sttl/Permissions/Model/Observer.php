<?php

/* Added as per the instruction of 
  Asst. Project Leader Chintan Pancholi. */

class Sttl_Permissions_Model_Observer {

    protected $_helper;

    public function __construct() {
        $this->_helper = Mage::helper('permissions');
    }

    public function savePermissionRole($observer) {
        if (Mage::helper('permissions')->permissionsEnabled()) {
            $role = $observer->getObject();
            $request = Mage::app()->getRequest()->getPost();
            $all = $request['gws_is_all'];
            $roleId = $role->getId();
            if ($all != 1) {
                if (!empty($request['store_groups'])) {
                    $StoreIds = $request['store_groups'];
                    $SelectedStoreIds = implode(',', $StoreIds);
                    if (!empty($request['store_groups_name'])) {
                        $StoreViewIds = $request['store_groups_name'];
                        $SelectedStoreViewIds = implode(',', $StoreViewIds);
                        if (!empty($request['sub-category'])) {
                            $subCatIds = $request['sub-category'];
                            $subCategory = implode(',', $subCatIds);
                        } else {
                            $subCategory = '';
                        }

                        if (!empty($request['root-category'])) {
                            $rootIds = implode(",", $request['root-category']);
                        } else {
                            foreach ($StoreViewIds as $s) {
                                $rootIds1[] = Mage::getModel('core/store')->load($s)->getRootCategoryId();
                            }
                            $rootIds = implode(",", array_unique($rootIds1));
                        }

                        $store_model = Mage::getModel('core/store');
                        foreach ($StoreIds as $store_id) {
                            $store_data = $store_model->load($store_id);
                        }
                    }
                    if ($roleId) {
                        // Cleaning
                        $advancedroleCollection = Mage::getModel('permissions/advancedrole')->getCollection();
                        $advancedroleCollection->addFieldToFilter('role_id', $roleId)->load();
                        foreach ($advancedroleCollection as $advancedrole) {
                            $advancedrole->delete();
                        }
                        $advancedrole = Mage::getModel('permissions/advancedrole');

                        $advancedrole->setData('role_id', $roleId);
                        $advancedrole->setData('gws_is_all', $all);
                        $advancedrole->setData('store_id', $SelectedStoreIds);
                        $advancedrole->setData('storeview_ids', $SelectedStoreViewIds);
                        $advancedrole->setData('root_cat_ids', $rootIds);
                        $advancedrole->setData('sub_cat_ids', $subCategory);
                        $advancedrole->setData('website_id', 1);
                        $advancedrole->save();
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please Select at least one store.'));
                }
            } else {
                $advancedroleCollection = Mage::getModel('permissions/advancedrole')->getCollection();
                $advancedroleCollection->addFieldToFilter('role_id', $roleId)->load();
                foreach ($advancedroleCollection as $advancedrole) {
                    if ($advancedrole) {
                        $advancedrole->delete();
                    }
                }
            }
        }
    }

    protected function storeCollection($collection) {
        if (!$collection->getFlag('permissions_processed')) {
            if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml')) {
                if (Mage::helper('permissions')->permissionsEnabled()) {
                    $currentRoles = Mage::helper('permissions')->getUser();
                    $coll = $currentRoles->getData();
                    $errors = array_filter($coll);
                    if (!empty($errors)) {
                        foreach ($currentRoles as $r) {
                            $storeViewIds = explode(',', $r['storeview_ids']);
                        }
                        $collection->addAttributeToFilter('store_id', array('in' => $storeViewIds));
                    }
                }
            }
            $collection->setFlag('permissions_processed', true);
        }
    }

    public function orderCollectionLoadBefore($observer) {
        $collection = $observer->getCollection();

        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Collection) {
            $this->storeCollection($collection);
        }

        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Invoice_Collection) {
            $this->storeCollection($collection);
        }

        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Shipment_Collection) {
            $this->storeCollection($collection);
        }

        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Creditmemo_Collection) {
            $this->storeCollection($collection);
        }
    }

    public function gridCollectionLoadBefore($observer) {
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection) {
            $this->storeCollection($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Invoice_Grid_Collection) {
            $this->storeCollection($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Shipment_Grid_Collection) {
            $this->storeCollection($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Creditmemo_Grid_Collection) {
            $this->storeCollection($collection);
        }
    }

    public function orderLoadAfter($observer) {
        $order = $observer->getOrder();

        if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml')) {
            if (Mage::helper('permissions')->permissionsEnabled()) {
                $currentRoles = Mage::helper('permissions')->getUser();
                $coll = $currentRoles->getData();
                $errors = array_filter($coll);
                if (!empty($errors)) {
                    foreach ($currentRoles as $r) {
                        $storeViewIds = explode(',', $r['storeview_ids']);
                    }
                }
                if (!in_array($order->getStoreId(), $storeViewIds)) {
                    Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/sales_order'));
                }
            }
        }
    }

    public function deletePermissionRole($observer) {
        $role = $observer->getObject();

        if ($role) {
            $advancedroleCollection = Mage::getModel('permissions/advancedrole')->getCollection();
            $advancedroleCollection->addFieldToFilter('role_id', $role->getId())->load();
            foreach ($advancedroleCollection as $advancedrole) {
                $advancedrole->delete();
            }
        }
    }

    public function productCollectionLoadBefore($observer) {
        if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml') || false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'bundle')) {
            if (Mage::helper('permissions')->permissionsEnabled()) {
                $currentRoles = Mage::helper('permissions')->getUser();
                $coll = $currentRoles->getData();
                $errors = array_filter($coll);
                if (!empty($errors)) {
                    foreach ($currentRoles as $r) {
                        if (!empty($r['sub_cat_ids'])) {
                            $catIds = explode(',', $r['sub_cat_ids']);
                        } else {
                            $catIds = '';
                        }
                    }
                }
                $collection = $observer->getCollection();
                if (!empty($catIds)) {
                    $where = ' e.entity_id IN ( ' .
                            ' SELECT product_id ' .
                            ' FROM ' . $collection->getTable('catalog_category_product') . ' ' .
                            ' WHERE category_id IN (' . join(',', $catIds) . ') ' .
                            ' ) ';
                    $collection->getSelect()->where($where);
                }
            }
        }
    }

    public function onBlockHtmlBeforeFunction(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if (!isset($block))
            return;
        switch ($block->getType()) {
            case 'adminhtml/cms_page_edit':
                if (Mage::helper('permissions')->permissionsEnabled()) {
                    $allowDelete = true;
                    $cmsPage = Mage::registry('cms_page');
                    $currentRoles = Mage::helper('permissions')->getUser();
                    $coll = $currentRoles->getData();
                    $errors = array_filter($coll);
                    if (!empty($errors)) {
                        foreach ($currentRoles as $r) {
                            if (!empty($r['storeview_ids'])) {
                                $storeViewIds = explode(',', $r['storeview_ids']);
                            } else {
                                $storeViewIds = '';
                            }
                        }
                    }
                    if (!empty($storeViewIds)) {
                        if (is_array($cmsPage->getStoreId()) && $cmsPage->getStoreId()) {
                            foreach ($cmsPage->getStoreId() as $pageStoreId) {
                                if (!in_array($pageStoreId, $storeViewIds)) {
                                    $allowDelete = false;
                                    break 1;
                                }
                            }
                        }
                    }

                    if (!$allowDelete) {
                        $block->removeButton('delete');
                    }
                }
                break;
            case 'adminhtml/cms_block_edit':
                if (Mage::helper('permissions')->permissionsEnabled()) {
                    $allowDelete = true;
                    $cmsPage = Mage::registry('cms_block');
                    $currentRoles = Mage::helper('permissions')->getUser();
                    $coll = $currentRoles->getData();
                    $errors = array_filter($coll);
                    if (!empty($errors)) {
                        foreach ($currentRoles as $r) {
                            if (!empty($r['storeview_ids'])) {
                                $storeViewIds = explode(',', $r['storeview_ids']);
                            } else {
                                $storeViewIds = '';
                            }
                        }
                    }
                    if (!empty($storeViewIds)) {
                        if (is_array($cmsPage->getStoreId()) && $cmsPage->getStoreId()) {
                            foreach ($cmsPage->getStoreId() as $pageStoreId) {
                                if (!in_array($pageStoreId, $storeViewIds)) {
                                    $allowDelete = false;
                                    break 1;
                                }
                            }
                        }
                    }

                    if (!$allowDelete) {
                        $block->removeButton('delete');
                    }
                }
                break;
            case 'adminhtml/poll_edit':
                if (Mage::helper('permissions')->permissionsEnabled()) {
                    $currentRoles = Mage::helper('permissions')->getUser();
                    $coll = $currentRoles->getData();
                    $errors = array_filter($coll);
                    if (!empty($errors)) {
                        foreach ($currentRoles as $r) {
                            if (!empty($r['storeview_ids'])) {
                                $storeViewIds = explode(',', $r['storeview_ids']);
                            } else {
                                $storeViewIds = '';
                            }
                        }
                    }
                    $allowDelete = true;
                    $poll = Mage::registry('poll_data');
                    if (!empty($storeViewIds)) {
                        if ($storeViewIds && !array_intersect($poll->getStoreIds(), $storeViewIds) && $poll->getId()) {
                            Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
                        }

                        if ($poll->getStoreIds() && is_array($poll->getStoreIds())) {
                            foreach ($poll->getStoreIds() as $pollStoreId) {
                                if (!in_array($pollStoreId, $storeViewIds)) {
                                    $allowDelete = false;
                                    break 1;
                                }
                            }
                        }
                    }

                    if (!$allowDelete) {
                        $block->removeButton('delete');
                    }
                }
                break;
        }
    }

}
