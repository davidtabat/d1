<?php
/**
 * Class Cmsmart_CalculateShipping_Block_Form
 *
 * @author Pham Hong Thanh
 * @email thanhpham0990@gmail.com
 */

class Cmsmart_CalculateShipping_Block_Form extends Mage_Catalog_Block_Product_Abstract{

    /**
     * Available Carriers Instances
     * @var null|array
     */
    protected $_carriers = null;


    /**
     * Session model
     *
     * @var $_session Cmsmart_CalculateShipping_Model_Session
     */
    protected $_session = null;


    /**
     * Configuration model
     *
     * @var $_config Cmsmart_CalculateShipping_Model_System_Config
     */
    protected $_config = null;


    /**
     * Check is enabled module
     *
     * @return boolean
     */
    public function isEnabled()
    {
        if(Mage::app()->getRequest()->getControllerName() == 'product'){
           return $this->getConfig()->isEnabled() && !$this->getProduct()->isVirtual();
        }

        return $this->getConfig()->isEnabled();

    }


    /**
     * Check visible field on the form
     *
     * @param $fieldName
     * @return boolean
     */
    public function isFieldVisible($fieldName){

        $config = Mage::getSingleton('calculateshipping/system_config');

        $methodName = 'use'.str_replace('_','',uc_words($fieldName));

        if(method_exists($config,$methodName)){
            return $config->$methodName();
        }

        return true;
    }


    /**
     * Check required field on the form
     *
     * @param $fieldName
     * @return boolean
     */
    public function isFieldRequired($fieldName){

        $requiredMethods = array(
            'region' => 'isStateProvinceRequired', // Checks is region required
            'city'   => 'isCityRequired', // Checks is city required
            'post_code' => 'isZipCodeRequired' // Checks is postal code required
        );

        if (!isset($requiredMethods[$fieldName])) {
            return false;
        }

        $method = $requiredMethods[$fieldName];

        foreach ($this->getCarriers() as $carrier) {
            if ($carrier->$method()) {
                return true;
            }
        }

        return false;
    }


    /**
     * Retrieve field value
     *
     * @param string $fieldName
     * @return mixed
     */
    public function getFieldValue($fieldName)
    {
        $values = $this->getSession()->getFormValues();


        if (isset($values[$fieldName])) {
            return $values[$fieldName];
        }

        return null;
    }

    /**
     * Retrieve session model object
     *
     * @return Cmsmart_CalculateShipping_Model_Session
     */
    public function getSession()
    {
        if ($this->_session === null) {
            $this->_session = Mage::getSingleton('calculateshipping/session');
        }

        return $this->_session;
    }


    /**
     * Retrieve url for submit form
     *
     * @return string
     */
    public function getControllerSubmit()
    {
        return $this->getUrl('calculateshipping/index/calculate', array('_current' => true));
    }


    /**
     * Check include cart
     *
     * @return boolean
     */
    public function useIncludeCart()
    {
        if ($this->getSession()->getFormValues() === null || !$this->isFieldVisible('include_cart')) {
            return $this->getConfig()->useIncludeCartDefault();
        }

        return $this->getFieldValue('include_cart');
    }


    /**
     * Check use auto detect address customer by ip
     *
     * @return boolean
     */
    public function useAutoDetect(){
        return $this->getConfig()->useAutoDetect();
    }



    /**
     * Retrieve configuration model for module
     *
     * @return Cmsmart_CalculateShipping_Model_System_Config
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = Mage::getSingleton('calculateshipping/system_config');
        }

        return $this->_config;
    }


    /**
     * Obtain available carriers instances
     *
     * @return array
     */
    public function getCarriers(){
        if (null === $this->_carriers) {
            $this->_carriers = Mage::getModel('shipping/config')->getActiveCarriers();
        }
        return $this->_carriers;
    }
   /**
    * Get country collection
    * @return array
    */
    public function getCountryCollection()
    {
        $countryCollection = Mage::getModel('directory/country_api')->items();
        return $countryCollection;
    }

}