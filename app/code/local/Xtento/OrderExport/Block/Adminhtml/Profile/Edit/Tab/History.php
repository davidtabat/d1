<?php

/**
 * Product:       Xtento_OrderExport (1.8.5)
 * ID:            gynD3Fd2Yg7FQAAmb/3dfnkXJHnIofKqiiFav0FPkb8=
 * Packaged:      2015-09-09T07:08:21+00:00
 * Last Modified: 2014-07-14T20:56:51+02:00
 * File:          app/code/local/Xtento/OrderExport/Block/Adminhtml/Profile/Edit/Tab/History.php
 * Copyright:     Copyright (c) 2015 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Block_Adminhtml_Profile_Edit_Tab_History extends Xtento_OrderExport_Block_Adminhtml_History_Grid
{
    protected function _getProfile()
    {
        return Mage::registry('order_export_profile') ? Mage::registry('order_export_profile') : Mage::getModel('xtento_orderexport/profile')->load($this->getRequest()->getParam('id'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('xtento_orderexport/history_collection');
        if ($this->_getProfile()->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_QUOTE) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('sales/' . $this->_getProfile()->getEntity())), 'main_table.entity_id = object.entity_id', array('object.entity_id'));
        } else if ($this->_getProfile()->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_AWRMA) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('awrma/entity')), 'main_table.entity_id = object.id', array('object.id'));
        } else if ($this->_getProfile()->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_BOOSTRMA) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('ProductReturn/rma')), 'main_table.entity_id = object.rma_id', array('object.rma_id'));
        } else if ($this->_getProfile()->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('customer/entity')), 'main_table.entity_id = object.entity_id', array('object.entity_id'));
        } else {
            if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.4.0.1', '>')) {
                $collection->getSelect()->joinLeft(array('object' => $collection->getTable('sales/' . $this->_getProfile()->getEntity())), 'main_table.entity_id = object.entity_id', array('object.increment_id'));
            }
        }
        $collection->addFieldToFilter('main_table.profile_id', $this->_getProfile()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        unset($this->_columns['profile']);
        foreach ($this->_columns as $key => $column) {
            if ($key == 'history_id') {
                continue;
            }
            // Rename column IDs so they're not posted to the profile information
            $column->setId('col_' . $column->getId());
            $this->_columns['col_' . $key] = $column;
            unset($this->_columns[$key]);
        }
        if ($this->_getProfile()->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_QUOTE || $this->_getProfile()->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
            unset($this->_columns['col_increment_id']);
        } else {
            $this->_columns['col_increment_id']->setFilterConditionCallback(false);
            $this->_columns['col_increment_id']->setFilterIndex('object.increment_id');
        }
    }

    protected function _prepareMassaction()
    {
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/historyGrid', array('_current' => true));
    }
}