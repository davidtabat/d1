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

class MDN_Cdiscount_Helper_Package_Offers extends MDN_Cdiscount_Helper_Package_Abstract {

    protected $_type = 'offers';
    protected $_matchingEan = false;
    protected $_lastSentProduct = null;

    /**
     * Setter matchingEan
     *
     * @param boolean $value
     */
    public function setMatchingEan($value){
        if(is_bool($value))
            $this->_matchingEan = $value;
    }

    /**
     * Getter matchingEan
     *
     * @return boolean
     */
    public function getMatchingEan(){
        return $this->_matchingEan;
    }

    /**
     * Build XML offers file
     */
    protected function _buildXML(){

        $this->log('========================================================');
        $this->log('Start export');

        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        if (!$account->getParam('seller_product_reference')) {
            throw new Exception('Please select seller product reference in accounts configuration');
        }

        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration('cdiscount');
        $disableBarcode = $config->getdisable_barcode();
        $this->log('Disable barcode: '.($disableBarcode ? 1 : 0));

        $content = '';

        $xml = new DomDocument();
        $xml->formatOutput = true;

        $offerPackageNode = $xml->createElement('OfferPackage', '');
        $xml->appendChild($offerPackageNode);

        $name = $xml->createAttribute('Name');
        $name->appendChild($xml->createTextNode($this->getPackageId()));
        $offerPackageNode->appendChild($name);

        $purgeAndReplace = $xml->createAttribute('PurgeAndReplace');
        $purgeAndReplace->appendChild($xml->createTextNode('false'));
        $offerPackageNode->appendChild($purgeAndReplace);

        $xmlns = $xml->createAttribute('xmlns');
        $xmlns->appendChild($xml->createTextNode("clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration"));
        $offerPackageNode->appendChild($xmlns);

        $xmlnsx = $xml->createAttribute('xmlns:x');
        $xmlnsx->appendChild($xml->createTextNode("http://schemas.microsoft.com/winfx/2006/xaml"));
        $offerPackageNode->appendChild($xmlnsx);

        $offersNode = $xml->createElement('OfferPackage.Offers','');
        $offerPackageNode->appendChild($offersNode);

        $offerCollectionNode = $xml->createElement('OfferCollection');
        /*$capacity = $xml->createAttribute('Capacity');
        $capacity->appendChild($xml->createTextNode(count($this->_getProducts())));
        $offerCollectionNode->appendChild($capacity);*/

        $offersNode->appendChild($offerCollectionNode);

        $products = $this->_getProducts();
        $this->log(count($products).' offers to export');

        foreach($products as $product){

            $offerNode = $xml->createElement('Offer');

            $deaValue = Mage::Helper('Cdiscount/Ecopart')->getDeaValueForProduct($product);
            $dea = $xml->createAttribute('DeaTax');
            $dea->appendChild($xml->createTextNode($deaValue));
            $offerNode->appendChild($dea);
            
            $eco = $xml->createAttribute('EcoPart');
            $ecoPartValue = Mage::Helper('Cdiscount/Ecopart')->getValueForProduct($product);
            $eco->appendChild($xml->createTextNode($ecoPartValue));
            $offerNode->appendChild($eco);

            $delay = ($this->_matchingEan === true) ? $country->getParam('default_shipment_delay') : Mage::Helper('MarketPlace/Product')->getDelayToExport($product);
            $maxDeliveryTime = $xml->createAttribute('MaxDeliveryTime');
            $maxDeliveryTime->appendChild($xml->createTextNode($delay));
            $offerNode->appendChild($maxDeliveryTime);

            $minDeliveryTime = $xml->createAttribute('MinDeliveryTime');
            $minDeliveryTime->appendChild($xml->createTextNode($delay));
            $offerNode->appendChild($minDeliveryTime);

            $shipping_cost = ($this->_matchingEan === true) ? 0 : round(Mage::Helper('MarketPlace/Shippingcost')->calculateShippingCost($product, false),2);
            $normalShippingPrice = $xml->createAttribute('NormalShippingPrice');
            $normalShippingPrice->appendChild($xml->createTextNode($shipping_cost));
            $offerNode->appendChild($normalShippingPrice);

            $priceToExport = ($this->_matchingEan === true) ? round(str_replace(',','.',$product['price']), 2) : round(str_replace(',','.',Mage::Helper('MarketPlace/Product')->getPriceToExport($product)), 2);
            $priceToExport -= $ecoPartValue;
            if($priceToExport <= 0)
            {
                $this->log('Skip product #'.$product['sku'].', price to export is 0');
                continue;
            }

            $price = $xml->createAttribute('Price');
            $price->appendChild($xml->createTextNode($priceToExport));
            $offerNode->appendChild($price);

            $condition = $xml->createAttribute('ProductCondition');
            $condition->appendChild($xml->createTextNode(6)); //neuf
            $offerNode->appendChild($condition);


            $barcode = ($this->_matchingEan === true) ? $product['ean'] : Mage::helper('MarketPlace/Barcode')->getBarcodeForProduct($product);

            if ($this->getMatchingEan())
            {
                if (($barcode == "") && (!$disableBarcode))
                {
                    $this->log('Skip product #'.$product['sku'].', no barcode');
                    continue;
                }
            }
            else
            {
                if (($barcode == "") && (!$config->getremoveBarcodeForUpdate()))
                {
                    $this->log('Skip product #'.$product['sku'].', no barcode');
                    continue;
                }
            }

            $exportEan = true;
            if ($config->getremoveBarcodeForUpdate() && !$this->getMatchingEan())
                $exportEan = false;

            if ($exportEan)
            {
                $ean = $xml->createAttribute('ProductEan');
                $ean->appendChild($xml->createTextNode($barcode));
                $offerNode->appendChild($ean);
            }

            if (Mage::registry('mp_country')->getParam('registered_shipment_method'))
            {
                $registeredShippingCost = ($this->_matchingEan === true) ? 0 : round(Mage::Helper('MarketPlace/Shippingcost')->calculateShippingCost($product, false, Mage::registry('mp_country')->getParam('registered_shipment_method')),2);
                $registeredShippingPrice = $xml->createAttribute('RegisteredShippingPrice');
                $registeredShippingPrice->appendChild($xml->createTextNode($registeredShippingCost));
                $offerNode->appendChild($registeredShippingPrice);
            }

            $sellerProductId = '';
            if ($this->_matchingEan === true) {
                $sellerProductId = ($account->getParam('seller_product_reference') == 'sku') ? $product['sku'] : $product['id'];
            } else {
                $sellerProductId = ($account->getParam('seller_product_reference') == 'sku') ? $product->getsku() : $product->getentity_id();
            }
            $sku = $xml->createAttribute('SellerProductId');
            $sku->appendChild($xml->createTextNode($sellerProductId));
            $offerNode->appendChild($sku);
            
            $stock_value = 0;
            if ($this->_matchingEan === true) {

                $stock_value = $product['stock'];
                
            } else {

                $stock_value = Mage::Helper('MarketPlace/Product')->getStockToExport($product);

            }

            $stock = $xml->createAttribute('Stock');            
            $stock->appendChild($xml->createTextNode($stock_value));
            $offerNode->appendChild($stock);

            if (Mage::registry('mp_country')->getParam('tracking_shipment_method'))
            {
                $trackingShippingCost = ($this->_matchingEan === true) ? 0 : round(Mage::Helper('MarketPlace/Shippingcost')->calculateShippingCost($product, false, Mage::registry('mp_country')->getParam('tracking_shipment_method')),2);;
                $trackingShippingPrice = $xml->createAttribute('TrackingShippingPrice');
                $trackingShippingPrice->appendChild($xml->createTextNode($trackingShippingCost));
                $offerNode->appendChild($trackingShippingPrice);
            }

            $additionalNormalShippingPrice = $xml->createAttribute('AdditionalNormalShippingPrice');
            $additionalNormalShippingPrice->appendChild($xml->createTextNode($shipping_cost));
            $offerNode->appendChild($additionalNormalShippingPrice);

            if (Mage::registry('mp_country')->getParam('registered_shipment_method'))
            {
                $registeredShippingCost = ($this->_matchingEan === true) ? 0 : round(Mage::Helper('MarketPlace/Shippingcost')->calculateShippingCost($product, false, Mage::registry('mp_country')->getParam('registered_shipment_method')),2);
                $additionalRegisteredShippingPrice = $xml->createAttribute('AdditionalRegisteredShippingPrice');
                $additionalRegisteredShippingPrice->appendChild($xml->createTextNode($registeredShippingCost));
                $offerNode->appendChild($additionalRegisteredShippingPrice);
            }

            if (Mage::registry('mp_country')->getParam('tracking_shipment_method'))
            {
                $trackingShippingCost = ($this->_matchingEan === true) ? 0 : round(Mage::Helper('MarketPlace/Shippingcost')->calculateShippingCost($product, false, Mage::registry('mp_country')->getParam('tracking_shipment_method')),2);;
                $additionalTrackingShippingPrice = $xml->createAttribute('AdditionalTrackingShippingPrice');
                $additionalTrackingShippingPrice->appendChild($xml->createTextNode($trackingShippingCost));
                $offerNode->appendChild($additionalTrackingShippingPrice);
            }

            $vat = $xml->createAttribute('Vat');
            $vat->appendChild($xml->createTextNode(Mage::registry('mp_country')->getParam('taxes')));
            $offerNode->appendChild($vat);

            $offerCollectionNode->appendChild($offerNode);

            // save updated products
            if ($this->_matchingEan === false) {
                $this->_submitted[$product->getsku()] = array(
                    'stock' => $stock_value,
                    'price' => $priceToExport,
                    'delay' => $delay
                );

                $debug = 'stock: '.$stock_value.', price: '.$priceToExport.', delay: '.$delay.', shipping: '.$shipping_cost.', vat: '.$vat;
                $this->log('Product #'.$product['sku'].' updated : '.$debug);
            }else{
                $id = Mage::getModel('catalog/product')->getIdBySku($product['sku']);
                Mage::getModel('MarketPlace/Data')->updateStatus($id, Mage::registry('mp_country')->getId(), MDN_Cdiscount_Helper_ProductCreation::kStatusPending);
                $this->log('Update product #'.$product['sku'].', change status to pending');
            }

        }

        $content = $xml->saveXML($offerPackageNode);

        $this->_save($this->_getPackageTmpPath().'/Content/Offers.xml', $content);
        $this->log('Stream saved to '.$this->_getPackageTmpPath().'/Content/Offers.xml');
        
    }

    protected function log($msg)
    {
        Mage::log($msg, null, 'cdiscount_offers_export.log');
    }

}
