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

class MDN_MarketPlace_Model_Carrier_Rates extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    /* @var string */
    protected $_code = 'marketplacerates';
    /* @var boolean */
    protected $_isFixed = true;
    /* @var string */
    protected $_default_condition_name = 'package_weight';

    /**
     * Get rate (HT)
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return float
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request){

        $shipping = 0;
        
        $country = Mage::register('mp_country');
        
        if(!is_object($country) || !($country instanceof MDN_MarketPlace_Model_Countries)){
            
            throw new Exception('Current country is not defined in %s', __METHOD__);
            
        }
        
        $tax = $country->getParam('taxes');
        
        if(!is_numeric($tax)){
            
            throw new Exception('Tax rate not set in %s', __METHOD__);
            
        }
        
        $weight = $request->getPackageWeight();

        if(0 <= $weight && $weight < 0.25)
            $shipping = 5.33;
        elseif(0.25 <= $weight && $weight < 0.5)
            $shipping = 6.22;
        elseif(0.5 <= $weight && $weight < 0.75)
            $shipping = 6.91;
        elseif(0.75 <= $weight && $weight < 1)
            $shipping = 7.41;
        elseif(1 <= $weight && $weight < 2)
            $shipping = 8.18;
        elseif(2 <= $weight && $weight < 3)
            $shipping = 8.96;
        elseif(3 <= $weight && $weight < 5)
            $shipping = 9.46;
        elseif(5 <= $weight && $weight < 10)
            $shipping = 11.25;
        elseif(10 <= $weight && $weight < 15)
            $shipping = 13.05;
        elseif(15 <= $weight && $weight < 20)
            $shipping = 14.84;
        elseif(20 <= $weight && $weight < 25)
            $shipping = 16.63;
        else
            $shipping = 20.27;

        $shipping = $shipping / (1 + $tax /100);

        return $shipping;

    }

    /**
     * Collect rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return <type> $result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request){

        $result = Mage::getModel('shipping/rate_result');
        $rate = $this->getRate($request);

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier('marketplacerate');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('marketplacerate');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($rate);        

        $result->append($method);
        
        return $result;


    }

    /**
     * Get allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('marketplacerates'=>$this->getConfigData('name'));
    }

}
