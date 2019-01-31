<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
abstract class Trustedshops_Trustedshops_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * list of supported locales
     *
     * @var array
     */
    protected $supportedLocales = array(
        'de_DE',
        'en_GB',
        'fr_FR',
        'es_ES',
        'it_IT',
        'nl_NL',
        'pl_PL',
    );

    /**
     * locale to use if no appropriate one was found
     *
     * @var string
     */
    protected $fallbackLocale = 'en_GB';

    /**
     * get configuration value by field and area
     *
     * @param $field string
     * @param $area  string
     *
     * @return mixed
     */
    public function getConfig($field, $area)
    {
        return Mage::getStoreConfig('trustedshops/' . $area . '/' . $field);
    }

    /**
     * get the trustedshops id
     *
     * @return string
     */
    public function getTsId()
    {
        return $this->getConfig('tsid', 'info');
    }

    /**
     * get the current shop locale
     *
     * @return string
     */
    public function getLocale()
    {
        $shopLocale = Mage::app()->getLocale()->getLocaleCode();

        if (in_array($shopLocale, $this->supportedLocales)) {
            return $shopLocale;
        }

        // find base locale
        $localeParts = explode('_', $shopLocale);
        foreach ($this->supportedLocales as $supportedLocale) {
            if (strpos($supportedLocale, $localeParts[0]) !== false) {
                return $supportedLocale;
            }
        }

        return $this->fallbackLocale;
    }

    /**
     * check if the extension has a trustedshops id
     *
     * @return bool
     */
    public function isActive()
    {
        $tsId = $this->getTsId();
        if (!empty($tsId)) {
            return true;
        }
        return false;
    }

    /**
     * check if the mode is expert
     *
     * @return bool
     */
    public function isExpert()
    {
        return (Trustedshops_Trustedshops_Model_System_Config_Source_Mode::MODE_EXPERT == $this->getConfig('mode', 'info'));
    }

    /**
     * get the current product sku
     *
     * @return string
     */
    public function getProductSku()
    {
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        $product = Mage::registry('current_product');
        if (!$product) {
            return "";
        }
        return $product->getSku();
    }

    /**
     * replace variables in expert codes
     *
     * @param $code string
     *
     * @return string
     */
    public function replaceVariables($code)
    {
        $vars = array(
            'tsid' => $this->getTsId(),
            'sku' => $this->getProductSku()
        );

        foreach ($vars as $_placeholder => $_replaceValue) {
            $code = str_replace('%' . $_placeholder . '%', $_replaceValue, $code);
        }

        return $code;
    }
}
