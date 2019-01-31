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
 * @todo : check that orders are ok (check magento reports)
 */

abstract class MDN_MarketPlace_Helper_Orders extends Mage_Core_Helper_Abstract {

    const kOrderAccepted = "Accepted";

    /**
     * Import orders manually from exported file
     *
     * @param string $path
     * @param string $file
     * @return string $debug
     *
     */
    public function importOrdersFromUploadedFile($path, $file) {

        $filePath = $path . $file;
        $lines = file($filePath);

        $ordersTab = $this->buildOrdersTab($lines);
        return $this->importMarketPlaceOrders($ordersTab);
    }

    /**
     * Import orders from marketplace
     *
     * @param array $ordersTab
     * @return string
     */
    public function importMarketPlaceOrders($ordersTab) {

        $debug = "";
        $nbImported = 0;
        $nbSkipped = 0;

        $successOrders = array();
        $errorOrders = array();
        $skippedOrders = array();
        $errorMessages = array();
        
        foreach ($ordersTab as $order) {

            if ($this->orderAlreadyImported($order['mpOrderId'])) {
                $nbSkipped += 1;
                $skippedOrders[] = $order['mpOrderId'];
                continue;
            }

            try
            {
                $new_order = $this->importOrder($order);

                if ($new_order != null) {
                    $nbImported += 1;
                    $successOrders[] = $order['mpOrderId'];
                } else {
                    $nbSkipped += 1;
                    $errorOrders[] = $order['mpOrderId'];
                }
            }
            catch(Exception $ex)
            {
                $nbSkipped += 1;
                $errorOrders[] = $order['mpOrderId'];
                $errorMessages[$order['mpOrderId']] = $ex->getMessage();
            }
        }

        $this->updateOrders($successOrders, $this->getOrderStatus(self::kOrderAccepted));
        $log = $this->setOrderImportationLog($successOrders, $skippedOrders, $errorOrders, $errorMessages);

        $debug .= Mage::Helper('MarketPlace')->__('%s lines to process', count($ordersTab));
        $debug .= '<br/>';
        $debug .= Mage::Helper('MarketPlace')->__('%s order(s) imported', $nbImported);
        $debug .= '<br/>';
        $debug .= Mage::Helper('MarketPlace')->__('%s order(s) skipped (already exists)', $nbSkipped);

        return $debug;
    }

    /**
     * Update orders
     * 
     * @param array $orders
     * @param string $status
     * @return int 0
     */
    protected function updateOrders($orders, $status){
        // to implement in subclass
        return 0;
    }

    /**
     * Get order status
     * 
     * @param string $status
     * @return string 
     */
    protected function getOrderStatus($status){
        return '';
    }

   /**
    * Retrieve country code
    *
    * @return string $stateCode
	*
	*/
    public function getStateCode($text)
    {
        $stateString = trim( strtolower($text) );
        $tabStateCode = array('AL','AK','AS','AZ','AR','AF','AA','AC','AE','AM','AP','CA','CO','CT','DE','DC','FM','FL','GA','GU','HI','ID','IL','IN','IA','KS','KY','LA','ME','MH','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','MP','OH','OK','OR','PW','PA','PR','RI','SC','SD','TN','TX','UT','VT','VI','VA','WA','WV','WI','WY');
        $tabStateString = array('alabama','alaska','american samoa','arizona','arkansas','armed forces africa','armed forces americas','armed forces canada','armed forces europe','armed forces middle east','armed forces pacific','california','colorado','connecticut','delaware','district of columbia','federated states of micronesia','florida','georgia','guam','hawaii','idaho','illinois','indiana','iowa','kansas','kentucky','louisiana','maine','marshall islands','maryland','massachusetts','michigan','minnesota','mississippi','missouri','montana','nebraska','nevada','new hampshire','new jersey','new mexico','new york','north carolina','north dakota','northern mariana islands','ohio','oklahoma','oregon','palau','pennsylvania','puerto rico','rhode island','south carolina','south dakota','tennessee','texas','utah','vermont','virgin islands','virginia','washington','west virginia','wisconsin','wyoming');

        $stateCode = str_replace($tabStateString, $tabStateCode, $stateString);

        return $stateCode;
    }

