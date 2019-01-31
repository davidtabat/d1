<?php

/**
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
class MDN_MarketPlace_Block_Orders_Tabs_Statistics extends Mage_Adminhtml_Block_Template {
    
    /**
     * Construct 
     */
    public function __construct(){
        
        parent::__construct();
        $this->setTemplate('MarketPlace/Orders/Tabs/Statistics.phtml');
        
    }
    
    /**
     * Get totals
     *
     * @return array $retour
     */
    public function getTotals(){

        $retour = array();
        $marketplaces = array();

        $subtotal = 0;
        $tax = 0;
        $shipping = 0;
        $quantity = 0;
        $items = 0;

        $helpers = Mage::helper('MarketPlace')->getHelpers();

        if(count($helpers) > 0){

            foreach($helpers as $helper){

                if(Mage::helper($helper)->isDisplayedInSalesOrderSummary()){
                    $marketplaces[] = strtolower(Mage::helper($helper)->getMarketPlaceName());
                }

            }

            $marketplacesString = "";

            for($i=0; $i<count($marketplaces);$i++){
                $marketplacesString .= '"'.strtolower($marketplaces[$i]).'"';
                if($i < count($marketplaces) -1 )
                    $marketplacesString .= ',';
            }

            $sql = $this->getSql($marketplacesString);

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $orders = $read->fetchAll($sql);

            foreach($orders as $order){

                $marketplace = strtolower($order['from_site']);

                if(!array_key_exists($marketplace, $retour)){
                    $retour[$marketplace] = array(
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'shipping' => $shipping,
                        'quantity' => $quantity,
                        'items' => $items
                    );
                }

                $retour[$marketplace]['subtotal'] += $order['subtotal'] - $order['subtotal_refunded'] - $order['subtotal_canceled'];
                $retour[$marketplace]['tax'] += $order['tax_amount'] - $order['tax_refunded'] - $order['tax_canceled'];
                $retour[$marketplace]['shipping'] += $order['shipping_amount'] - $order['shipping_refunded']- $order['shipping_canceled'];
                $retour[$marketplace]['quantity'] += 1;
                $retour[$marketplace]['items'] += 0;

            }
        }

        return $retour;

    }

    /**
     * Get sql
     *
     * @param string $marketplaces
     * @return string $sql
     */
    public function getSql($marketplaces){

        $sql = "";
        $prefix = Mage::getConfig()->getTablePrefix();

        if(Mage::helper('MarketPlace/FlatOrder')->isFlatOrder()){
            $sql = 'SELECT from_site, subtotal, subtotal_refunded, subtotal_canceled, tax_amount, tax_refunded, tax_canceled, shipping_amount, shipping_refunded, shipping_canceled
                    FROM '.$prefix.'sales_flat_order
                    WHERE from_site IN ('.$marketplaces.')
                    AND status = "complete"';
        }
        else{
            
            $sql = 'SELECT attribute_id AS attribute_id, backend_type AS backend_type
                    FROM '.$prefix.'eav_attribute
                    WHERE attribute_code = "from_site"';
            
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $infos = $read->fetchAll($sql);
            
            $sql = 'SELECT f.value AS from_site, o.subtotal, o.subtotal_refunded, o.subtotal_canceled, o.tax_amount, o.tax_refunded, o.tax_canceled,
                o.shipping_amount, o.shipping_refunded, o.shipping_canceled
                FROM '.$prefix.'sales_order AS o
                LEFT JOIN '.$prefix.'sales_order_'.$infos[0]['backend_type'].' AS f
                ON (o.entity_id = f.entity_id) AND (f.attribute_id = '.$infos[0]['attribute_id'].')
                WHERE f.value IN ('.$marketplaces.')';

        }

        return $sql;
    }

    
}
