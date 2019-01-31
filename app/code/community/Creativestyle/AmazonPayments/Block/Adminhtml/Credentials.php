<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Block_Adminhtml_Credentials extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _validateCredentials() {
        $validation = array();
        $configData = $this->getConfigData();
        $merchantId = isset($configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_ACCOUNT_MERCHANT_ID])
            ? $configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_ACCOUNT_MERCHANT_ID]
            : null;
        $accessKey = isset($configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_ACCOUNT_ACCESS_KEY])
            ? $configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_ACCOUNT_ACCESS_KEY]
            : null;
        $secretKey = isset($configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_ACCOUNT_SECRET_KEY])
            ? $configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_ACCOUNT_SECRET_KEY]
            : null;
        $clientId = isset($configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_LOGIN_CLIENT_ID])
            ? $configData[Creativestyle_AmazonPayments_Model_Config::XML_PATH_LOGIN_CLIENT_ID]
            : null;

        if (empty($merchantId)) {
            $validation[] = 'Merchant ID';
        }

        if (empty($accessKey) || !preg_match('/^[a-z0-9]{20}$/i', trim($accessKey))) {
            $validation[] = 'Access Key ID';
        }

        if (empty($secretKey) || preg_match('/\\s{1,}/', trim($secretKey))) {
            $validation[] = 'Secret Access Key';
        }

        if (empty($clientId)) {
            $validation[] = 'Client ID';
        }

        return $validation;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $validation = $this->_validateCredentials();
        if (empty($validation)) {
            return '';
        } else {
            $html = '<ul class="messages"><li class="error-msg"><p>'
                . $this->__('It seems that following config options are either empty or invalid')
                .'</p><ul>';
            foreach ($validation as $field) {
                $html .= '<li>' . $this->__($field) . '</li>';
            }

            $html .= '</ul></li></ul>';
            return $html;
        }
    }
}