    /**
     * Order importation
     *
     * @param array $order
     * @return order
     */
    protected function importOrder($order) {

        $_orderWeight = 0;
        $_orderQtyOrdered = 0;
        
        // retrieve billing region id
        $billingState = $this->getStateCode($order['billing_adress']['state']);
        $regionModel = Mage::getModel('directory/region')->loadByCode($billingState, $order['billing_adress']['country']);
        $billingRegionId = $regionModel->getId();
        
        // retrieve shipping region id
        $shippingState = $this->getStateCode($order['shipping_adress']['state']);
        $regionModel = Mage::getModel('directory/region')->loadByCode($shippingState, $order['shipping_adress']['country']);
        $shippingRegionId = $regionModel->getId();
        
        $decrement_stock = (Mage::getStoreConfig('cataloginventory/options/can_subtract') == 1) ? true : false;

        // get taxRate
        $taxRate = Mage::registry('mp_country')->getParam('taxes');

        if ($taxRate == "") {
            throw new Exception($this->__('Tax rate attribute not set in Sales > MarketPlace > Configuration > <a href="'.Mage::Helper('adminhtml')->getUrl('MarketPlace/Configuration', array()).'">Accounts</a>'), 15);
        }

        $currency = Mage::registry('mp_country')->getParam('currency');
        if ($currency == "") {
            throw new Exception($this->__('Currency attribute not set in Sales > MarketPlace > Configuration > <a href="'.Mage::Helper('adminhtml')->getUrl('MarketPlace/Configuration', array()).'">Accounts</a>'), 15);
        }

        $payment_method = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_default_payment_method();
        if ($payment_method == "") {
            throw new Exception($this->__('Payment method attribute not set'), 15);
        }

        //create customer
        $customer = $this->createOrReturnCustomer($order);

        //create order
        $new_order = Mage::getModel('sales/order');
        $new_order->reset();
        $new_order->setcustomer_id($customer->getId());
        $new_order->setCustomerGroupId($customer->getGroupId());
        $new_order->setCustomerFirstname($customer->getFirstname());
        $new_order->setCustomerLastname($customer->getLastname());
        $new_order->setCustomerIsGuest(0);
        $new_order->setCustomerEmail($customer->getemail());
        $new_order->setcreated_at(now());

        $new_order->setStore_id(Mage::registry('mp_country')->getParam('store_id'));
        $new_order->setorder_currency_code($currency);
        $new_order->setbase_currency_code($currency);
        $new_order->setstore_currency_code($currency);
        $new_order->setglobal_currency_code($currency);
        $new_order->setstore_to_base_rate(1);

        //shipping address
        $shipping_address = Mage::getModel('sales/order_address');
        $shipping_address->setOrder($new_order);
        $shipping_address->setId(null);
        $shipping_address->setentity_type_id(12);
        
        $tShippingName = explode(' ', $order['shipping_adress']['firstname']);
        $shippingFirstname = $tShippingName[0];
        $shippingLastname = '';
        for ($i = 1; $i < count($tShippingName); $i++)
            $shippingLastname .= $tShippingName[$i] . ' ';
        
        $shipping_address->setfirstname($shippingFirstname);
        $shipping_address->setlastname($shippingLastname);

        $shipping_address->setStreet($order['shipping_adress']['street']);

        $shipping_address->setCity($order['shipping_adress']['city']);
        $shipping_address->setPostcode($order['shipping_adress']['zipCode']);
        $shipping_address->setcountry_id($order['shipping_adress']['country']);
        $shipping_address->setregion_id($shippingRegionId);
        $shipping_address->setEmail($customer->getEmail());
        $shipping_address->setTelephone($order['shipping_adress']['phone']);
        $shipping_address->setcomments($order['shipping_adress']['comments']);
        $company = (array_key_exists('company', $order['shipping_adress'])) ? $order['shipping_adress']['company'] : '';
        $shipping_address->setcompany($company);

        // building        
        $building = (array_key_exists('building', $order['shipping_adress'])) ? $order['shipping_adress']['building'] : '';
        $shipping_address->setbuilding($building);
        // appartment
        $appartment = (array_key_exists('appartment', $order['shipping_adress'])) ? $order['shipping_adress']['appartment'] : '';
        $shipping_address->setappartment($appartment);
        
        $new_order->setShippingAddress($shipping_address);

        //shipping address
        $billing_address = Mage::getModel('sales/order_address');
        $billing_address->setOrder($new_order);
        $billing_address->setId(null);

        // retrieve entity type id address, if current mage version does'nt use flat order then let it as null
        $entity_type_id_address = null;
        if (!Mage::helper('MarketPlace/FlatOrder')->isFlatOrder())
            $entity_type_id_address = Mage::getResourceModel("sales/order_address")->getTypeId();

        $billing_address->setentity_type_id($entity_type_id_address);
        
        $tBillingName = explode(' ', $order['billing_adress']['firstname']);
        $billingFirstname = $tBillingName[0];
        $billingLastname = '';
        for ($i = 1; $i < count($tBillingName); $i++)
            $billingLastname .= $tBillingName[$i] . ' ';
        
        $billing_address->setfirstname($billingFirstname);
        $billing_address->setlastname($billingLastname);
        
        $billing_address->setStreet($order['billing_adress']['street']);
        $billing_address->setCity($order['billing_adress']['city']);
        $billing_address->setPostcode($order['billing_adress']['zipCode']);
        $billing_address->setcountry_id($order['billing_adress']['country']);
        $billing_address->setregion_id($billingRegionId);
        $billing_address->setEmail($customer->getEmail());
        $billing_address->setTelephone($order['billing_adress']['phone']);
        $billing_address->setcomments($order['billing_adress']['comments']);
        $company = (array_key_exists('company', $order['billing_adress'])) ? $order['billing_adress']['company'] : '';
        $billing_address->setcompany($company);
        
        // building        
        $building = (array_key_exists('building', $order['billing_adress'])) ? $order['billing_adress']['building'] : '';
        $billing_address->setbuilding($building);
        // appartment
        $appartment = (array_key_exists('appartment', $order['billing_adress'])) ? $order['billing_adress']['appartment'] : '';
        $billing_address->setappartment($appartment);
        
        $new_order->setBillingAddress($billing_address);

        //Payment method
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod($payment_method);
        $new_order->setPayment($payment);

        //shipping method
        $shippingTaxAmount = $order['shipping_tax'];
        $shippingAmount = $order['shipping_excl_tax'];
        $shipping_method = Mage::registry('mp_country')->getParam('default_shipment_method');
        $shipping_method_title = $this->getShippingMethodTitle($shipping_method);

        $new_order->setshipping_method($shipping_method);
        $new_order->setshipping_description($shipping_method_title);
        $new_order->setshipping_amount((double) $shippingAmount);
        $new_order->setbase_shipping_amount((double) $shippingAmount);
        $new_order->setshipping_tax_amount((double) $shippingTaxAmount);
        $new_order->setbase_shipping_tax_amount((double) $shippingTaxAmount);
        $shippingInclTax = $shippingAmount + $shippingTaxAmount;
        $new_order->setshipping_incl_tax((double) $shippingInclTax);
        $new_order->setbase_shipping_incl_tax((double) $shippingInclTax);
        
        $new_order->setbase_to_global_rate(1);
        $new_order->setbase_to_order_rate(1);
        $new_order->setstore_to_order_rate(1);
        $new_order->setis_virtual(0);
        $new_order->setbase_discount_amount(0);
        $new_order->setdiscount_amount(0);
        $new_order->setbase_shipping_discount_amount(0);
        $new_order->setshipping_discount_amount(0);
        $new_order->sethidden_tax_amount(0);
        $new_order->setbase_hidden_tax_amount(0);
        $new_order->setshipping_hidden_tax_amount(0);
        $new_order->setbase_shipping_hidden_tax_amount(0);


        //init order totals
        $new_order
                ->setGrandTotal($shippingAmount + $shippingTaxAmount)
                ->setBaseGrandTotal($shippingAmount + $shippingTaxAmount)
                ->setTaxAmount($shippingTaxAmount)
                ->setBaseTaxAmount($shippingTaxAmount);

        foreach ($order['products'] as $item) {

            //set price and tax
            $price_excl_tax = $item['price_excl_tax'];
            $price_incl_tax = $item['price_incl_tax'];
            $tax = $item['price_tax'];
            $qty = $item['quantity'];

            $taxTotal = $tax * $qty;
            $htTotal = $price_excl_tax * $qty;

            //add product
            $product = Mage::getModel('catalog/product')->load($item['id']);

            // check if product exists
            if (!$product->getId()) {
                throw new Exception('Sku '.$item['id'].' does not exist');
            }
            
            $_orderWeight += $product->getweight() * $qty;
            $_orderQtyOrdered += $qty;
            
            $NewOrderItem = Mage::getModel('sales/order_item')
                            ->setProductId($product->getId())
                            ->setSku($product->getSku())
                            ->setName($product->getName())
                            ->setWeight($product->getWeight())
                            ->setTaxClassId($product->getTaxClassId())
                            ->setCost($product->getCost())
                            ->setOriginalPrice($price_excl_tax)
                            ->setbase_original_price($price_excl_tax)
                            ->setIsQtyDecimal(0)
                            ->setProduct($product)
                            ->setPrice((double) $price_excl_tax)
                            ->setBasePrice((double) $price_excl_tax)
                            ->setprice_incl_tax((double) $price_incl_tax)
                            ->setbase_price_incl_tax((double) $price_incl_tax)
                            ->setbase_row_total_incl_tax((double) $price_incl_tax * $qty)
                            ->setrow_total_incl_tax((double) $price_incl_tax * $qty)
                            ->setQtyOrdered($item['quantity'])
                            ->setmarketplace_item_id($item['mp_item_id'])
                            ->setTaxAmount($taxTotal)
                            ->setBaseTaxAmount($taxTotal)
                            ->setTaxPercent($taxRate)
                            ->setRowTotal($htTotal)
                            ->setBaseRowTotal($htTotal)
                            ->setRowWeight($product->getWeight() * $qty)
                            ->setbase_tax_before_discount($taxTotal)
                            ->settax_before_discount($taxTotal)
                            ->setstore_id($this->getCurrentStoreID())
                            ->setproduct_type($product->getTypeId());
                            /*->setprice_incl_tax($price_incl_tax)
                            ->setrow_total_incl_tax($row_total_incl_tax);*/


            $NewOrderItem->setbase_weee_tax_applied_amount(0);
            $NewOrderItem->setbase_weee_tax_disposition(0);
            $NewOrderItem->setweee_tax_applied_amount(0);
            $NewOrderItem->setweee_tax_disposition(0);

            // update product stock (fix bug...)
            if($decrement_stock === true)
                    $this->_updateProductStock($product, $item['quantity']);

            //add product
            $new_order->addItem($NewOrderItem);
            $new_order
                    ->setSubtotal($new_order->getSubtotal() + $price_excl_tax * $qty)
                    ->setBaseSubtotal($new_order->getBaseSubtotal() + $price_excl_tax * $qty)
                    ->setGrandTotal($new_order->getGrandTotal() + (($tax + $price_excl_tax) * $qty))
                    ->setBaseGrandTotal($new_order->getBaseGrandTotal() + (($tax + $price_excl_tax) * $qty))
                    ->setTaxAmount($new_order->getTaxAmount() + $tax * $qty)
                    ->setBaseTaxAmount($new_order->getBaseTaxAmount() + $tax * $qty)
                    ->setbase_subtotal_incl_tax($new_order->getbase_subtotal_incl_tax() + $price_incl_tax * $qty)
                    ->setsubtotal_incl_tax($new_order->getsubtotal_incl_tax() + $price_incl_tax * $qty);
        }

        
        $new_order->setweight($_orderWeight);
        $new_order->settotal_qty_ordered($_orderQtyOrdered);
        
        //save order
        $new_order->setstatus(Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_order_status());
        $new_order->setstate('new');
        $new_order->addStatusToHistory(
                                    'pending',
                                    'Commande ' . $order['marketplace'] . ' #' . $order['mpOrderId']
                                );
        $new_order->setcreated_at(date("Y-m-d H:i:s"), Mage::getModel('Core/Date')->timestamp());
        $new_order->setupdated_at(date("Y-m-d H:i:s"), Mage::getModel('Core/Date')->timestamp());
        $new_order->setfrom_site($order['marketplace']);
        $new_order->setmarketplace_order_id($order['mpOrderId']);
        $new_order->setinvoice_comments('Commande ' . $order['marketplace'] . ' #' . $order['mpOrderId']);
        $new_order->save();

        if(Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_generate_invoice() == 1){

            Mage::helper('MarketPlace/Invoice')->createInvoice($new_order);
            $new_order->save();
        }

        // dispatch event
        Mage::dispatchEvent('sales_marketplace_order_after_import', array('order' => $new_order));
        
        return $new_order;
    }

