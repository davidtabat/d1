<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Model_Observer
{
    /**
     * load module configurations so we can access the default configurations
     */
    public function __construct()
    {
        Mage::getConfig()->loadModules();
    }

    /**
     * prevent saving of empty expert code fields
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminSystemConfigChangedSectionTrustedshops(Varien_Event_Observer $observer)
    {
        /**
         * find the correct scope to save the default values
         */
        $store = Mage::app()->getRequest()->getParam('store');
        $website = Mage::app()->getRequest()->getParam('website');
        if ($store) {
            $scope = 'stores';
            $scopeId = (int)Mage::app()->getStore($store)->getId();
        } elseif ($website) {
            $scope = 'websites';
            $scopeId = (int)Mage::app()->getWebsite($website)->getId();
        } else {
            $scope = 'default';
            $scopeId = 0;
        }

        $this->_checkFields('trustbadge', 'code', $scope, $scopeId);
        $this->_checkFields('product', 'review_code', $scope, $scopeId);
        $this->_checkFields('product', 'rating_code', $scope, $scopeId);
    }

    /**
     * check post params and set default value if they are empty
     *
     * @param string $area
     * @param string $field
     * @param string $scope
     * @param int    $scopeId
     */
    protected function _checkFields($area, $field, $scope, $scopeId)
    {
        $configPath = "trustedshops/" . $area . "/" . $field;

        $currentConfiguration = Mage::getStoreConfig($configPath, $scopeId);
        if (empty($currentConfiguration)) {
            $defaultConfigurationValue = (string)Mage::getConfig()->getNode('default/' . $configPath);
            Mage::getConfig()->saveConfig($configPath, $defaultConfigurationValue, $scope, $scopeId);
        }
    }
}
