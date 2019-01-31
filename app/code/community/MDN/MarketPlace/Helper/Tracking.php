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

class MDN_MarketPlace_Helper_Tracking extends Mage_Core_Helper_Abstract {

    /**
     * ge tracking for order
     * 
     * @param Mage_Sales_Model_Order $order
     * @return string $tracking 
     */
    public function getTrackingForOrder($order) {

        $tracking_delta = Mage::registry('mp_country')->getParam('delta_tracking');
        $tracking_default = Mage::registry('mp_country')->getParam('default_tracking');

        $tracking = '';

        //retrieve shipment
        $shipment = null;
        foreach ($order->getShipmentsCollection() as $item) {
            $shipment = $item;
            break;
        }

        if ($shipment != null) {

            foreach ($order->getTracksCollection() as $track) {

                // check magento version, fix retreive traking bug
                $version = Mage::Helper('MarketPlace/MagentoVersionCompatibility')->getVersion();
                $tmp = explode('.', $version);

                if($tmp[0] > 1){
                    $tracking = $track->gettrack_number();
                }else{
                    if($tmp[1] > 5){
                        $tracking = $track->gettrack_number();
                    }else{
                        if (is_object($track->getNumberDetail()))
                            $tracking = $track->getNumberDetail()->gettracking();
                    }

                }

            }

            if ($tracking == '') {

                $current_timestamp = Mage::getModel('Core/Date')->Timestamp();
                $created_at = $shipment->getcreated_at();
                $tmp = explode(" ", $created_at);

                if (count($tmp) == 2) {
                    $values1 = explode("-", $tmp[0]);
                    $values2 = explode(":", $tmp[1]);
                    $timestamp = mktime($values2[0], $values2[1], $values2[2], $values1[1], $values1[2], $values1[0]);
                } else {
                    $values = explode("-", $tmp[0]);
                    $timestamp = mktime(0, 0, 0, $values[1], $values[2], $values[0]);
                }

                if (($current_timestamp - $timestamp) > $tracking_delta * 3600) {
                    $tracking = $tracking_default;
                    $message = 'Order #' . $order->getincrement_id() . ' has been updated as shipped with default tracking number (' . $tracking_default . ')';
                    mail(Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_bug_report(), 'default tracking', $message);
                }
            }
        }

        return $tracking;
    }

}
