<?php

/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */
class Mageshops_Rakuten_Model_Order extends Mageshops_Rakuten_Model_Abstract
{

    const SHIPPING_METHOD = 'nnrakuten_nnrakuten';
    const PAYMENT_METHOD = 'nnrakuten';

    static $_rakutenCarriers = null;

    public function massSyncOrderFromRakuten(array $ids)
    {
        if (empty($rakutenOrderIds) || !is_array($ids)) {
            Mage::throwException($this->__('Invalid Order ID List.'));
        }

        foreach ($ids as $id) {
            $this->syncOrderFromRakuten($id);
        }

        return $this;
    }

    public function syncOrderFromRakuten($rakutenOrderId)
    {
        $helper = Mage::helper('rakuten');
        $orderId = null;

        $rakutenOrder = Mage::getModel('rakuten/rakuten_order')->load($rakutenOrderId);
        // echo "<pre>"; print_r($rakutenOrder); exit();
        if (!$rakutenOrder) {
            throw new Exception($helper->__('Cannot find order %s', $rakutenOrderId));
        }

        $order = Mage::getModel('sales/order')->load($rakutenOrder->getMagentoIncrementId(), 'increment_id');

        if (!$order->getId()) {
            $quote = $this->_prepareQuote($rakutenOrder);
            $order = $this->_createOrder($quote);
            $this->_updateOrderTotals($order, $quote, $rakutenOrder);
            $quote->setIsActive(false)->save();
        }

        $this->_updateOrderState($order, $rakutenOrder->getStatus());
        $orderId = $order->getIncrementId();

        $rakutenOrder->setMagentoIncrementId($orderId);
        $rakutenOrder->save();

        return $orderId;
    }

    private function _prepareQuote(Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        $customer = $this->_setCustomer($rakutenOrder);
        $storeId = $customer->getStore();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote');
        $quote->assignCustomer($customer);
        $quote->setStore($storeId);
        $quote->save();

        // addresses
        $quote = $this->_setBillingAddress($quote, $rakutenOrder);
        $this->_setShippingAddress($quote, $rakutenOrder);

        Mage::getSingleton('tax/calculation')->setCustomer($quote->getCustomer());

        // add products to quote
        $this->_addItemsToQuote($quote, $rakutenOrder);

        // coupon code
        if (!empty($couponCode)) {
            $quote->setCouponCode($couponCode);
        }


        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getPayment()->importData(array('method' => self::PAYMENT_METHOD));

        $quote->setTotalsCollectedFlag(false)->collectTotals();

        $this->_updateQuoteTotals($quote, $rakutenOrder);

        $quote->setMageshopsRakutenFlag(true);
        $quote->save();
        $quote->setIsActive(0);

        return $quote;
    }