    /**
     * Update produdct stock
     * 
     * @param object $product
     * @param int $qty_to_substract 
     * @return int 0
     */
    protected function _updateProductStock($product, $qty_to_substract){

        $stock_item = Mage::getModel('cataloginventory/stock_item');
        $current_qty = $stock_item->loadByProduct($product)->getQty();
        $qty = $current_qty - $qty_to_substract;

        $stock_item->setQty($qty)
                    ->save();       
        
        return 0;

    }

    /**
     * Return shipment method title
     *
     * @param string $shipping_method
     * @return string
     */
    public function getShippingMethodTitle($shipping_method) {

        return mage::helper('MarketPlace')->getShippingMethodTitle($shipping_method);
    }

    /**
     * Create or return customer
     *
     * @param array $order
     * @return object $customer
     * @todo : insertion des informations concernant les adresses de livraison et facturation !!! http://www.boostmyshop.com/index.php/admin/CrmTicket/Admin_Ticket/Edit/ticket_id/11118/id/4691/
     */
    protected function createOrReturnCustomer($order) {

        // retrieve store ID and webSiteId
        $storeId = Mage::registry('mp_country')->getParam('store_id');
        $webSiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();

        //return customer if already exists
        $email = $order['email'];
        $fakeCustomer = mage::getModel('customer/customer');
        $fakeCustomer->setWebsiteId($webSiteId);
        $tName = explode(' ', $order['firstname']);
        $firstname = $tName[0];
        $lastname = '';
        for ($i = 1; $i < count($tName); $i++)
            $lastname .= $tName[$i] . ' ';

        $customer = Mage::getModel('customer/customer')
                        ->setWebsiteId($webSiteId)
                        ->loadByEmail($email);
        if ($customer->getId())
            return $customer;

        //create new customer
        $customer = mage::getModel('customer/customer');
        $customer->setWebsiteId($webSiteId);
        $customer->setstore_id($storeId);
        $customer->setFirstname($firstname);
        $customer->setLastname($lastname);
        $customer->setEmail($email);
        $customer_group_id = Mage::Helper('MarketPlace')->getCustomerGroupId(strtolower($this->getMarketPlaceName()));
        $customer->setgroup_id($customer_group_id);
        $customer->save();

        return $customer;
    }

