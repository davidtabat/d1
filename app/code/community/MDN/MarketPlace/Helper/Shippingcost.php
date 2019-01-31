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
class MDN_MarketPlace_Helper_Shippingcost extends Mage_Core_Helper_Abstract {

    const kDelayTnt = '1';
    const kDelayColissimo = '2';

    /**
     * Calculate shipping cost
     *
     * @param Varien_Object $product
     * @param boolean $addTax
     * @return float
     */
    public function calculateShippingCost($_product, $addTax, $carrierCode = null) {

        // must load product, if not, error when trying to setStockItem...
        $product = Mage::getModel('catalog/product')->load($_product->getentity_id());

        $country = Mage::registry('mp_country')->getmpac_country_code();
        $retour = false;

        $storeId = Mage::registry('mp_country')->getParam('store_id');
        $store = Mage::getModel('core/store')->load($storeId);
        $websiteId = $store->getwebsite_id();

        if ($carrierCode == null)
            $carrierCode = Mage::registry('mp_country')->getParam('default_shipment_method');

        $code = explode("_", $carrierCode);

        $carrierObject = $this->getCarrierFromCode($code[0]);

        if ($carrierObject) {

            try {
                $request = Mage::getModel('shipping/rate_request');

                //force stock item load (ressource consumer but no choice regarding the way magento implements cost calculation)
                $product->setStockItem(mage::getModel('cataloginventory/stock_item')->loadByProduct($product));

                //set request item (build a quote)
                $quote = mage::getModel('sales/quote');
                $quoteItem = mage::getModel('sales/quote_item');
                $quoteItem->setQuote($quote);
                $quoteItem->setProduct($product);
                $quoteItem->setWeight($product->getweight());
                $quoteItem->setQty(1);
                $quote->addItem($quoteItem);

                //set request
                $request->setAllItems($quote->getAllItems());
                $request->setPackageValue($product->getFinalPrice());
                $request->setPackageValueWithDiscount($product->getFinalPrice());
                $request->setPackagePhysicalValue($product->getFinalPrice());
                $request->setDestCountryId($country);
                $request->setPackageWeight($product->getweight());
                $request->setPackageQty(1);
                $request->setFreeMethodWeight(0);
                $request->setFreeShipping(0);
                $request->setStoreId($storeId);
                $request->setWebsiteId($websiteId);

                // collect rates may fail for custom shipping methods
                $rates = $carrierObject->collectRates($request);
                //try to retrieve rate using carrier code
                $cheaperRate = null;
                foreach($rates->getAllRates() as $rate)
                {
                    if ($rate->getcarrier().'_'.$rate->getmethod() == $carrierCode)
                    {
                        $cheaperRate = $rate;
                    }
                }

                if ($cheaperRate == null)
                {
                    if($rates){
                        $cheaperRate = $rates->getCheapestRate();
                    } else {
                        $cheaperRate = null;
                    }
                }

                if ($cheaperRate !== null && !$cheaperRate->getError()) {
                    $retour = $cheaperRate->getPrice();
                    if ($addTax === true) {
                        $tax = Mage::Helper('MarketPlace/Taxes')->getTaxRate();
                        $retour = $retour * ( 1 + $tax / 100);
                    }
                }
            } catch (Exception $ex) {
                throw new Exception('Unable to collect shipping cost for product ' . $product->getName() . ':' . $ex->getMessage());
            }
        }
        else
            throw new Exception('Unable to find carrier with code = ' . $code[0], 17);

        // dispatch event for updating shipping price
        $_product->setData('shipping_to_export', $retour);
        Mage::dispatchEvent('marketplace_before_export_shipping', array('product'=>$_product));
        $retour = $_product->getData('shipping_to_export');

        return $retour;
    }

    /**
     *  Return carrier model from carrier code
     *
     * @param string
     * @return Model
     *
     */
    public function getCarrierFromCode($CarrierCode) {

        $config = Mage::getStoreConfig('carriers');
        foreach ($config as $code => $methodConfig) {
            if (Mage::getStoreConfigFlag('carriers/' . $code . '/active')) {

                if ($code == $CarrierCode) {

                    if (isset($methodConfig['model'])) {
                        $modelName = $methodConfig['model'];
                        $Model = Mage::getModel($modelName);
                        return $Model;
                    }
                }
            }
        }
    }


}
