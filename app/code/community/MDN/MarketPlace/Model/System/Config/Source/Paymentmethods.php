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
class MDN_MarketPlace_Model_System_Config_Source_Paymentmethods extends Mage_Core_Model_Abstract{

    const XML_PATH_PAYMENT_METHODS = 'payment';
    
    /**
     * get all options
     * 
     * @return array 
     */
    public function getAllOptions(){
        
        if(!$this->_options){

            $payment_methods = $this->getPaymentMethods();
            
            foreach($payment_methods as $method){
                $tmp[$method->getcode()] = $method->getcode();
            }

            $this->_options = $tmp;
        }

        return $this->_options;
    }

    /**
     * To option array
     * 
     * @return array 
     */
    public function toOptionArray(){
        return $this->getAllOptions();
    }

    /**
     * ge tpayment methods
     * 
     * @return array $res 
     */
    public function getPaymentMethods(){

        $methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, null);
        $res = array();

        foreach ($methods as $code => $methodConfig) {
            $prefix = self::XML_PATH_PAYMENT_METHODS.'/'.$code.'/';

            if (!$model = Mage::getStoreConfig($prefix.'model', null)) {
                continue;
            }

            $res[] = Mage::getModel($model);
        }

        return $res;
    }

}
