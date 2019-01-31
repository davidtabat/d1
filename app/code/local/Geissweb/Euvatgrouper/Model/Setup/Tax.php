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

class Geissweb_Euvatgrouper_Model_Setup_Tax {

    protected $_setup;
    protected $_connection;
    protected $_helper;

    const TC_PRODUCTS_STANDART = 1;
    const TC_PRODUCTS_REDUCED = 2;
    const TC_PRODUCTS_SHIPPING_STANDART = 3;
    const TC_PRODUCTS_SHIPPING_REDUCED = 4;
    const TC_PRODUCTS_DIGITAL = 5;
    const TC_PRODUCTS_NON_TAXABLE = 6;

    const TC_CUSTOMER_CONSUMER = 7;
    const TC_CUSTOMER_BUSINESS_DOMESTIC = 8;
    const TC_CUSTOMER_BUSINESS_EU = 9;

    protected $_standardRateIds;
    protected $_reducedRateIds;
    protected $_digitalRateIds;
    protected $_exemptRateIds;

    public function __construct()
    {
        $this->_helper = Mage::helper('euvatgrouper');
        $this->_setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $this->_connection = $this->_setup->getConnection();
    }

    /**
     * Execute setup
     * @param array $post
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function runSetup(array $post)
    {
        try {

            if(isset($post['execute_vat_settings']))
            {
                $this->applyStoreConfig($post['country'], $post['license_key'], $post['vat_id'], $post['execute_tax_rules']);
            }

            if(isset($post['execute_tax_rules']))
            {
                //Truncate tables
                foreach(array('tax/tax_class', 'tax/tax_calculation_rule', 'tax/tax_calculation_rate',
                            'tax/tax_calculation_rate_title', 'tax/tax_calculation') as $tablename) {
                    $this->_connection->delete($this->_setup->getTable($tablename));
                }

                //Add tax classes
                $this->addTaxClasses(Mage::helper('euvatgrouper/setup')->getTaxClassesInsertArray());

                //Add tax rates
                $withReduced = ((float)$post['reduced_rate'] > 0) ? true : false;
                $withDigital = (isset($post['add_digital'])) ? true : false;
                $withZero = (isset($post['add_zero'])) ? true : false;
                $this->addTaxRates(Mage::helper('euvatgrouper/setup')->getTaxRatesInsertArray($post['country'], $withDigital, $withReduced, (float)$post['reduced_rate']));

                //Add tax rules
                $this->addTaxRules($withReduced, $withZero, $post['country']);

                //Update existing products and customer groups
                if(is_array($post['ptcm']))
                {
                    $this->updateProductTaxClasses($post['ptcm']);
                }
                if(is_array($post['ctcm']))
                {
                    $this->updateCustomerGroupTaxClasses($post['ctcm']);
                }
            }

            $this->setIsInstalled();
            return true;

        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

    }

    /**
     * Creates new tax classes
     * @param array $classes
     */
    private function addTaxClasses(array $classes)
    {
        foreach($classes as $class)
        {
            $this->_connection->insert($this->_setup->getTable('tax/tax_class'), $class);
        }
    }

    /**
     * Creates new tax rates
     * @param array $rates
     */
    private function addTaxRates(array $rates)
    {
        $tableTaxCalcRate = $this->_setup->getTable('tax/tax_calculation_rate');
        foreach($rates as $rate)
        {
            //Insert rate
            $this->_connection->insert($tableTaxCalcRate, $rate);

            //Sort rates for use in rules
            if(strstr($rate['code'], $this->_helper->__(' standard VAT')))
            {
                $this->_standardRateIds[] = $this->_connection->lastInsertId($tableTaxCalcRate);
            }
            if(strstr($rate['code'], $this->_helper->__(' reduced VAT')))
            {
                $this->_reducedRateIds[] = $this->_connection->lastInsertId($tableTaxCalcRate);
            }
            if(strstr($rate['code'], $this->_helper->__(' digital VAT')))
            {
                $this->_digitalRateIds[] = $this->_connection->lastInsertId($tableTaxCalcRate);
            }
            if(strstr($rate['code'], $this->_helper->__(' VAT exempt')))
            {
                $this->_exemptRateIds[] = $this->_connection->lastInsertId($tableTaxCalcRate);
            }
        }
    }

