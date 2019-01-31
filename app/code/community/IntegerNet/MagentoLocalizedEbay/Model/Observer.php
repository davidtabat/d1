<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_MagentoLocalizedEbay
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

class IntegerNet_MagentoLocalizedEbay_Model_Observer
{
    public function magentoLocalizedFormSave($observer)
    {
        $params = $observer->getParams();

        if (isset($params['billsafe_install']) && $params['billsafe_install'] == 1) {

            Mage::getSingleton('magento_localized/installer')->installPackageByName('billsafe/billsafe');

            $this->_setConfigData('payment/billsafe/active', $params['billsafe_active']);
            $this->_setConfigData('payment/billsafe/sandbox', $params['billsafe_sandbox']);
            $this->_setConfigData('payment/billsafe/merchant_id', $params['billsafe_merchant_id']);
            $this->_setConfigData('payment/billsafe/merchant_license', $params['billsafe_merchant_license']);
            $this->_setConfigData('payment/billsafe_installment/active', $params['billsafe_installment_active']);
            $this->_setConfigData('payment/billsafe_installment/sandbox', $params['billsafe_installment_sandbox']);
        }

        if ($params['paypal_active'] && !Mage::getStoreConfigFlag('payment/paypal_express/active')) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magento_localized_ebay')->__(
                'You have successfully activated PayPal payment. Anytime you have the possibility to select advanced settings <a href="%s">here</a>.',
                Mage::helper('adminhtml')->getUrl('adminhtml/magentolocalizedebay/paypal')
            ));
        }

        $this->_setConfigData('payment/paypal_express/active', $params['paypal_active']);
        $this->_setConfigData('paypal/wpp/sandbox_flag', $params['paypal_sandbox']);
        $this->_setConfigData('paypal/general/business_account', $params['paypal_express_email']);
        $this->_setConfigData('paypal/wpp/api_username', $this->_encrypt($params['paypal_api_username']));
        $this->_setConfigData('paypal/wpp/api_password', $this->_encrypt($params['paypal_api_password']));
        $this->_setConfigData('paypal/wpp/api_signature', $this->_encrypt($params['paypal_api_signature']));

        if (isset($params['m2e_install']) && $params['m2e_install'] == 1) {

            Mage::getSingleton('magento_localized/installer')->installPackageByName('ess/m2e-pro');
            /*
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('magento_localized_ebay')->__(
                'M2E Pro is installed, but not configured yet. Click <a href="%s">here</a> to start the wizard.',
                Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_wizard_main/')
            ));
            */
        }
    }

    /**
     * Set configuration data
     *
     * @param string $key
     * @param string|int $value
     * @param string $scope
     * @param int $scopeId
     */
    protected function _setConfigData($key, $value, $scope = 'default', $scopeId = 0)
    {
        Mage::getModel('eav/entity_setup', 'core_setup')->setConfigData($key, $value, $scope, $scopeId);
    }

    /**
     * Encrypt value before saving
     *
     * @param string $value
     * @return string
     */
    protected function _encrypt($value)
    {
        // don't change value, if an obscured value came
        if (preg_match('/^\*+$/', $value)) {
            return $value;
        }
        if (!empty($value) && ($encrypted = Mage::helper('core')->encrypt($value))) {
            return $encrypted;
        }

        return $value;
    }

}