    private function _setCustomer(Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        $customer = Mage::getModel('customer/customer');
        $store = Mage::getModel('core/store')->load(Mage::helper('rakuten')->getRakutenOrderStore());
        $websiteId = $store->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($rakutenOrder->getEmail());

        if (!$customer->getId()) {
            $customer->setWebsiteId($websiteId);
            $customer->setStore($store);
            $customer->setData('prefix', $rakutenOrder->getGender());
            $customer->setData('firstname', $rakutenOrder->getFirstName());
            $customer->setData('lastname', $rakutenOrder->getLastName());
            $customer->setData('email', $rakutenOrder->getEmail());

            $customer->setConfirmation(null);
            $customer->save();

            $customer->loadByEmail($rakutenOrder->getEmail());
        }


        // Customer Address

        $address = Mage::getModel("customer/address");
        $address->setCustomerId($customer->getId());
        $address->setData('prefix', $rakutenOrder->getGender());
        $address->setData('firstname', $rakutenOrder->getFirstName());
        $address->setData('lastname', $rakutenOrder->getLastName());
        $address->setData('company', $rakutenOrder->getCompany());
        $address->setData('street', array(
            '0' => $rakutenOrder->getStreet() . ' ' . $rakutenOrder->getStreetNo(),
            '1' => $rakutenOrder->getAddressAdd(),
        ));
        $address->setData('postcode', $rakutenOrder->getZipCode());
        $address->setData('city', $rakutenOrder->getCity());
        $address->setData('country_id', $rakutenOrder->getCountry());
        $address->setData('telephone', $rakutenOrder->getPhone());
        $address->implodeStreetAddress();
        $address->setShippingMethod(self::SHIPPING_METHOD);
        $address->setCollectShippingRates(true);
        $address->setShouldIgnoreValidation(true);
        $address->setIsDefaultBilling('1');
       // $address->setIsDefaultShipping('1');
        $address->setSaveInAddressBook('1');
        //echo  $rakutenOrder->getZipCode(); exit();
         try{
            $address->save();
        }
        catch (Exception $e) {
            Mage::logException($e);
             Mage::getSingleton('adminhtml/session')->addSuccess($e->getMessage());
        }

        // Shipping Address

        $address->setData('prefix', $rakutenOrder->getDeliveryGender());
        $address->setData('firstname', $rakutenOrder->getDeliveryFirstName());
        $address->setData('lastname', $rakutenOrder->getDeliveryLastName());
        $address->setData('company', $rakutenOrder->getDeliveryCompany());
        $address->setData('street', array(
            '0' => $rakutenOrder->getDeliveryStreet() . ' ' . $rakutenOrder->getDeliveryStreetNo(),
            '1' => $rakutenOrder->getDeliveryAddressAdd(),
        ));
        $address->setData('postcode', $rakutenOrder->getDeliveryZipCode());
        $address->setData('city', $rakutenOrder->getDeliveryCity());
        $address->setData('country_id', $rakutenOrder->getDeliveryCountry());
        $address->setData('telephone', $rakutenOrder->getPhone());
        $address->setData('region_id', -1);
        $address->implodeStreetAddress();

        $address->setShippingAmount($rakutenOrder->getShipping());

        $address->setShippingMethod(self::SHIPPING_METHOD);
        $address->setCollectShippingRates(true);
        $address->setShouldIgnoreValidation(true);
        $address->setIsDefaultShipping('1');
        $address->setSaveInAddressBook('1');

        try{
            $address->save();
        }
        catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }
    

