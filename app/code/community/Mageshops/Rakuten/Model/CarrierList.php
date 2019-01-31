<?php
/**
 * @category    Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2014
 * @author      Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Model_CarrierList
{
    static $_carriers = null;

    public function toOptionArray($isMultiSelect = false)
    {
        if (self::$_carriers === null) {

            $methods = Mage::getSingleton('shipping/config')->getAllCarriers();

            self::$_carriers = array();

            foreach ($methods as $_code => $_method) {
                if(!$_title = Mage::getStoreConfig("carriers/$_code/title")) {
                    $_title = $_code;
                }
                self::$_carriers[] = array('value' => $_code, 'label' => $_title);
            }

            if (!$isMultiSelect) {
                array_unshift(self::$_carriers, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
            }
        }

        return self::$_carriers;
    }
}
