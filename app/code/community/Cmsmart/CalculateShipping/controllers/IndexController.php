<?php
require_once Mage::getModuleDir('controllers', 'Mage_Catalog').DS.'ProductController.php';

class Cmsmart_CalculateShipping_IndexController extends Mage_Catalog_ProductController
{
    /**
     * Return Ip Address of customer
     */

    public function getIpAddr(){

        if (!empty($_SERVER['HTTP_CLIENT_IP'])){

            // check ip from share internet

            $ipAddr = $_SERVER['HTTP_CLIENT_IP'];

        }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){

            // to check ip is pass from proxy

            $ipAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];

        }else{

            $ipAddr = $_SERVER['REMOTE_ADDR'];

        }

        return $ipAddr;
    }

    /**
     *  Get information about Location of customer by ip
     */
    function getLocation(){

        // verify the IP.
        ip2long($this->getIpAddr())== -1 || ip2long($this->getIpAddr()) === false ? trigger_error("Invalid IP", E_USER_ERROR) : "";

        //get the JSON result from hostip.info

        $result = file_get_contents("http://freegeoip.net/json/".$this->getIpAddr());

        /**
         * Ip Address
         * Country: United State, Region: Alabama, City: Hartselle, Zipcode: 35640
         */
//        $result = file_get_contents("http://freegeoip.net/json/174.125.192.125");

        /**
         * Ip Address
         * Country: United Kingdom , Region: London, City: London, Zipcode: N6
         */
//        $result = file_get_contents("http://freegeoip.net/json/77.99.179.98");

        /**
         * Ip Address
         * Country: Viet Nam , Region: Ha Noi, City: Ha Noi, Zipcode: ''
         */
//        $result = file_get_contents("http://freegeoip.net/json/117.0.35.249");


        $result = json_decode($result, 1);

        return $result;

    }


    public function indexAction(){

        $response = array();

        $address = $this->getLocation();

        $response['countryId'] = $address['country_code'];

        $response['city'] = $address['city'];

        $response['regionId'] = $address['region_code'];

        $response['region'] = $address['region_name'];

        $response['zipcode'] = $address['zipcode'];

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

        return;

    }


    public function calculateAction(){

        $response = array();

        $this->loadLayout();

        $categoryId = (int) $this->getRequest()->getParam('category');

        $productId  = (int) $this->getRequest()->getPost('product');

        $params = new Varien_Object();

        $params->setCategoryId($categoryId);

        $product = Mage::helper('calculateshipping/product')->initProduct($productId, $this, $params);

        $block = $this->getLayout()->getBlock('calculateshipping.result');

        $estimate = Mage::getSingleton('calculateshipping/estimate');

        if ($block) {

            $params = $this->getRequest()->getPost();

            $addressInfo = $params['estimate'];

            $session = Mage::getSingleton('calculateshipping/session');

            $product->setAddToCartInfo((array) $params);

            $estimate->setProduct($product);

            $estimate->setAddressInfo((array) $addressInfo);

            $session->setFormValues($addressInfo);

            try {

                $estimate->estimate();

                $result = $this->getLayout()->getBlock('calculateshipping.result')->toHtml();


                $response['result'] = $result;

            } catch (Mage_Core_Exception $e) {

                $response['status'] = 0;

                $response['message'] = $e->getMessage();

            } catch (Exception $e) {

                $response['status'] = 0;

                $response['message'] = Mage::helper('calculateshipping')->__('There was an error during processing your shipping request');

            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

        return;

    }
}
