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

class MDN_Cdiscount_Helper_Tracking extends MDN_MarketPlace_Helper_Tracking {

    /**
     * Send tracking
     */
    public function sendTracking(){

        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        if(!$account->getParam('seller_product_reference')){
            throw new Exception('Please select seller product id in account configuration');
        }

        $orders = null;
        $order_ids = array();
        $orders_to_update = array();
        $helper = Mage::Helper('Cdiscount/Services');

        // recuperation des commandes en attente de livraison
        $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeGetOrdersToShip);
        $res = $helper->getOrderList(array(MDN_Cdiscount_Helper_Orders::kWaitingForShipmentAcceptation));

        $xml = new DomDocument();
        $xml->loadXML($res['content']);

        if($xml->getElementsByTagName('Order')->item(0)){
            foreach($xml->getElementsByTagName('Order') as $order){
                $order_ids[] = $order->getElementsByTagName('OrderNumber')->item(0)->nodeValue;
            }
        }

        // get orders to update
        if(count($order_ids) > 0){

            $orders = mage::getModel('sales/order')
                            ->getCollection()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('from_site', Mage::helper('Cdiscount')->getMarketPlaceName())
                            ->addAttributeToFilter('marketplace_order_id', $order_ids)
                            ->addAttributeToFilter('status', 'complete');

            if(count($orders) > 0){

                $i = 0;
                $shipping_method = $country->getParam('default_shipment_method');
                $shipping_method_title = mage::helper('MarketPlace')->getShippingMethodTitle($shipping_method);
                
                foreach($orders as $order){

                    $items = array();
                    // retrieve tracking
                    $tracking_number = Mage::Helper('MarketPlace/Tracking')->getTrackingForOrder($order);
                    //$shipping_method_title = $order->getshipping_description();

                    if($tracking_number == '')
                        continue;

                    foreach($order->getAllItems() as $item){

                        $sellerProductId = ($account->getParam('seller_product_reference') == 'sku') ? $item->getsku() : $item->getproduct_id();

                        $items[] = array(
                            'status' => 'ShippedBySeller',
                            'sellerProductId' => $sellerProductId
                        );

                    }

                    $orders_to_update[$i] = array(
                            'items' => $items,
                            'orderNumber' => $order->getmarketplace_order_id(),
                            'status' => MDN_Cdiscount_Helper_Orders::kShipped,
                            'tracking' => array(
                                'carrierName' => $shipping_method_title,
                                'number' => $tracking_number,
                                'url' => ''
                            )
                    );

                    $i++;

                }

                if(count($orders_to_update) > 0){
                    $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeTracking);
                    $res = $helper->validateOrderList($orders_to_update);
                }


            }

        }


    }
    

}
