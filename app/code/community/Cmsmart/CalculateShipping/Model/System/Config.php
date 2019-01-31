<?php
/**
 * Class Cmsmart_CalculateShipping_Model_System_Config
 * Get all information that user config for the extension
 *
 * @author Pham Hong Thanh
 * @email thanhpham0990@gmail.com
 */

class Cmsmart_CalculateShipping_Model_System_Config{

    const XML_PATH_ENABLED              = 'calculateshipping/allow/enabled';
    const XML_PATH_APPLY_CATEGORY       = 'calculateshipping/allow/category';
    const XML_PATH_AUTO_DETECT          = 'calculateshipping/allow/ip';

    const XML_PATH_POSITION             = 'calculateshipping/use/position';
    const XML_PATH_COUNTRY              = 'calculateshipping/use/country';
    const XML_PATH_REGION               = 'calculateshipping/use/region';
    const XML_PATH_CITY                 = 'calculateshipping/use/city';
    const XML_PATH_POSTCODE             = 'calculateshipping/use/post_code';

    const XML_PATH_INCLUDE_CART         = 'calculateshipping/advance/include_cart';
    const XML_PATH_INCLUDE_CART_DEFAULT = 'calculateshipping/advance/include_cart_default';

    const XML_PATH_DEFAULT_COUNTRY      = 'general/country/default';
    const XML_PATH_ACTIONS              = 'calculateshipping/apply/action';

    const DISPLAY_POSITION_RIGHT        = 'right';
    const DISPLAY_POSITION_LEFT         = 'left';
    const DISPLAY_POSITION_POPUP        = 'popup';

    const LAYOUT_HANDLE_LEFT            = 'calculateshipping_left';
    const LAYOUT_HANDLE_RIGHT           = 'calculateshipping_right';
    const LAYOUT_HANDLE_POPUP           = 'calculateshipping_popup';

    const LAYOUT_HANDLE_CATEGORY        = 'calculateshipping_category';



    /**
     * Check extension is enabled or disabled
     * @return boolean
     */

    public function isEnabled(){
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }


    /**
     * Check extension is applied for category page
     * @return boolean
     */

    public function isApplyCategory(){
        return Mage::getStoreConfig(self::XML_PATH_APPLY_CATEGORY);
    }


    /**
     * Check position of form on frontend
     * @return Cmsmart_CalculateShipping_Model_System_Config_Source_Position
     */

    public function getPosition(){
        return Mage::getStoreConfig(self::XML_PATH_POSITION);
    }


    /**
     * Check use auto detect address from ip
     * @return boolean
     */

    public function useAutoDetect(){
        return Mage::getStoreConfig(self::XML_PATH_AUTO_DETECT);
    }


    /**
     * Check use country
     * @return boolean
     */

    public function useCountry(){
        return Mage::getStoreConfig(self::XML_PATH_COUNTRY);
    }


     /**
     * Check use region
     * @return boolean
     */

    public function useRegion(){
        return Mage::getStoreConfig(self::XML_PATH_REGION);
    }


     /**
     * Check use city
     * @return boolean
     */

    public function useCity(){
        return Mage::getStoreConfig(self::XML_PATH_CITY);
    }


     /**
     * Check use postcode
     * @return boolean
     */

    public function usePostCode(){
        return Mage::getStoreConfig(self::XML_PATH_POSTCODE);
    }


     /**
     * Check use include cart
     * @return boolean
     */

    public function useIncludeCart(){
        return Mage::getStoreConfig(self::XML_PATH_INCLUDE_CART);
    }


     /**
     *  Get default include cart
     * @return boolean
     */

    public function useIncludeCartDefault(){
        return Mage::getStoreConfig(self::XML_PATH_INCLUDE_CART_DEFAULT);
    }


    /**
     * Return default country code
     *
     * @param Mage_Core_Model_Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_COUNTRY, $store);
    }


    /**
     * Retrieve layout handles list for applying of the form
     *
     * @return array
     */
    public function getHandles()
    {
        $actions = array();
        foreach (Mage::getConfig()->getNode(self::XML_PATH_ACTIONS)->children() as $action => $node) {
            $actions[] = $action;
        }

        return $actions;
    }
}