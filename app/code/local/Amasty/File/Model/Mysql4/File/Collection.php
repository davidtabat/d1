<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_Mysql4_File_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{

    public function _construct() 
    {
        $this->_init('amfile/file');
    }

    public function getFilesFrontend($productId, $storeId)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.file_id AND s.store_id = ?", $storeId);
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $select = $this->getSelect()
            ->join(
                array('d' => $this->getTable('amfile/store')), "main_table.file_id = d.file_id AND d.store_id = 0")
            ->joinLeft(
                array('s' => $this->getTable('amfile/store')), $joinCondition,
                array(
                    'label' => $this->getCheckSql("s.use_default_label = 0", 's.label', 'd.label'),
                    'visible' => $this->getCheckSql("s.use_default_visible = 0", 's.visible', 'd.visible'),
                    'position' => $this->getCheckSql("s.position IS NULL", 'd.position', 's.position'),
                    'store_id' => $this->getCheckSql("s.store_id IS NULL", 'd.store_id', 's.store_id'),
                    'show_ordered' => $this->getCheckSql(
                        's.show_ordered IS NULL OR s.use_default_show_ordered = 1',
                        'd.show_ordered', 's.show_ordered'
                    )
                ))
            ->joinInner(array('cg' => $this->getTable('amfile/store_customer_group')),
                $adapter->quoteInto("(
                    main_table.file_id = cg.file_id
                    AND ((s.store_id IS NULL OR s.use_default_customer_group = 1)
                        AND (d.store_id = cg.store_id)
                    OR (s.store_id = cg.store_id))
                    AND (cg.customer_group_id = '-1' OR cg.customer_group_id = ?))", $customerGroupId), '')
            ->where('main_table.product_id = ?', $productId)
            ->having('visible = 1')
            ->order("position ASC")
            ->group('main_table.file_id');
        $productIdRow = $this->_getCustomerOrderedProduct($productId);
        $select->having(
                '(( show_ordered = 1 AND main_table.product_id IN (?)) OR show_ordered = 0)',
                $productIdRow
            );

//        echo $select->__toString();die;
        return $this;
    }

    public function getFilesAdmin($productId, $storeId)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.file_id AND s.store_id = ?", $storeId);
        $select = $this->getSelect()
            ->join(
                array('d' => $this->getTable('amfile/store')), 'main_table.file_id = d.file_id')
            ->joinLeft(array('s' => $this->getTable('amfile/store')), $joinCondition,
                array(
                    'label'         => $this->getCheckSql("s.use_default_label = 0", 's.label', 'd.label'),
                    'visible'       => $this->getCheckSql("s.use_default_visible = 0", 's.visible', 'd.visible'),
                    'position'      => $this->getCheckSql("s.position IS NULL", 'd.position', 's.position'),
                    'use_default_label'         => $this->getCheckSql("s.use_default_label = 0", '0', '1'),
                    'use_default_visible'       => $this->getCheckSql("s.use_default_visible = 0", '0', '1'),
                    'show_ordered'              => $this->getCheckSql('s.show_ordered IS NULL', 'd.show_ordered', 's.show_ordered'),
                    'default_show_ordered'  => $this->getCheckSql('s.use_default_show_ordered = 0', '0', '1'),
                    'default_customer_group'  => $this->getCheckSql('s.use_default_customer_group = 0', '0', '1'),
            ))
            ->joinLeft(array('cg' => $this->getTable('amfile/store_customer_group')),
                '((s.store_id IS NULL AND cg.store_id = d.store_id) OR s.store_id = cg.store_id) AND cg.file_id = main_table.file_id AND cg.is_active = 1',
                array('customer_groups' => new Zend_Db_Expr('GROUP_CONCAT(`customer_group_id`)')))
            ->where('main_table.product_id = ?', $productId)
            ->where('d.store_id = ?',0)
            ->group('main_table.file_id');
        return $this;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param int $attachmentId
     * @return Amasty_File_Model_Mysql4_File_Collection $this
     */
    public function getFilesApi($productId, $storeId = 0, $attachmentId = 0)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.file_id AND s.store_id = ?", $storeId);
        $select = $this->getSelect()
            ->join(
                array('d' => $this->getTable('amfile/store')), 'main_table.file_id = d.file_id')
            ->joinLeft(
                array('s' => $this->getTable('amfile/store')), $joinCondition, array(
                'label' => $this->getCheckSql("s.use_default_label = 0", 's.label', 'd.label'),
                'visible' => $this->getCheckSql("s.use_default_visible = 0", 's.visible', 'd.visible'),
                'position' => $this->getCheckSql("s.position IS NULL", 'd.position', 's.position'),
                'use_default_label' => $this->getCheckSql("s.use_default_label = 0", '0', '1'),
                'use_default_visible' => $this->getCheckSql("s.use_default_visible = 0", '0', '1'),
            ))
            ->where('main_table.product_id = ?', $productId)
            ->where('d.store_id = ?',0)
            ->where($attachmentId != 0 ? 'main_table.file_id = ?' : '1', $attachmentId);
        return $this;
    }

    public function readFiles()
    {
        foreach($this as $item)
        {
            $item->readFile();
        }
        $this->setResetItemsDataChanged(false);
        return $this;
    }

    /**
     * Delete Attachments in Collection from DB and if necessary from files system
     *
     */
    public function deleteAttachments()
    {
        foreach($this as $item)
        {
            $item->delete();
        }
        return $this;
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }

    protected function _getCustomerOrderedProduct($productId) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $orders = Mage::getModel('sales/order')->getCollection()
            ->join(array('oi' => 'sales/order_item'), sprintf('main_table.entity_id = oi.order_id AND product_id = %d', $productId), 'product_id')
            ->addFieldToFilter('main_table.customer_id', $customer->getId())
            ->addFieldToFilter('main_table.status', Mage_Sales_Model_Order::STATE_COMPLETE);
        return $orders->getColumnValues('product_id');
    }
}