    /**
     * Creates new tax rules
     *
     * @param bool   $withReduced Also adds tax rule for reduced products
     * @param bool   $withZero    Also adds tax rule for non-taxable products
     * @param string $baseCc      Country of merchant
     */
    private function addTaxRules($withReduced=false, $withZero=false, $baseCc='')
    {
        $rule = Mage::getModel('tax/calculation_rule');
        $rule->setData(array(
            'code'                  => $this->_helper->__("Consumers buy products with standard VAT"),
            'tax_customer_class'    => array(self::TC_CUSTOMER_CONSUMER,self::TC_CUSTOMER_BUSINESS_DOMESTIC),
            'tax_product_class'     => array(self::TC_PRODUCTS_STANDART,self::TC_PRODUCTS_SHIPPING_STANDART),
            'tax_rate'              => $this->_standardRateIds,
            'priority'              => 0,
            'position'              => 0,
        ))->save();

        if($withReduced)
        {
            $rule->clearInstance();
            $rule->setData(array(
                'code'                  => $this->_helper->__("Consumers buy products with reduced VAT"),
                'tax_customer_class'    => array(self::TC_CUSTOMER_CONSUMER,self::TC_CUSTOMER_BUSINESS_DOMESTIC),
                'tax_product_class'     => array(self::TC_PRODUCTS_REDUCED,self::TC_PRODUCTS_SHIPPING_REDUCED),
                'tax_rate'              => $this->_reducedRateIds,
                'priority'              => 0,
                'position'              => 0,
            ))->save();
        }

        if(count($this->_digitalRateIds) > 0)
        {
            $rule->clearInstance();
            $rule->setData(array(
                'code'                  => $this->_helper->__("Consumers buy digital products and services"),
                'tax_customer_class'    => array(self::TC_CUSTOMER_CONSUMER),
                'tax_product_class'     => array(self::TC_PRODUCTS_DIGITAL),
                'tax_rate'              => $this->_digitalRateIds,
                'priority'              => 0,
                'position'              => 0,
            ))->save();

            $rule->clearInstance();
            $rule->setData(array(
                'code'                  => $this->_helper->__("Domestic businesses buy digital products and services"),
                'tax_customer_class'    => array(self::TC_CUSTOMER_BUSINESS_DOMESTIC),
                'tax_product_class'     => array(self::TC_PRODUCTS_DIGITAL),
                'tax_rate'              => $this->_standardRateIds,
                'priority'              => 0,
                'position'              => 0,
            ))->save();
        }

        if($withZero)
        {
            $rule->clearInstance();
            $rule->setData(array(
                'code'                  => $this->_helper->__("Not taxable products"),
                'tax_customer_class'    => array(self::TC_CUSTOMER_CONSUMER,self::TC_CUSTOMER_BUSINESS_DOMESTIC,self::TC_CUSTOMER_BUSINESS_EU),
                'tax_product_class'     => array(self::TC_PRODUCTS_NON_TAXABLE),
                'tax_rate'              => $this->_exemptRateIds,
                'priority'              => 0,
                'position'              => 0,
            ))->save();
        }

        $wrongRate = Mage::getSingleton('tax/calculation_rate')->getCollection()
            ->addFieldToFilter('code', array('like'=>$baseCc. Mage::helper('euvatgrouper')->__(' VAT exempt')))
            ->getFirstItem()->getId();
        $correctRate = Mage::getSingleton('tax/calculation_rate')->getCollection()
            ->addFieldToFilter('code', array('like'=>$baseCc. Mage::helper('euvatgrouper')->__(' standard VAT')))
            ->getFirstItem()->getId();
        if(in_array($wrongRate, $this->_exemptRateIds))
        {
            unset($this->_exemptRateIds[$wrongRate]);
            $this->_exemptRateIds[] = $correctRate;
        }

        $rule->clearInstance();
        $rule->setData(array(
            'code'                  => $this->_helper->__("EU Businesses buy VAT exempt"),
            'tax_customer_class'    => array(self::TC_CUSTOMER_BUSINESS_EU),
            'tax_product_class'     => array(self::TC_PRODUCTS_STANDART,self::TC_PRODUCTS_REDUCED,self::TC_PRODUCTS_SHIPPING_STANDART,
                                            self::TC_PRODUCTS_SHIPPING_REDUCED,self::TC_PRODUCTS_DIGITAL,self::TC_PRODUCTS_NON_TAXABLE),
            'tax_rate'              => $this->_exemptRateIds,
            'priority'              => 0,
            'position'              => 0,
        ))->save();

    }

    /**
     * Updates existing product tax classes
     * @param array $mapping
     */
    private function updateProductTaxClasses(array $mapping)
    {
        foreach($mapping as $source => $target)
        {
            $productCollection = Mage::getModel('catalog/product')->getCollection()
                                    ->addAttributeToFilter('tax_class_id', (int)$source);

            foreach ($productCollection as $product)
            {
                $product->setTaxClassId((int)$target);
                $product->getResource()->saveAttribute($product, 'tax_class_id');
            }
        }
    }

    /**
     * Updates existing customer group tax classes
     * @param array $mapping
     */
    private function updateCustomerGroupTaxClasses(array $mapping)
    {
        foreach($mapping as $source => $target)
        {
            $groupCollection = Mage::getModel('customer/group')->getCollection()
                ->addFieldToFilter('tax_class_id', (int)$source);

            foreach ($groupCollection as $group)
            {
                $group->setTaxClassId((int)$target);
                $group->save();
            }
        }
    }

    /**
     * Sets the store config for EU VAT calculation
     *
     * @param $baseCc
     * @param $key
     * @param $vatId
     * @param $withRules
     */
    private function applyStoreConfig($baseCc, $key, $vatId, $withRules)
    {
        $config = Mage::helper('euvatgrouper/setup')->getStoreConfigValues($baseCc, $key, $vatId, $withRules);
        foreach($config as $path => $value)
        {
            $this->_setup->setConfigData($path, $value);
        }

    }

    /**
     * Sets installed config flag
     */
    public function setIsInstalled()
    {
        $this->_setup->setConfigData('euvatgrouper/extension_info/is_installed', true);
    }

}