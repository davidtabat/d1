<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_Carrier_Nnrakuten extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'nnrakuten';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $shippingPrice = $this->getConfigData('price');
        if ($shippingPrice !== false) {
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier('nnrakuten');
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('nnrakuten');
            $method->setMethodTitle($this->getConfigData('name'));
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        return array('nnrakuten' => $this->getConfigData('name'));
    }
}