        return $customer;
    }

    private function _setBillingAddress($quote, Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        $address = $quote->getBillingAddress();

        $address->setData('prefix', $rakutenOrder->getGender());
        $address->setData('firstname', $rakutenOrder->getFirstName());
        $address->setData('lastname', $rakutenOrder->getLastName());
        $address->setData('company', $rakutenOrder->getCompany());
        $address->setData('street', array(
            '0' => $rakutenOrder->getStreet() . ' ' . $rakutenOrder->getStreetNo(),
            '1' => $rakutenOrder->getAddressAdd(),
        ));
        $address->setData('postcode', $rakutenOrder->getZipCode());
        $address->setData('city', $rakutenOrder->getCity());
        $address->setData('country_id', $rakutenOrder->getCountry());
        $address->setData('telephone', $rakutenOrder->getPhone());
        $address->implodeStreetAddress();

        $address->setShippingMethod(self::SHIPPING_METHOD);
        $address->setCollectShippingRates(true);
        $address->setShouldIgnoreValidation(true);

        return $quote;
    }

    private function _setShippingAddress(Mage_Sales_Model_Quote $quote, Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        $address = $quote->getShippingAddress();

        $address->setData('prefix', $rakutenOrder->getDeliveryGender());
        $address->setData('firstname', $rakutenOrder->getDeliveryFirstName());
        $address->setData('lastname', $rakutenOrder->getDeliveryLastName());
        $address->setData('company', $rakutenOrder->getDeliveryCompany());
        $address->setData('street', array(
            '0' => $rakutenOrder->getDeliveryStreet() . ' ' . $rakutenOrder->getDeliveryStreetNo(),
            '1' => $rakutenOrder->getDeliveryAddressAdd(),
        ));
        $address->setData('postcode', $rakutenOrder->getDeliveryZipCode());
        $address->setData('city', $rakutenOrder->getDeliveryCity());
        $address->setData('country_id', $rakutenOrder->getDeliveryCountry());
        $address->setData('telephone', $rakutenOrder->getPhone());
        $address->setData('region_id', -1);
        $address->implodeStreetAddress();

        $address->setShippingAmount($rakutenOrder->getShipping());

        $address->setShippingMethod(self::SHIPPING_METHOD);
        $address->setCollectShippingRates(true);
        $address->setShouldIgnoreValidation(true);

        return $quote;
    }

    /**
     * Update quate with rakuten order items
     *
     * @param Mage_Sales_Model_Quote            $quote
     * @param Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder
     * @return $this
     */
    private function _addItemsToQuote(Mage_Sales_Model_Quote $quote, Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        foreach ($rakutenOrder->getOrderItems() as $item) {
            if (preg_match('#^' . Mage::helper('rakuten')->getBundledPrefix() . '#', $item->getProductId()) === 1) {
                $skuArray = explode('|', $item->getProductId());
                unset($skuArray[0]);
                foreach ($skuArray as $sku) {
                    $this->_addItemToQuote($sku, $item, $quote, true, count($skuArray));
                }
            } else {
                // Clear items cache each time when adding product
                $this->_clearQuoteItemsCache($quote);

                $this->_addItemToQuote($item->getProductId(), $item, $quote);
            }
        }

        return $this;
    }

    /**
     * Adds single item to quate
     *
     * @param Mage_Sales_Model_Quote            $quote
     * @@param $item
     * @param  $sku
     * @param $bundle
     */
    private function _addItemToQuote($sku, $item, Mage_Sales_Model_Quote $quote, $bundle = false, $count = 0)
    {
        $product = Mage::getModel('catalog/product');
        $productId = $product->getIdBySku($sku);
        if ($productId) {

            // Product exists in magento use it
            $product = $product->load($productId);
            $product->setSkipCheckRequiredOption(true);

            $result = $quote->addProduct($product);
            if (is_string($result)) {
                throw new Exception($result);
            }

            $quoteItem = $quote->getItemByProduct($product);
        } else {

            // Product is not in store, so create new item for this quote
            $product->setSku($item->getProductId());
            $product->setName($item->getName());
            $product->setTypeId('simple');
            $product->setPrice($item->getPrice());

            $quoteItem = Mage::getModel('sales/quote_item');
            $quoteItem->setProduct($product);

            $quote->addItem($quoteItem);
        }

        if ($quoteItem !== false) {
            $quoteItem->setOriginalCustomPrice($item->getPrice());
            $quoteItem->setQty($item->getQty());
            $quoteItem->setRakutenTaxIdx($item->getTax());
        }

        if ($bundle == true) {
            $quoteItem->setOriginalCustomPrice($product->getPrice());
            $quoteItem->setQty($item->getQty());
            $quoteItem->setRakutenTaxIdx($item->getTax() / $count);
        }

        $product->unsSkipCheckRequiredOption();
        $quoteItem->checkData();
    }

    private function _clearQuoteItemsCache($quote)
    {
        /** @var $address Mage_Sales_Model_Quote_Address */
        foreach ($quote->getAllAddresses() as $address) {
            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonominal');
        }

        return $this;
    }

    /**
     * Update totals with rakuten tax and subtotal values
     *
     * @param Mage_Sales_Model_Quote            $quote
     * @param Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder
     * @return $this
     */
    protected function _updateQuoteTotals(Mage_Sales_Model_Quote $quote, Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        $helper = Mage::helper('rakuten');
        $taxHelper = Mage::helper('tax');
        $dirHelper = Mage::helper('directory');
        $calculator = Mage::getSingleton('tax/calculation');

        $baseCurrency = Mage::app()->getStore($helper->getRakutenOrderStore())->getBaseCurrencyCode();
        $currency = Mage::app()->getStore($helper->getRakutenOrderStore())->getCurrentCurrencyCode();

        /** @var $quoteItem Mage_Sales_Model_Quote_Item */
        foreach ($quote->getAllItems() as $quoteItem) {
            // Rakuten price is always with tax
            $price = (float)$quoteItem->getPrice();
            $rowTotal = $price * $quoteItem->getQty();

            $taxPercent = (float)$helper->getTaxPercentFromRakutenIdx($quoteItem->getRakutenTaxIdx());
            $taxAmount = $calculator->calcTaxAmount($rowTotal, $taxPercent, true, false);

            $quoteItem->setBasePriceInclTax($price);
            $quoteItem->setPriceInclPrice($dirHelper->currencyConvert($price, $baseCurrency, $currency));

            // Set Tax Percent column in order view
            $quoteItem->setTaxPercent($taxPercent);

            // Sets price
            $quoteItem->setBaseRowTotalInclTax($rowTotal);
            $quoteItem->setRowTotalInclTax($dirHelper->currencyConvert($rowTotal, $baseCurrency, $currency));
            
            // If in shop isset price with tax
            if ($taxHelper->priceIncludesTax($quote->getStore())) {
                $quoteItem->setBasePrice($price);
                $quoteItem->setPrice($dirHelper->currencyConvert($price, $baseCurrency, $currency));
                $quoteItem->setBaseRowTotal($rowTotal);
                $quoteItem->setRowTotal($dirHelper->currencyConvert($rowTotal, $baseCurrency, $currency));
            } else {
                // Calculate tax amount per product
                $itemTax = $calculator->calcTaxAmount($price, $taxPercent, false);
                // Set price per product without tax
                $quoteItem->setBasePrice($price - $itemTax);
                $quoteItem->setPrice($dirHelper->currencyConvert($price - $itemTax, $baseCurrency, $currency));
                $quoteItem->setBaseRowTotal($rowTotal - $taxAmount);
                $quoteItem->setRowTotal($dirHelper->currencyConvert($rowTotal - $taxAmount, $baseCurrency, $currency));
            }
        }

        return $this;
    }

    /**
     * Update totals with rakuten tax and subtotal values
     *
     * @param Mage_Sales_Model_Order            $order
     * @param Mage_Sales_Model_Quote            $quote
     * @param Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder
     * @return $this
     */
    protected function _updateOrderTotals(Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote, Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder)
    {
        $helper = Mage::helper('rakuten');
        $taxHelper = Mage::helper('tax');
        $dirHelper = Mage::helper('directory');

        $baseCurrency = Mage::app()->getStore($helper->getRakutenOrderStore())->getBaseCurrencyCode();
        $currency = Mage::app()->getStore($helper->getRakutenOrderStore())->getCurrentCurrencyCode();

        $baseSubtotal = 0.0;
        $subtotal = 0.0;

        $baseTaxAmount = 0.0;
        $taxAmount = 0.0;

        $taxes = array();
        $baseTaxes = array();

        /** @var $quoteItem Mage_Sales_Model_Quote_Item */
        foreach ($quote->getAllItems() as $quoteItem) {
            $baseTaxAmount += $quoteItem->getData('base_tax_amount');
            $taxAmount += $quoteItem->getData('tax_amount');

            $baseSubtotal += $quoteItem->getBaseRowTotal();
            $subtotal += $quoteItem->getRowTotal();

            $taxPercent = sprintf('%01.1F', $quoteItem->getTaxPercent());
            if (!isset($taxes[$taxPercent])) {
                $taxes[$taxPercent] = 0.0;
                $baseTaxes[$taxPercent] = 0.0;
            }
            $taxes[$taxPercent] += $quoteItem->getData('tax_amount');
            $baseTaxes[$taxPercent] += $quoteItem->getData('base_tax_amount');
        }

        $order->setBaseSubtotal($baseSubtotal);
        $order->setSubtotal($subtotal);

        if ($taxHelper->displaySalesSubtotalExclTax($quote->getStore())) {
            $order->setBaseSubtotalInclTax($baseSubtotal);
            $order->setSubtotalInclTax($subtotal);     
        } else {
            $order->setBaseSubtotalInclTax($baseSubtotal + $baseTaxAmount);
            $order->setSubtotalInclTax($subtotal + $taxAmount);
        }
       
        $shipping = $rakutenOrder->getShipping();
        $shippingTax = $quote->getShippingAddress()->getShippingTaxAmount();
        $taxAmount += $shippingTax;

        if ($shipping > 0.0) {
            $taxPercent = sprintf('%01.1F', round($shippingTax * 100.0 / $shipping, 1));
            if (!isset($taxes[$taxPercent])) {
                $taxes[$taxPercent] = 0.0;
                $baseTaxes[$taxPercent] = 0.0;
            }
            $taxes[$taxPercent] += $shippingTax;
            $baseTaxes[$taxPercent] += $shippingTax;
        }
        
        if ($taxHelper->shippingPriceIncludesTax($quote->getStore())) {
            $order->setBaseShippingInclTax($shipping);
            $order->setShippingInclTax($dirHelper->currencyConvert($shipping, $baseCurrency, $currency));
        } else {
            $order->setBaseShippingAmount($shipping - $shippingTax);
            $order->setShippingAmount($dirHelper->currencyConvert($shipping - $shippingTax, $baseCurrency, $currency));

            $order->setBaseShippingTaxAmount($shippingTax);
            $order->setShippingTaxAmount($dirHelper->currencyConvert($shippingTax, $baseCurrency, $currency));

            $order->setBaseShippingInclTax($shipping);
            $order->setShippingInclTax($dirHelper->currencyConvert($shipping, $baseCurrency, $currency));            
        }

        $order->setBaseTaxAmount($taxAmount);
        $order->setTaxAmount($dirHelper->currencyConvert($taxAmount, $baseCurrency, $currency));

        $discount = (float)$rakutenOrder->getCouponTotal();

        $order->setBaseDiscountAmount(-$discount);
        $order->setDiscountAmount($dirHelper->currencyConvert(-$discount, $baseCurrency, $currency));
        
        $grandTotal = (float)$rakutenOrder->getTotal();

        $order->setBaseGrandTotal($grandTotal);
        $order->setGrandTotal($dirHelper->currencyConvert($grandTotal, $baseCurrency, $currency));

        $order->save();


        $rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order);
        foreach ($rates as $rate) {
            $taxPercent = sprintf('%01.1F', $rate->getPercent());
            if ($taxes[$taxPercent] != $rate->getAmount()) {
                $rate->setAmount($taxes[$taxPercent]);
                $rate->setBaseAmount($baseTaxes[$taxPercent]);
                $rate->save();
            }
        }

        return $this;
    }

    private function _createOrder(Mage_Sales_Model_Quote $quote)
    {
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        return $service->getOrder();
    }

    private function _updateOrderState(Mage_Sales_Model_Order $order, $rakutenState)
    {
        switch ($rakutenState) {
            case 'pending':
                break;
            case 'editable':
                break;
            case 'payout':
                $this->_createInvoice($order);
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                break;
            case 'shipped':
                if ($order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $this->_createInvoice($order)->_createShipment($order);
                }
                break;
            case 'cancelled':
                $order->cancel();
                break;
        }

        return $this;
    }

    private function _createInvoice(Mage_Sales_Model_Order $order)
    {
        if (!$order->hasInvoices()) {
            $invoice = $order->prepareInvoice()
                ->setTransactionId($order->getId())
                ->addComment('Invoice for Rakuten Order')
                ->register()
                ->pay();
            
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
        }

        return $this;
    }

    private function _createShipment(Mage_Sales_Model_Order $order)
    {
        if (!$order->hasShipments()) {
            $shipment = $order->prepareShipment();
            if ($shipment) {
                $shipment->register();
                $order->setIsInProcess(true);

                Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
            }
        }

        return $this;
    }

    public function syncRakutenOrders($newOnly = true)
    {
        $helper = Mage::helper('rakuten');
        $rakuten_request = $helper->getUrl('getOrders');

        $page = 1;

        $params = $helper->getRequestParams();

        if ($newOnly) {
            $lastOrder = Mage::getModel('rakuten/rakuten_order')->getCollection()
                ->addFieldToSelect('created')
                ->setOrder('created')
                ->getFirstItem();

            if ($lastOrder->getId()) {
                $params['from'] = $lastOrder->getCreated();
            }
        }

        do {
            $params['page'] = $page;
            $request = $helper->callAPI($rakuten_request, $params);

            $pages = $this->_saveRakutenOrders($request);

            $page++;
        } while ($page <= $pages);

        return $this;
    }

    private function _saveRakutenOrders($request)
    {
        $rakuten = simplexml_load_string($request->getAnswer());
        $pages = 0;

        $success = $rakuten->success;
        if ($success) {

            $pages = (int)$rakuten->orders->paging->pages;

            foreach ($rakuten->orders->order as $order) {

                $orderNo = (string)$order->order_no;
                $rOrder = Mage::getModel('rakuten/rakuten_order')->loadByOrderNo($orderNo);

                $rOrder->setData('order_no', $orderNo);
                $rOrder->setData('total', (float)$order->total);
                $rOrder->setData('shipping', (float)$order->shipping);
                $rOrder->setData('max_shipping_date', (string)$order->max_shipping_date);
                $rOrder->setData('payment', (string)$order->payment);
                $rOrder->setData('status', (string)$order->status);
                $rOrder->setData('invoice_no', (string)$order->invoice_no);
                $rOrder->setData('comment_client', (string)$order->comment_client);
                $rOrder->setData('comment_merchant', (string)$order->comment_merchant);
                $rOrder->setData('created', (string)$order->created);

                $rOrder->setData('gender', (string)$order->client->gender);
                $rOrder->setData('first_name', (string)$order->client->first_name);
                $rOrder->setData('last_name', (string)$order->client->last_name);
                $rOrder->setData('company', (string)$order->client->company);
                $rOrder->setData('street', (string)$order->client->street);
                $rOrder->setData('street_no', (string)$order->client->street_no);
                $rOrder->setData('address_add', (string)$order->client->address_add);
                $rOrder->setData('zip_code', (int)$order->client->zip_code);
                $rOrder->setData('city', (string)$order->client->city);
                $rOrder->setData('country', (string)$order->client->country);
                $rOrder->setData('email', (string)$order->client->email);
                $rOrder->setData('phone', (string)$order->client->phone);

                $rOrder->setData('delivery_gender', (string)$order->delivery_address->gender);
                $rOrder->setData('delivery_first_name', (string)$order->delivery_address->first_name);
                $rOrder->setData('delivery_last_name', (string)$order->delivery_address->last_name);
                $rOrder->setData('delivery_company', (string)$order->delivery_address->company);
                $rOrder->setData('delivery_street', (string)$order->delivery_address->street);
                $rOrder->setData('delivery_street_no', (string)$order->delivery_address->street_no);
                $rOrder->setData('delivery_address_add', (string)$order->delivery_address->address_add);
                $rOrder->setData('delivery_zip_code', (int)$order->delivery_address->zip_code);
                $rOrder->setData('delivery_city', (string)$order->delivery_address->city);
                $rOrder->setData('delivery_country', (string)$order->delivery_address->country);

                $rOrder->setData('coupon_id', (int)$order->coupon->coupon_id);
                $rOrder->setData('coupon_total', (float)$order->coupon->total);
                $rOrder->setData('coupon_code', (string)$order->coupon->code);
                $rOrder->setData('coupon_comment', (string)$order->coupon->comment);

                $rOrder->save();

                $orderId = $rOrder->getId();

                foreach ($order->items->item as $item) {
                    $itemId = (int)$item->item_id;
                    $rItem = Mage::getModel('rakuten/rakuten_order_item')->load($itemId);

                    $rItem->setData('item_id', $itemId);
                    $rItem->setData('product_id', (int)$item->product_id);
                    $rItem->setData('variant_id', (int)$item->variant_id);
                    $rItem->setData('product_art_no', (string)$item->product_art_no);
                    $rItem->setData('name', (string)$item->name);
                    $rItem->setData('name_add', (string)$item->name_add);
                    $rItem->setData('qty', (int)$item->qty);
                    $rItem->setData('price', (float)$item->price);
                    $rItem->setData('price_sum', (float)$item->price_sum);
                    $rItem->setData('tax', (int)$item->tax);

                    $rItem->setData('rakuten_order_id', $orderId);

                    $rItem->save();
                }
            }
        } elseif ($success < 0) {
            $rakuten->setStatus(Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR_ORDER)->save();
        }

        return $pages;
    }

    public function exportOrderToRakuten($rakutenOrderId)
    {
        $helper = Mage::helper('rakuten');
        $orderId = null;

        $rakutenOrder = Mage::getModel('rakuten/rakuten_order')->load($rakutenOrderId);

        if (!$rakutenOrder) {
            throw new Exception($helper->__('Can not find order %s', $rakutenOrderId));
        }

        $order = Mage::getModel('sales/order')->load($rakutenOrder->getMagentoIncrementId(), 'increment_id');

        if (!$order->getId()) {
            throw new Exception($helper->__('Can not find corresponding magento order for %s', $rakutenOrderId));
        }

        $state = $order->getState();

        if ($state == Mage_Sales_Model_Order::STATE_COMPLETE && $rakutenOrder->getStatus != 'shipped') {
            $this->_exportOrderStatus($rakutenOrder, 'shipped', $order);
        }

        if ($state == Mage_Sales_Model_Order::STATE_CANCELED && $rakutenOrder->getStatus != 'cancelled') {
            $this->_exportOrderStatus($rakutenOrder, 'cancelled');
        }

        return $this;
    }

    private function _exportOrderStatus(Mageshops_Rakuten_Model_Rakuten_Order $rakutenOrder, $status, $order = null)
    {
        $helper = Mage::helper('rakuten');

        $params = $helper->getRequestParams();
        $params['order_no'] = $rakutenOrder->getOrderNo();

        switch ($status) {
            case 'shipped':
                $rakutenRequest = $helper->getUrl('setOrderShipped');
                if ($order !== null) {
                    $tracking = $this->_getTracking($order);
                    if ($tracking !== false) {
                        $params['carrier'] = $tracking['carrier'];
                        $params['tracking_number'] = $tracking['tracking_number'];
                    }
                }
                break;
            case 'cancelled':
                $rakutenRequest = $helper->getUrl('setOrderCancelled');
                break;
            default:
                Mage::throwException($helper->__('Unknown rakuten order status "%s"', $status));
                break;
        }

        $request = $helper->callAPI($rakutenRequest, $params);
        $helper->checkAnswer($request, $params, $rakutenRequest);

        $rakutenOrder->setStatus($status)->save();

        return $this;
    }

    private function _getTracking(Mage_Sales_Model_Order $order)
    {
        $tracking = array();

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();

        foreach ($shipmentCollection as $shipment) {

            foreach ($shipment->getAllTracks() as $track) {

                if ($title = $this->_getRakutenCarrier($track->getCarrierCode())) {

                    $tracking['carrier'] = $title;
                    $tracking['tracking_number'] = $track->getNumber();

                    return $tracking;
                }
            }
        }

        return false;
    }

    private function _getRakutenCarrier($code)
    {
        if (self::$_rakutenCarriers === null) {
            self::$_rakutenCarriers = array();
            self::$_rakutenCarriers[Mage::getStoreConfig('nn_market/rakuten_order/carrier_dhl')] = 'DHL';
            self::$_rakutenCarriers[Mage::getStoreConfig('nn_market/rakuten_order/carrier_hermes')] = 'Hermes';
            self::$_rakutenCarriers[Mage::getStoreConfig('nn_market/rakuten_order/carrier_ups')] = 'UPS';
            self::$_rakutenCarriers[Mage::getStoreConfig('nn_market/rakuten_order/carrier_dpd')] = 'DPD';
            self::$_rakutenCarriers[Mage::getStoreConfig('nn_market/rakuten_order/carrier_gls')] = 'GLS';
            self::$_rakutenCarriers[Mage::getStoreConfig('nn_market/rakuten_order/carrier_post')] = 'Post';
            unset(self::$_rakutenCarriers['']);
        }

        if (isset(self::$_rakutenCarriers[$code])) {
            return self::$_rakutenCarriers[$code];
        }

        return false;
    }

}


