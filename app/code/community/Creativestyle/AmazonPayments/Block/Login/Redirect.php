<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2015 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2015 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

/**
 * @method $this setAccessTokenParamName(string $accessToken)
 * @method $this setRedirectUrl(string $redirectUrl)
 * @method $this setFailureUrl(string $failureUrl)
 */
class Creativestyle_AmazonPayments_Block_Login_Redirect extends Creativestyle_AmazonPayments_Block_Login_Abstract
{
    /**
     * Returns access token param name
     *
     * @return string
     */
    public function getAccessTokenParamName()
    {
        if ($this->hasData('access_token_param_name')) {
            return $this->getData('access_token_param_name');
        }

        return 'access_token';
    }

    /**
     * Returns redirect URL
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_getUrl()->getPaySuccessUrl('%s');
    }

    /**
     * Returns redirect URL
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_getUrl()->getLoginRedirectUrl();
    }

    /**
     * Returns failure URL
     *
     * @return string
     */
    public function getFailureUrl()
    {
        if ($this->hasData('failure_url')) {
            return $this->getData('failure_url');
        }

        return $this->getUrl('customer/account/login');
    }
}
