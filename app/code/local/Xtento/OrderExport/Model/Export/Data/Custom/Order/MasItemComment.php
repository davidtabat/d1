<?php

/**
 * Product:       Xtento_OrderExport (1.8.5)
 * ID:            gynD3Fd2Yg7FQAAmb/3dfnkXJHnIofKqiiFav0FPkb8=
 * Packaged:      2015-09-09T07:08:21+00:00
 * Last Modified: 2015-07-13T16:28:24+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Custom/Order/MasItemComment.php
 * Copyright:     Copyright (c) 2015 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Custom_Order_MasItemComment extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Mas_Masoic Item Comment Export',
            'category' => 'Order',
            'description' => 'Export item comments stored by the Mas_Masoic extension',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
            'third_party' => true,
            'depends_module' => 'Mas_Masoic',
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('masoic_comments')) {
            return $returnArray;
        }

        // Sample to get comment for item:
        // <xsl:variable name="itemId" select="item_id"/>
        // <xsl:value-of select="../../masoic_comments/masoic_comment[item_id=$itemId]/comment"/>
        try {
            $this->_writeArray = & $returnArray['masoic_comments']; // Write on "masoic_comments" level
            $allComments = Mage::getModel('masoic/comment')->getComments($order->getId());
            foreach ($allComments as $itemId => $itemComments) {
                foreach ($itemComments as $itemComment) {
                    $this->_writeArray = &$returnArray['masoic_comments'][];
                    foreach ($itemComment as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                }
            }
        } catch (Exception $e) {

        }
        $this->_writeArray = & $returnArray;

        // Done
        return $returnArray;
    }
}