<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Helper_Setup extends Geissweb_Euvatgrouper_Helper_Abstract {

    /**
     * @return array
     */
    public function getNewProductTaxClasses()
    {
        return array(
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_PRODUCTS_STANDART => $this->__('Products with standard VAT rate'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_PRODUCTS_REDUCED => $this->__('Products with reduced VAT rate'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_PRODUCTS_SHIPPING_STANDART => $this->__('Shipping with standard VAT rate'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_PRODUCTS_SHIPPING_REDUCED => $this->__('Shipping with reduced VAT rate'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_PRODUCTS_DIGITAL => $this->__('Digital products and services'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_PRODUCTS_NON_TAXABLE => $this->__('Non-Taxable Products'),
        );
    }

    /**
     * @return array
     */
    public function getNewCustomerTaxClasses()
    {
        return array(
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_CUSTOMER_CONSUMER => $this->__('Consumers incl. VAT'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_CUSTOMER_BUSINESS_DOMESTIC => $this->__('Businesses incl. VAT'),
            Geissweb_Euvatgrouper_Model_Setup_Tax::TC_CUSTOMER_BUSINESS_EU => $this->__('Businesses excl. VAT')
        );
    }

    /**
     * @return array
     */
    public function getTaxClassesInsertArray()
    {
        $insert = array();
        foreach($this->getNewProductTaxClasses() as $key=>$class)
        {
            $insert[] = array('class_id'=>$key, 'class_name'=>$class, 'class_type'=>Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
        }
        foreach($this->getNewCustomerTaxClasses() as $key=>$class)
        {
            $insert[] = array('class_id'=>$key, 'class_name'=>$class, 'class_type'=>Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
        }
        return $insert;
    }

    /**
     * Gets the standard VAT rates for all EU countires
     * Source: http://ec.europa.eu/taxation_customs/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf
     * @return array
     */
    public function getStandardRates()
    {
        return array(
            'AT' => 20,
            'BE' => 21,
            'BG' => 20,
            'CY' => 19,
            'CZ' => 21,
            'DK' => 25,
            'DE' => 19,
            'EE' => 20,
            'ES' => 21,
            'FI' => 24,
            'FR' => 20,
            'GB' => 20,
            'GR' => 23,
            'HR' => 25,
            'HU' => 27,
            'IE' => 23,
            'IT' => 22,
            'LV' => 21,
            'LT' => 21,
            'LU' => 17,
            'MT' => 18,
            'NL' => 21,
            'PL' => 23,
            'PT' => 23,
            'RO' => 20,
            'SE' => 25,
            'SK' => 20,
            'SI' => 22,
        );
    }

    /**
     * "Compiles" an array with tax rates to be inserted into the database
     *
     * @param      $baseCc
     * @param bool $withDigital
     * @param bool $withReduced
     * @param null $reducedRate
     *
     * @return array
     */
    public function getTaxRatesInsertArray($baseCc, $withDigital=false, $withReduced=false, $reducedRate=null)
    {
        $standardRates = $this->getStandardRates();
        $standardRate = (float)$standardRates[$baseCc];
        $insertRates = array();

        foreach($standardRates as $cC => $rate)
        {
            $insertRates[] = array(
                'tax_country_id'=> $cC,
                'code' => $cC. Mage::helper('euvatgrouper')->__(" standard VAT"),
                'rate' => (float)$standardRate
            );

            if($withReduced && !is_null($reducedRate))
            {
                $insertRates[] = array(
                    'tax_country_id'=> $cC,
                    'code' => $cC. Mage::helper('euvatgrouper')->__(" reduced VAT"),
                    'rate' => (float)$reducedRate
                );
            }

            if($withDigital)
            {
                $insertRates[] = array(
                    'tax_country_id'=> $cC,
                    'code' => $cC. Mage::helper('euvatgrouper')->__(" digital VAT"),
                    'rate' => (float)$rate
                );
            }

            $insertRates[] = array(
                'tax_country_id'=> $cC,
                'code' => $cC. Mage::helper('euvatgrouper')->__(" VAT exempt"),
                'rate' => 0.0000
            );

        }

        return $insertRates;
    }

    /**
     * "Compiles" import array for store config value changes
     *
     * @param      $baseCc
     * @param      $key
     * @param      $vatId
     *
     * @param bool $withRules
     *
     * @return array
     */
    public function getStoreConfigValues($baseCc, $key, $vatId, $withRules=false)
    {
        $config = array(
            'general/country/default'           => $baseCc,
            'tax/classes/shipping_tax_class'    => 3,
            'tax/calculation/based_on'          => 'shipping',
            'tax/defaults/country'              => $baseCc,
            'shipping/origin/country_id'        => $baseCc,
            'customer/create_account/auto_group_assign' => 0,
            'customer/create_account/vat_frontend_visibility' => 1,
            'customer/address/taxvat_show'      => 0,
            'euvatgrouper/extension_info/license_key' => $key,
            'euvatgrouper/vat_settings/own_vatid' => $this->cleanCustomerVatId($vatId),
        );

        if($withRules == '1')
        {
            $config['euvatgrouper/vat_settings/tax_class_including'] = Geissweb_Euvatgrouper_Model_Setup_Tax::TC_CUSTOMER_CONSUMER;
            $config['euvatgrouper/vat_settings/tax_class_including_business'] = Geissweb_Euvatgrouper_Model_Setup_Tax::TC_CUSTOMER_BUSINESS_DOMESTIC;
            $config['euvatgrouper/vat_settings/tax_class_excluding'] = Geissweb_Euvatgrouper_Model_Setup_Tax::TC_CUSTOMER_BUSINESS_EU;
        }

        return $config;
    }
}