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

class MDN_Cdiscount_Helper_Orders extends MDN_MarketPlace_Helper_Orders {

    // order status
    const kCancelledByCustomer = 'CancelledByCustomer';
    const kWaitingForSellerAcceptation = 'WaitingForSellerAcceptation';
    const kAcceptedBySeller = 'AcceptedBySeller';
    const kPaymentInProgress = 'PaymentInProgress';
    const kWaitingForShipmentAcceptation = 'WaitingForShipmentAcceptation';
    const kShipped = 'Shipped';
    const kRefusedBySeller = 'RefusedBySeller';
    const kAutomaticCancellation = 'AutomaticCancellation';
    const kPaymentRefused = 'PaymentRefused';
    const kShipmentRefusedBySeller = 'ShipmentRefusedBySeller';
    const kRefusedNoShipment = 'RefusedNoShipment';

    /**
     * Get all available order statuses
     *
     * @return array
     */
    public function getAllOrderStatuses(){
        return array(
            self::kCancelledByCustomer => self::kCancelledByCustomer,
            self::kWaitingForSellerAcceptation => self::kWaitingForSellerAcceptation,
            self::kAcceptedBySeller => self::kAcceptedBySeller,
            self::kPaymentInProgress => self::kPaymentInProgress,
            self::kWaitingForShipmentAcceptation => self::kWaitingForShipmentAcceptation,
            self::kShipped => self::kShipped,
            self::kRefusedBySeller => self::kRefusedBySeller,
            self::kAutomaticCancellation => self::kAutomaticCancellation,
            self::kPaymentRefused => self::kPaymentRefused,
            self::kShipmentRefusedBySeller => self::kShipmentRefusedBySeller,
            self::kRefusedNoShipment => self::kRefusedNoShipment
        );
    }

    /**
     * Get order statuses allowed to be updated
     *
     * @return array
     */
    public function getAllowedStatusesToUpdate(){

        return array(
            'AcceptedBySeller' => 'AcceptedBySeller',
            'Shipped' => 'Shipped',
            'RefusedBySeller' => 'RefusedBySeller',
            'ShipmentRefusedBySeller' => 'ShipmentRefusedBySeller'
        );

    }

    /**
     * Update Orders
     *
     * @param string $from : current status
     * @param string $to : target status
     * @param string $id
     * @return array
     */
    public function updateOrders($from, $to, $id = null){

        //prevent issue between marketplace & cdiscount modules (but requried for amazon)
        if (is_array($from))
            return false;

        $ordersTab = array();
        $i = 0;
        $helper = Mage::Helper('Cdiscount/Services');
        $feed = $helper->getOrderList(array($from));

        $xml = new DomDocument();
        $xml->loadXML($feed['content']);

        if($xml->getElementsByTagName('OrderList')->item(0)){

            $orderList = $xml->getElementsByTagName('OrderList')->item(0);

            if($orderList->getElementsByTagName('Order')->item(0)){

                foreach($orderList->getElementsByTagName('Order') as $orderNode){

                    if($id !== null && $orderNode->getElementsByTagName('OrderNumber')->item(0)->nodeValue != $id)
                            continue;

                    $ordersTab[$i] = array(
                        'orderNumber' => $orderNode->getElementsByTagName('OrderNumber')->item(0)->nodeValue,
                        'status' => $to,
                        'items' => array(),
                        'tracking' => null
                    );

                    if($orderNode->getElementsByTagName('OrderLine')->item(0)){

                        foreach($orderNode->getElementsByTagName('OrderLine') as $orderLineNode){

                            if($orderLineNode->getElementsByTagName('SellerProductId')->item(0)->nodeValue != ''){

                                $ordersTab[$i]['items'][] = array(
                                    'status' => $to,
                                    'sellerProductId' => $orderLineNode->getElementsByTagName('SellerProductId')->item(0)->nodeValue
                                );

                            }

                        }

                    }

                    $i++;

                }

            }
        }

        $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeUpdateOrders);
        $res = $helper->ValidateOrderList($ordersTab);

