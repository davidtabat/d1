<?php

/**
 * Product:       Xtento_OrderExport (1.8.5)
 * ID:            gynD3Fd2Yg7FQAAmb/3dfnkXJHnIofKqiiFav0FPkb8=
 * Packaged:      2015-09-09T07:08:21+00:00
 * Last Modified: 2014-07-14T21:16:21+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Shared/General.php
 * Copyright:     Copyright (c) 2015 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Shared_General extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Entity fields',
            'category' => 'Shared',
            'description' => 'Export fields from the respective *entity* table.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO, Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER, Xtento_OrderExport_Model_Export::ENTITY_QUOTE, Xtento_OrderExport_Model_Export::ENTITY_AWRMA, Xtento_OrderExport_Model_Export::ENTITY_BOOSTRMA),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        $this->_writeArray = & $returnArray; // Write directly on object level
        // Fetch fields to export
        $object = $collectionItem->getObject();

        // Timestamps of creation/update
        if ($this->fieldLoadingRequired('created_at_timestamp')) $this->writeValue('created_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($object->getCreatedAt()));
        if ($this->fieldLoadingRequired('updated_at_timestamp')) $this->writeValue('updated_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($object->getUpdatedAt()));

        // Which order line is this?
        $this->writeValue('order_line_number', $collectionItem->_currItemNo); // Legacy field
        if ($entityType !== Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
            $this->writeValue('order_count', $collectionItem->_collectionSize); // Legacy field
        }
        $this->writeValue('line_number', $collectionItem->_currItemNo);
        $this->writeValue('count', $collectionItem->_collectionSize);

        // Export information
        $this->writeValue('export_id', (Mage::registry('export_log')) ? Mage::registry('export_log')->getId() : 0);

        // General data - just not for orders and customers, handled in its own order_general class
        if ($entityType !== Xtento_OrderExport_Model_Export::ENTITY_ORDER && $entityType !== Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
            foreach ($object->getData() as $key => $value) {
                $this->writeValue($key, $value);
            }
        } else {
            // Just the entity_id at least for orders
            $this->writeValue('entity_id', $object->getId());
        }

        // Done
        return $returnArray;
    }
}