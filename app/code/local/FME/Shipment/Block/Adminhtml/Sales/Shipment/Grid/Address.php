<?php

class FME_Shipment_Block_Adminhtml_Sales_Shipment_Grid_Address 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{

    public function render(Varien_Object $row) {

        $order          = Mage::getModel('sales/order')->load($row['order_increment_id'], 'increment_id');
        $data           = $order->getShippingAddress()->getData();
        $countryName    = Mage::app()->getLocale()->getCountryTranslation($data['country_id']);

        $html  = $data['firstname'].' '.$data['lastname'].'<br>';
        $html .= $data['street'].'<br>';
        $html .= $data['city'].', '.$data['region'].', '.$data['postcode'].'<br>';
        $html .= $countryName;
        $html .= $data['telephone'];

        echo $html;
    }
}