        return $res;

    }

    /**
     * Get orders
     */
    public function getMarketPlaceOrders(){

        $orders = array();
        $helper = Mage::Helper('Cdiscount/Services');
        
        // accept orders :
        $this->updateOrders(self::kWaitingForSellerAcceptation, self::kAcceptedBySeller);

        // request orders, only kWaitingForShipmentAcceptation orders, other state don't display address
        $helper->setRequestType(MDN_Cdiscount_Helper_Feed::kFeedTypeImportOrders);
        $feed = $helper->getOrderList(array(self::kWaitingForShipmentAcceptation));

        // build array
        $orders = $this->buildOrdersTab($feed);

        return $orders;

    }

    /**
     * Build order tab
     *
     * @param string $str
     */
    public function buildOrdersTab($result){

        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        
        $ordersTab = array();
        $tax = $country->getParam('taxes');

        $xml = new DomDocument('1.0','utf-8');

        $xml->loadXML($result['content']);

        if($xml->getElementsByTagName('OrderList')->item(0)){

            $orderListNode = $xml->getElementsByTagName('OrderList')->item(0);
            if($orderListNode->getElementsByTagName('Order')->item(0)){

                foreach($orderListNode->getElementsByTagName('Order') as $orderNode){

                    // ORDER NUMBER
                    $order_number = $orderNode->getElementsByTagName('OrderNumber')->item(0)->nodeValue;

                    // BILLING ADDRESS
                    $billingAddressNode = $orderNode->getElementsByTagName('BillingAddress')->item(0);

                    if(!$billingAddressNode->hasAttribute('i:nil')){
                        //$billing_address1 = $billingAddressNode->getElementsByTagName('Address1')->item(0)->nodeValue;
                        //$billing_address2 = $billingAddressNode->getElementsByTagName('Address2')->item(0)->nodeValue;
                        $billing_appt_number = $billingAddressNode->getElementsByTagName('ApartmentNumber')->item(0)->nodeValue;
                        $billing_building = $billingAddressNode->getElementsByTagName('Building')->item(0)->nodeValue;
                        $billing_city = $billingAddressNode->getElementsByTagName('City')->item(0)->nodeValue;
                        $billing_civility  = $billingAddressNode->getElementsByTagName('Civility')->item(0)->nodeValue;
                        $billing_company_name = $billingAddressNode->getElementsByTagName('CompanyName')->item(0)->nodeValue;
                        $billing_country = $billingAddressNode->getElementsByTagName('Country')->item(0)->nodeValue;
                        $billing_firstname = $billingAddressNode->getElementsByTagName('FirstName')->item(0)->nodeValue;
                        $billing_lastname = $billingAddressNode->getElementsByTagName('LastName')->item(0)->nodeValue;
                        $billing_instructions = $billingAddressNode->getElementsByTagName('Instructions')->item(0)->nodeValue;
                        $billing_placename = $billingAddressNode->getElementsByTagName('PlaceName')->item(0)->nodeValue;
                        $billing_street = $billingAddressNode->getElementsByTagName('Street')->item(0)->nodeValue;
                        $billing_zip_code = $billingAddressNode->getElementsByTagName('ZipCode')->item(0)->nodeValue;

                        if($billing_placename)
                            $billing_street .= ' '.$billing_placename;
                        
                        if($billing_building)
                            $billing_street .= ' '.$billing_building;
                        
                        if($billing_appt_number)
                            $billing_street .= ' '.$billing_appt_number;
                        
                        // SHIPPING ADDRESS
                        $shippingAddressNode = $orderNode->getElementsByTagName('ShippingAddress')->item(0);

                        if(!$shippingAddressNode->hasAttribute('i:nil')){

                            //$shipping_address1 = $shippingAddressNode->getElementsByTagName('Address1')->item(0)->nodeValue;
                            //$shipping_address2 = $shippingAddressNode->getElementsByTagName('Address2')->item(0)->nodeValue;
                            $shipping_appt_number = $shippingAddressNode->getElementsByTagName('ApartmentNumber')->item(0)->nodeValue;
                            $shipping_building = $shippingAddressNode->getElementsByTagName('Building')->item(0)->nodeValue;
                            $shipping_city = $shippingAddressNode->getElementsByTagName('City')->item(0)->nodeValue;
                            $shipping_civility  = $shippingAddressNode->getElementsByTagName('Civility')->item(0)->nodeValue;
                            $shipping_company_name = $shippingAddressNode->getElementsByTagName('CompanyName')->item(0)->nodeValue;
                            $shipping_country = $shippingAddressNode->getElementsByTagName('Country')->item(0)->nodeValue;
                            $shipping_firstname = $shippingAddressNode->getElementsByTagName('FirstName')->item(0)->nodeValue;
                            $shipping_lastname = $shippingAddressNode->getElementsByTagName('LastName')->item(0)->nodeValue;
                            $shipping_instructions = $shippingAddressNode->getElementsByTagName('Instructions')->item(0)->nodeValue;
                            $shipping_placename = $shippingAddressNode->getElementsByTagName('PlaceName')->item(0)->nodeValue;
                            $shipping_street = $shippingAddressNode->getElementsByTagName('Street')->item(0)->nodeValue;
                            $shipping_zip_code = $shippingAddressNode->getElementsByTagName('ZipCode')->item(0)->nodeValue;

                            if($shipping_placename)
                                $shipping_street .= ' '.$shipping_placename;

                            if($shipping_building)
                                $shipping_street .= ' '.$shipping_building;

                            if($shipping_appt_number)
                                $shipping_street .= ' '.$shipping_appt_number;
                            

                        }else{

                            //$shipping_address1 = $billing_address1;
                            //$shipping_address2 = $billing_address2;
                            $shipping_appt_number = $billing_appt_number;
                            $shipping_building = $billing_building;
                            $shipping_city = $billing_city;
                            $shipping_civility  = $billing_civility;
                            $shipping_company_name = $billing_company_name;
                            $shipping_country = $billing_country;
                            $shipping_firstname = $billing_firstname;
                            $shipping_lastname = $billing_lastname;
                            $shipping_instructions = $billing_instructions;
                            $shipping_placename = $billing_placename;
                            $shipping_street = $billing_street;
                            $shipping_zip_code = $billing_zip_code;

                        }
                    }

					$shipping_comments = $shipping_instructions;
                    $billing_comments = $billing_instructions;

                    // DATE  DE CREATION
                    $created_at = $orderNode->getElementsByTagName('CreationDate')->item(0)->nodeValue;

                    // CUSTOMER
                    $customerNode = $orderNode->getElementsByTagName('Customer')->item(0);
                    $customer_civility = $customerNode->getElementsByTagName('Civility')->item(0)->nodeValue;
                    $customer_id = $customerNode->getElementsByTagName('CustomerId')->item(0)->nodeValue;
                    $customer_firstname = $customerNode->getElementsByTagName('FirstName')->item(0)->nodeValue;
                    $customer_lastname = $customerNode->getElementsByTagName('LastName')->item(0)->nodeValue;
                    $customer_mobile_phone = $customerNode->getElementsByTagName('MobilePhone')->item(0)->nodeValue;
                    $customer_phone = $customerNode->getElementsByTagName('Phone')->item(0)->nodeValue;
                    $customer_email = $customerNode->getElementsByTagName('Email')->item(0)->nodeValue;

                    if($customer_email == ''){
                        $customer_email = 'auto_'.md5(rand().date('YmdHis', Mage::getModel('core/date')->timestamp())).'@cdiscount.com';
                    }

                    // DIVERS
                    $initial_total_amount = $orderNode->getElementsByTagName('InitialTotalAmount')->item(0)->nodeValue;
                    $initial_total_shipping_charges_amount = $orderNode->getElementsByTagName('InitialTotalShippingChargesAmount')->item(0)->nodeValue;
                    $last_updated_date = $orderNode->getElementsByTagName('LastUpdatedDate')->item(0)->nodeValue;
                    $modified_date = $orderNode->getElementsByTagName('ModifiedDate')->item(0)->nodeValue;

                    // OTHER
                    $order_state = $orderNode->getElementsByTagName('OrderState')->item(0)->nodeValue;
                    $shipped_total_amount = $orderNode->getElementsByTagName('ShippedTotalAmount')->item(0)->nodeValue;
                    $shipped_total_shipping_charges = $orderNode->getElementsByTagName('ShippedTotalShippingCharges')->item(0)->nodeValue;
                    $shipping_code =$orderNode->getElementsByTagName('ShippingCode')->item(0)->nodeValue;
                    $site_commission_promised_amount = $orderNode->getElementsByTagName('SiteCommissionPromisedAmount')->item(0)->nodeValue;
                    $site_commission_shipped_amount = $orderNode->getElementsByTagName('SiteCommissionShippedAmount')->item(0)->nodeValue;
                    $site_commission_validated_amount = $orderNode->getElementsByTagName('SiteCommissionValidatedAmount')->item(0)->nodeValue;
                    $status = $orderNode->getElementsByTagName('Status')->item(0)->nodeValue;
                    $validated_total_amount = $orderNode->getElementsByTagName('ValidatedTotalAmount')->item(0)->nodeValue;
                    $validated_total_shipping_charges = $orderNode->getElementsByTagName('ValidatedTotalShippingCharges')->item(0)->nodeValue;
                    $validation_status = $orderNode->getElementsByTagName('ValidationStatus')->item(0)->nodeValue;

                    // OFFER
                    $offerNode = $orderNode->getElementsByTagName('Offer')->item(0);

                    $shippingStreetTab = $this->explodeAdress($shipping_street);
                    $billingStreetTab = $this->explodeAdress($billing_street);
                    
                    $ordersTab[$order_number] = array(
                        'mpOrderId' => $order_number,
                        'email' => $customer_email,
                        'marketplace' => $this->getMarketPlaceName(),
                        'phone' => $customer_phone.' / '.$customer_mobile_phone,
                        'firstname' => $customer_firstname.' '.$customer_lastname,
                        'lastname' => $customer_lastname,
                        'date' => $created_at,
                        'currency' => $country->getParam('currency'),
                        'shipping_excl_tax' => 0,
                        'shipping_tax' => 0,
                        'shipping_incl_tax' => 0,
                        'shipping_adress' => array(
                            'firstname' => $shipping_firstname.' '.$shipping_lastname,
                            'lastname' => $shipping_lastname,
                            'zipCode' => $shipping_zip_code,
                            'country' => $shipping_country,
                            'state' => '',
                            'city' => utf8_decode($shipping_city),
                            'comments' => utf8_decode($shipping_comments),
                            'company' => $shipping_company_name,
                            'email' => '',
                            'phone' => ($customer_mobile_phone != '') ? $customer_mobile_phone : $customer_phone,
                            'street' => array(
                                'adress1' => $shippingStreetTab[0],
                                'adress2' => $shippingStreetTab[1],
                                'adress3' => $shippingStreetTab[2]
                        ),
                            'building' => utf8_decode($shipping_building),
                            'appartment' => utf8_decode($shipping_appt_number)
                        ),
                        'billing_adress' => array(
                            'firstname' => $billing_firstname.' '.$billing_lastname,
                            'lastname' => $billing_lastname,
                            'zipCode' => $billing_zip_code,
                            'country' => $billing_country,
                            'state' => '',
                            'city' => utf8_decode($billing_city),
                            'comments' => utf8_decode($billing_comments),
                            'company' => $billing_company_name,
                            'email' => '',
                            'phone' => $customer_phone.' / '.$customer_mobile_phone,
                            'street' => array(
                                'adress1' => $billingStreetTab[0],
                                'adress2' => $billingStreetTab[1],
                                'adress3' => $billingStreetTab[2]
                        ),
                            'building' => utf8_decode($billing_building),
                            'appartment' => utf8_decode($billing_appt_number)
                        ),
                        'products' => array()
                    );

                    // Order Line List
                    $orderLineListNode = $orderNode->getElementsByTagName('OrderLineList')->item(0);

                    $shipping = 0;

                    foreach($orderLineListNode->getElementsByTagName('OrderLine') as $orderLineNode){                        

                        $acceptation_state = $orderLineNode->getElementsByTagName('AcceptationState')->item(0)->nodeValue;
                        $category_code = $orderLineNode->getElementsByTagName('CategoryCode')->item(0)->nodeValue;
                        $delivery_date_max = $orderLineNode->getElementsByTagName('DeliveryDateMax')->item(0)->nodeValue;
                        $delivery_date_min = $orderLineNode->getElementsByTagName('DeliveryDateMax')->item(0)->nodeValue;
                        $name = $orderLineNode->getElementsByTagName('Name')->item(0)->nodeValue;
                        $product_condition = $orderLineNode->getElementsByTagName('ProductCondition')->item(0)->nodeValue;
                        $product_ean = $orderLineNode->getElementsByTagName('ProductEan')->item(0)->nodeValue;
                        $product_id = $orderLineNode->getElementsByTagName('ProductId')->item(0)->nodeValue;
                        $purchase_price = $orderLineNode->getElementsByTagName('PurchasePrice')->item(0)->nodeValue;
                        $quantity = $orderLineNode->getElementsByTagName('Quantity')->item(0)->nodeValue;
                        $row_id = $orderLineNode->getElementsByTagName('RowId')->item(0)->nodeValue;
                        $seller_product_id = $orderLineNode->getElementsByTagName('SellerProductId')->item(0)->nodeValue;
                        $shipping_date_max = $orderLineNode->getElementsByTagName('ShippingDateMax')->item(0)->nodeValue;
                        $shipping_date_min = $orderLineNode->getElementsByTagName('ShippingDateMin')->item(0)->nodeValue;
                        $sku = $orderLineNode->getElementsByTagName('SellerProductId')->item(0)->nodeValue;
                        $unit_additional_shipping_charges = $orderLineNode->getElementsByTagName('UnitAdditionalShippingCharges')->item(0)->nodeValue;
                        $unit_shipping_charges = $orderLineNode->getElementsByTagName('UnitShippingCharges')->item(0)->nodeValue;

                        // GESTION DES FRAIS DE TRAITEMENT
                        if ($orderLineNode->getElementsByTagName('ProductId')->item(0)->nodeValue == 'FRAISTRAITEMENT' || $orderLineNode->getElementsByTagName('ProductId')->item(0)->nodeValue == 'INTERETBCA'){
                            
                            $sku = 'INTERETBCA';
                            $seller_product_id = Mage::getModel('catalog/product')->getIdBySku($sku);
                            
                        }else{
                            
                            $seller_product_id = ($account->getParam('seller_product_reference') == 'sku') ? Mage::getModel('catalog/product')->getIdBySku($seller_product_id) : $seller_product_id;
                            
                        }
                        
                        // TODO : add item row
                        $price_incl_tax = $purchase_price / $quantity;
                        $price_excl_tax = round($price_incl_tax / (1 + $tax/100), 2);
                        $price_tax = round(($price_incl_tax - $price_excl_tax),2);

                        
						
                        $ordersTab[$order_number]['products'][] = array(
                            'id' => $seller_product_id,
                            'mp_item_id' => $product_id,
                            'price_excl_tax' => $price_excl_tax,
                            'price_tax' => $price_tax,
                            'price_incl_tax' => $price_incl_tax,
                            'quantity' => $quantity,
                            'marketplace_product_name' => $name
                        );

                        //$shipping += ($unit_shipping_charges + $unit_additional_shipping_charges);

                    }

                    // TODO : shipping incl excl tax
                    $ordersTab[$order_number]['shipping_excl_tax'] = round($validated_total_shipping_charges / ( 1 + $tax / 100),2);
                    $ordersTab[$order_number]['shipping_tax'] = round($validated_total_shipping_charges - ($validated_total_shipping_charges / ( 1 + $tax / 100)), 2);
                    $ordersTab[$order_number]['shipping_incl_tax'] = $validated_total_shipping_charges;

                }

            }

        }

        //echo '<pre>';var_dump($ordersTab);die('</pre>');

        return $ordersTab;

    }

    /**
     * Check if file is ok
     *
     * @param array $lines
     */
    public function isFileOk($lines){
        throw new Exception('Not implemented yet !');
    }

    /**
     * Check file version
     *
     * @param array $lines
     */
    public function checkFileVersion($lines){
        throw new Exception('Not implemented yet !');
    }


    /**
     * get marketplace name
     *
     * @return string
     */
    public function getMarketPlaceName(){
        return Mage::Helper('Cdiscount')->getMarketPlaceName();
    }

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
        $feed = utf8_encode(file_get_contents($filePath));

        $ordersTab = $this->buildOrdersTab(array('content'=>$feed));

        return $this->importMarketPlaceOrders($ordersTab);
    }

    /**
     * Explode adress field (max 25 char per entry)
     *
     * @param string $adress 
     * @return array $retour
     */
    public function explodeAdress($adress){
        
        $retour = array(
            0 => '',
            1 => '',
            2 =>''
        );
        
        $str = '';
        $i = 0;
        $str_tmp = '';
        
        //$adress = utf8_decode($adress);
        
        $tmp = explode(" ",$adress);
        
        for($j = 0; $j < count($tmp); $j++){
            
            // init
            if($str == '')
                $str_tmp = $tmp[$j];
            else
                $str_tmp = $str.' '.$tmp[$j];
            
            // nombre de caractères < 25
            if(strlen($str_tmp) < 25){
                
                $str = $str_tmp;
                
            }else{
                // sinon ajout de la ligne dans le tableau
                $retour[$i] = $str;
                $str = $tmp[$j];
                $i++;
                
            }
            
            // cas ou on arrive à la fin du tableau $tmp et que la ligne n'a pas encore été ajoutée
            // au tableau $retour
            if($j == count($tmp) - 1)
                $retour[$i] = $str;
                        
        }
        
        return $retour;
    }
                
}
