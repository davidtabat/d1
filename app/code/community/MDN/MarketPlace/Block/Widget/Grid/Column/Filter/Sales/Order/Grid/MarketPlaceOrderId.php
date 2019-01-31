<?php

/**
 * Class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Sales_Order_Grid_MarketPlaceOrderId
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Sales_Order_Grid_MarketPlaceOrderId extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    /**
     * Get filter condition
     *
     * @return array|bool
     */
    public function getCondition(){

        if(!$searchString = $this->getValue())
            return false;

        $sql = 'SELECT o.entity_id
                FROM sales_flat_order AS o
                WHERE o.marketplace_order_id LIKE  "%'.$searchString.'%"';

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $res = $read->fetchCol($sql);

        return (is_array($res) && count($res) > 0) ? array('in', $res): false;

    }

}