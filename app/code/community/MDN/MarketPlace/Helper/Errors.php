<?php

/**
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
 */
class MDN_MarketPlace_Helper_Errors extends Mage_Core_Helper_Abstract implements MDN_MarketPlace_Helper_Interface_Errors {
    /* @var array */

    protected $_errorsOptions = null;

    /**
     * Get base error
     * 
     * @return array $errors 
     */
    public function getBaseErrors() {

        $errors = array(
            '0' => mage::helper('MarketPlace')->__('No error'),
            '1' => mage::helper('MarketPlace')->__('Get orders from marketplace'),
            '2' => mage::helper('MarketPlace')->__('Order(s) already imported'),
            '3' => mage::helper('MarketPlace')->__('Invalid product'),
            '4' => mage::helper('MarketPlace')->__('Update marketplace stock'),
            '9' => mage::helper('MarketPlace')->__('Bad query'),
            '10' => mage::helper('MarketPlace')->__('Can\'t write file'),
            '11' => mage::helper('MarketPlace')->__('Invalid file'),
            '12' => mage::helper('MarketPlace')->__('Error Response'),
            '13' => mage::helper('MarketPlace')->__('No more available request'),
            '14' => mage::helper('MarketPlace')->__('Check product creation'),
            '15' => mage::helper('MarketPlace')->__('Undefined Attribute'),
            '16' => mage::helper('MarketPlace')->__('Send tracking'),
            '17' => mage::helper('MarketPlace')->__('Unavailable carrier code'),
            '18' => mage::helper('MarketPlace')->__('Unable to retrieve some required informations'),
            '19' => mage::helper('MarketPlace')->__('Mass product creation'),
            '20' => mage::helper('MarketPlace')->__('URL called feed'),
            '21' => Mage::helper('MarketPlace')->__('Internationalization'),
            '22' => Mage::Helper('MarketPlace')->__('Matching EAN'),
            '98' => mage::helper('MarketPlace')->__('Notice'),
            '99' => mage::helper('MarketPlace')->__('Warning')
        );

        return $errors;
    }

    /**
     * Get errors tab
     *
     * @return array
     */
    public function getErrorsOptions() {

        $errors = $this->getBaseErrors();

        $helpers = Mage::helper('MarketPlace')->getHelpers();

        foreach ($helpers as $helper) {

            $helper = Mage::helper($helper);

            if ($helper->hasSpecificErrors()) {

                $tmp = Mage::helper(ucfirst($helper->getMarketPlaceName()) . '/errors')->getErrorsOptions();

                foreach ($tmp as $k => $v) {

                    if (!array_key_exists($k, $errors))
                        $errors[$k] = $v;
                }
            }
        }

        return $errors;
    }

    /**
     * get errors options for one mp
     * 
     * @param string $mp
     * @return array 
     */
    public function getErrorsOptionsFor($mp) {

        if ($this->_errorsOptions === null) {

            $this->_errorsOptions = $this->getBaseErrors();

            if (Mage::Helper(ucfirst($mp))->hasSpecificErrors()) {

                $tmp = Mage::Helper(ucfirst($mp) . '/errors')->getErrorsOptions();

                foreach ($tmp as $k => $v) {

                    if (!array_key_exists($k, $this->_errorsOptions))
                        $this->_errorsOptions[$k] = $v;
                }
            }
        }

        return $this->_errorsOptions;
    }

    /**
     * Get error label
     * 
     * @param int $id
     * @param string $mp
     * @return string $retour 
     */
    public function getErrorLabel($id, $mp) {

        $retour = '';

        $options = $this->getErrorsOptionsFor($mp);

        if (array_key_exists($id, $options)) {

            $retour = $options[$id];
        }

        return $retour;
    }
    
    public function formatErrorMessage($e){
        
        $config = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject();
        
        $message = '';
        $message .= $e->getMessage();
        if($config->getmp_stack_trace() == 1)
            $message .= "\n\n".$e->getTraceAsString()."\n\n";
        
        $message .= ' (<a target="_blanck" href="http://www.boostmyshop.com/docs/controller.php?action=index&autoSelectPath=%2Fdocs%2FAmazon%2F7+-+FAQ%2F">'.$this->__('View FAQ').'</a>)';
        
        $message = str_replace("\n", '<br/>', $message);
        
        return $message;
        
    }

}
