<?php
/**
 * Class Cmsmart_CalculateShipping_Model_Estimate
 *
 * @author Pham Hong Thanh
 * @email thanhpham0990@gmail.com
 *
 */

class Cmsmart_CalculateShipping_Model_Estimate
{

    /**
     * Customer object, if customer isn't logged in it return to false
     *
     * @var Mage_Customer_Model_Customer|boolean
     */
    protected $_customer = null;



    /**
     * Sales quote object to add products for calculate shipping
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;



    /**
     * Product model object
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;



    /**
     * Result calculate shipping
     *
     * @var array
     */
    protected $_result = array();



    /**
     * Delivery address information
     *
     * @var array
     */
    protected $_addressInfo = null;



    /**
     * Set address info for calculate shipping
     *
     * @param array $info
     * @return Cmsmart_CalculateShipping_Model_Estimate
     */
    public function setAddressInfo($info)
    {
        $this->_addressInfo = $info;
        return $this;
    }



    /**
     * Retrieve address information
     *
     * @return boolean
     */
    public function getAddressInfo()
    {
        return $this->_addressInfo;
    }



    /**
     * Set a product for the estimation
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Cmsmart_CalculateShipping_Model_Estimate
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }



    /**
     * Retrieve product for the estimation
     */
    public function getProduct()
    {
        return $this->_product;
    }



    /**
     * Retrieve shipping rate result
     *
     * @return array|null
     */
    public function getResult()
    {
        return $this->_result;
    }



    /**
     * Retrieve list of shipping rates
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $addToCartInfo
     * @param array $addressInfo
     * @return Cmsmart_CalculateShipping_Model_Estimate
     */
    public function estimate($product = null, $addToCartInfo = null, $addressInfo = null)
    {

        $addressInfo = (array) $this->getAddressInfo();

        $product = $this->getProduct();

        $addToCartInfo = (array) $product->getAddToCartInfo();

        if (!($product instanceof Mage_Catalog_Model_Product) || !$product->getId()) {
            Mage::throwException(
                Mage::helper('calculateshipping')->__('Please specify a valid product')
            );
        }

        if (!isset($addressInfo['country_id'])) {
            Mage::throwException(
                Mage::helper('calculateshipping')->__('Please specify a country')
            );
        }

        if (empty($addressInfo['include_cart'])) {
            $this->resetQuote();
        }

        $shippingAddress = $this->getQuote()->getShippingAddress();
        
        $shippingAddress->setCountryId($addressInfo['country_id'])
            ->setCity($addressInfo['city'])
            ->setPostcode($addressInfo['post_code'])
            ->setRegionId($addressInfo['region_id'])
            ->setRegion($addressInfo['region'])
            ->setCollectShippingRates(true);

        if (isset($addressInfo['coupon_code'])) {

            $this->getQuote()->setCouponCode($addressInfo['coupon_code']);
        }

        $request = new Varien_Object($addToCartInfo);

        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            if($minimumQty > 0 && $request->getQty() < $minimumQty){
                $request->setQty($minimumQty);
            }
        }

        $result = $this->getQuote()->addProduct($product, $request);

        if (is_string($result)) {
            Mage::throwException($result);
        }

        Mage::dispatchEvent('checkout_cart_product_add_after',
                            array('quote_item' => $result, 'product' => $product));

        $this->getQuote()->collectTotals();

        $this->_result = $shippingAddress->getGroupedAllShippingRates();

        return $this;
    }


    /**
     * Reset quote object
     *
     * @return Cmsmart_CalculateShipping_Model_Estimate
     */
    public function resetQuote()
    {
        $this->getQuote()->removeAllAddresses();

        if ($this->getCustomer()) {
            $this->getQuote()->setCustomer($this->getCustomer());
        }

        return $this;
    }


    /**
     * Retrieve sales quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote === null) {

            $addressInfo = $this->getAddressInfo();

            if (!empty($addressInfo['include_cart'])) {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
            } else {
                $quote = Mage::getModel('sales/quote');
            }

            $this->_quote = $quote;
        }

        return $this->_quote;
    }


    /**
     * Retrieve currently logged in customer,
     * if customer isn't logged it returns false
     *
     * @return Mage_Customer_Model_Customer|boolean
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {

            $customerSession = Mage::getSingleton('customer/session');

            if ($customerSession->isLoggedIn()){

                $this->_customer = $customerSession->getCustomer();

            } else {

                $this->_customer = false;

            }
        }

        return $this->_customer;
    }

    /**
     * Check if customer use include cart
     *
     * @return boolean
     */

    public function isIncludeCart(){

        $flag = false;

        $addressInfo = $this->getAddressInfo();

        if (!empty($addressInfo['include_cart'])){
            $flag = true;
        }

        return $flag;
    }



}
