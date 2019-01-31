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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Sales_Order_Grid_MarketPlaceOrderId extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * renderer
     * 
     * @param Varien_Object $row
     * @return string $html 
     */
    public function render(Varien_Object $row) {

        $html = '';

        $order = Mage::getModel('sales/order')->load($row->getId());

        if ($order->getmarketplace_order_id())
            $html .= $order->getmarketplace_order_id() . ' (' . $order->getfrom_site() . ')';
        else
            $html .= ' - ';

        return $html;
    }

}