    /**
     * Get current store id
     * 
     * @return int 
     */
    public function getCurrentStoreID(){

        return Mage::registry('mp_country')->getParam('store_id');               

    }

    /**
     * Check if current order has not be imported yet
     *
     * @param string $marketplaceOrderId
     * @return boolean
     */
    protected function orderAlreadyImported($marketplaceOrderId) {

        return mage::helper('MarketPlace')->orderAlreadyImported($marketplaceOrderId);
    }

    /**
     * Set log for marketplace imporation orders
     *
     * @param array $successOrders
     * @param array $skippedOrders
     * @param array $errorOrders
     */
    public function setOrderImportationLog($successOrders, $skippedOrders, $errorOrders, $errorMessages = array()) {

        $errorMsg = '';
        $successMsg = '';
        
        // Some error occured during order importation (invalid product)
        if (count($errorOrders) != 0) {

            // build error message
            $errorMsg = "An error occured during orders importation :";
            foreach ($errorOrders as $order) {
                $errorMsg .= ' ' . $order.(isset($errorMessages[$order]) ? ' : '.$errorMessages[$order] : '');
            }

            throw new Exception($errorMsg);
                        
        }

        $successMsg = "Imported orders :";
        // add information log
        if (count($successOrders) != 0) {
            // build success message
            foreach ($successOrders as $order) {
                $successMsg .= ' ' . $order;
            }
        } else {
            $successMsg .= ' none';
        }

        $successMsg .= ' Skipped orders (already exist) :';
        if (count($skippedOrders) != 0) {
            foreach ($skippedOrders as $order) {
                $successMsg .= " " . $order;
            }            
        } else {
            $successMsg .= ' none.';
        }

        return $successMsg;
    }

    /**
     * Get marketplace orders
     * (must be implemented in subclass )
     */
    abstract function getMarketPlaceOrders();

    /**
     * Check file version
     * (must be implemented in subclass)
     * 
     * @param array $lines 
     */
    abstract function checkFileVersion($lines);

    /**
     * Build order tab
     * (must be implemented in subclass )
     * 
     * @param string $str 
     */
    abstract function buildOrdersTab($str);

    /**
     * Check that file is ok
     * (ust be implemented in subclass)
     * 
     * @param array $lines 
     */
    abstract function isFileOk($lines);

    /**
     * Get marketPlace name
     * (must be implemented in subclass) 
     */
    abstract function getMarketPlaceName();

}
