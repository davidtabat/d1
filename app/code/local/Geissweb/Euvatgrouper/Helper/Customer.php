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
 * @category    Mage
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Helper_Customer extends Geissweb_Euvatgrouper_Helper_Abstract
{
	/**
	 * Try to return the best valid country code of customer
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param null                         $preferredCc
	 *
	 * @return mixed
	 */
	public function getBillingCountry($customer, $preferredCc=null)
	{
		$customerCcs = array();

		if(!is_null($preferredCc)) $customerCcs[] = $preferredCc;

		if(Mage::getSingleton('checkout/session')->hasQuote()) {
			$customerCcs[] = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getCountryId();
		}

		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			if(!is_object($customer)) $customer = Mage::getSingleton('customer/session')->getCustomer();
			$default_billing_address = $customer->getDefaultBillingAddress();
			if(is_object($default_billing_address))	$customerCcs[] = $default_billing_address->getCountryId();
		}

		// On customer registration
		$customerCcs[] = Mage::app()->getRequest()->getParam('country_id');

		// If no other countryId is available
		$customerCcs[] = $this->getMerchantCountryId();

		if ($this->_debug) { $_debug_msg = '|'; foreach ($customerCcs as $cc) { $_debug_msg .= $cc.'|'; } Mage::log("[EUVAT] Billing-CCs: ".$_debug_msg, null, 'euvatenhanced.log'); Mage::log("[EUVAT] PreferredCc is $preferredCc", null, 'euvatenhanced.log'); }

		foreach ($customerCcs as $cc) {
			if ($cc != NULL)
				return $cc;
		}

		return false;
	}

	/**
	 * Returns the Quote Shipping Country or Customers default Shipping Country
	 * @param Mage_Customer_Model_Customer $customer
	 * @return mixed
	 */
	public function getShippingCountry($customer)
	{
		$customerCcs = array();

		if(Mage::getSingleton('checkout/session')->hasQuote()) {
			$customerCcs[] = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCountryId();
		}

		$customerCcs[] = Mage::getSingleton('sales/quote')->getShippingAddress()->getCountryId();

		//if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			if(!is_object($customer)) $customer = Mage::getSingleton('customer/session')->getCustomer();
			$default_shipping_address = $customer->getDefaultShippingAddress();
			if(is_object($default_shipping_address)) $customerCcs[] = $default_shipping_address->getCountryId();
		//}

		$customerCcs[] = $this->getMerchantCountryId();

		if ($this->_debug) { $_debug_msg = '|'; foreach ($customerCcs as $cc) { $_debug_msg .= $cc.'|'; } Mage::log("[EUVAT] Shipping-CCs: ".$_debug_msg, null, 'euvatenhanced.log');	}

		foreach ($customerCcs as $cc) {
			if ($cc != NULL)
				return $cc;
		}
	}


	/**
	 * Try to return the best fitting group for the customer account
	 * based of supplied data
	 *
	 * gitmergebla
	 *
	 * @param array $vatdata
	 * @param null  $customerCc
	 *
	 * @return mixed
	 */
	public function getCustomerGroupForAccount(array $vatdata, $customerCc=null)
	{
		if($this->_debug) Mage::log("[EUVAT] Function RUNNING: getCustomerGroupForAccount(vatdata, $customerCc)", null, 'euvatenhanced.log');
        if($this->_debug) Mage::log("[EUVAT] Validation Data: ".var_export($vatdata,true), null, 'euvatenhanced.log');
		$shopCc = $this->getMerchantCountryId();

        if(is_array($vatdata) && isset($vatdata['country_id']))
        {
            $customerCc = $vatdata['country_id'];
            /*
            if(isset($vatdata['vat_id'])
                && $vatdata['vat_id'] != ''
                && $this->getVatIdCc($vatdata['vat_id']) != $customerCc)
                $customerCc = $this->getVatIdCc($vatdata['vat_id']);
            */
            if($this->_debug) Mage::log("[EUVAT] Getting customer group for VAT country: $customerCc VatdataCC[".$vatdata['country_id']."] (ShopCC: $shopCc)", null, 'euvatenhanced.log');

        } else {
            $customerCc = $this->getBillingCountry(Mage::getSingleton('customer/session')->getCustomer(), $customerCc);
            if ($this->_debug) Mage::log("[EUVAT] Getting customer group for billing country: $customerCc  (ShopCC: $shopCc)", null, 'euvatenhanced.log');
        }

        // Fix Greece
        if($customerCc == "EL") $customerCc = "GR";

		// Check the validation data and return best fitting group
		if (is_array($vatdata) && isset($vatdata['vat_is_valid']) && isset($vatdata['vat_id']) && $vatdata['vat_id'] != '')
		{
			if ($vatdata['vat_is_valid'] == true
                && $vatdata['vat_request_success'] == true
                && $shopCc != $customerCc
                && $this->isEuCountry($customerCc))
            {
				if($this->_debug) Mage::log("[EUVAT] Valid VAT-exempt -> GRP[" . $this->getValidEuVatGroupId() . "]", null, 'euvatenhanced.log');
				return (int)$this->getValidEuVatGroupId();

			} elseif ($vatdata['vat_is_valid'] == true
                && $vatdata['vat_request_success'] == true
                && $shopCc == $customerCc
                && $this->isEuCountry($customerCc))
            {
                if($this->_debug) Mage::log("[EUVAT] Valid own Country -> GRP[" . $this->getSameCountryGroupId() . "]", null, 'euvatenhanced.log');
                return (int)$this->getSameCountryGroupId();

            } elseif($vatdata['vat_is_valid'] == false && $vatdata['vat_request_success'] == true ) {
                if( $this->_debug ) Mage::log("[EUVAT] InValid VAT Number -> GRP[" . $this->getInvalidGroupId() . "]", null, 'euvatenhanced.log');
                return (int)$this->getInvalidGroupId();

            } elseif($vatdata['vat_request_success'] == false) {
                if($this->_debug) Mage::log("[EUVAT] Error during VAT Number validation -> GRP[" . $this->getErrorGroupId() . "]", null, 'euvatenhanced.log');
                return (int)$this->getErrorGroupId();

			} else {
				if (!$this->isEuCountry($customerCc)) {
					if($this->_debug) Mage::log("[EUVAT] OUTSIDE EU -> GRP[" . $this->getOutsideEuGroupId() . "]", null, 'euvatenhanced.log');
					return (int)$this->getOutsideEuGroupId();

				} else {
					if($this->_debug) Mage::log("[EUVAT] DEFAULT", null, 'euvatenhanced.log');
					return (int)$this->getDefaultGroupId();
				}
			}

		// No validation data, try to identify country outside EU
		} else {
			if (!$this->isEuCountry($customerCc)) {
				if($this->_debug) Mage::log("[EUVAT] OUTSIDE EU2 -> GRP[" . $this->getOutsideEuGroupId() . "]", null, 'euvatenhanced.log');
				return (int)$this->getOutsideEuGroupId();
			} else {
				if($this->_debug) Mage::log("[EUVAT] DEFAULT2", null, 'euvatenhanced.log');
				return (int)$this->getDefaultGroupId();
			}
		}

	}


	/**
	 * Try to return the best fitting customer group for the order
	 * based of supplied data
	 *
	 * @param $basedOnAddress
	 * @return mixed
	 */
	public function getCustomerGroupForOrder($basedOnAddress)
	{
		if($this->_debug) Mage::log("[EUVAT] Function RUNNING: getCustomerGroupForOrder(address)", null, 'euvatenhanced.log');

		// Do we have VAT data to evalualte?
		if( $this->isModifyOrderGroup() && is_object($basedOnAddress))
		{
			$merchantCc = $this->getShopVatCc();
			$customerCc = $basedOnAddress->getCountryId();
			if(!$customerCc || $customerCc == '') $customerCc = $this->getShippingCountry(Mage::getSingleton('customer/session')->getCustomer(), $merchantCc);

			// Set customer group to default when Tax was applied
			// may be required for accounting/ERP applications like Sage50
			if(Mage::helper('euvatgrouper')->getAccountingFix())
			{
				$quote = Mage::getSingleton('checkout/session')->getQuote();
				$based_on = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, Mage::app()->getStore()->getId());
				if($based_on == 'billing') {
					if($quote->getBillingAddress()->getTaxAmount() > 0) {
						return (int)$this->getDefaultGroupId();
					}
				} elseif( $based_on == 'shipping' ) {
					if($quote->getShippingAddress()->getTaxAmount() > 0) {
						return (int)$this->getDefaultGroupId();
					}
				}
			}

			if ($basedOnAddress->getVatId() != ''
				&& $basedOnAddress->getVatIsValid() == true
				&& $merchantCc != $customerCc
				&& $this->isEuCountry($customerCc)
                && $basedOnAddress->getVatRequestSuccess() == true)
			{
				if ($this->_debug) Mage::log("[EUVAT] Valid VAT-exempt -> GRP[" . $this->getValidEuVatGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getValidEuVatGroupId()) . "]", null, 'euvatenhanced.log');
				return (int)$this->getValidEuVatGroupId();

			} elseif ($basedOnAddress->getVatId() != ''
				&& $basedOnAddress->getVatIsValid() == true
				&& $merchantCc == $customerCc
				&& $this->isEuCountry($customerCc)
                && $basedOnAddress->getVatRequestSuccess() == true)
			{
				if ($this->_debug) Mage::log("[EUVAT] Valid own Country -> GRP[" . $this->getSameCountryGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getSameCountryGroupId()) . "]", null, 'euvatenhanced.log');
				return (int)$this->getSameCountryGroupId();

            } elseif($basedOnAddress->getVatId() != '' &&
				$basedOnAddress->getVatIsValid() == false
                && $basedOnAddress->getVatRequestSuccess() == true )
            {
                if( $this->_debug ) Mage::log("[EUVAT] InValid VAT Number -> GRP[" . $this->getInvalidGroupId() . "]", null, 'euvatenhanced.log');
                return (int)$this->getInvalidGroupId();

            } elseif($basedOnAddress->getVatId() != '' && $basedOnAddress->getVatRequestSuccess() == false) {
                if( $this->_debug ) Mage::log("[EUVAT] Error during VAT Number validation -> GRP[" . $this->getErrorGroupId() . "]", null, 'euvatenhanced.log');
                return (int)$this->getErrorGroupId();

			} else {
				if (!$this->isEuCountry($customerCc)) {
					if ($this->_debug) Mage::log("[EUVAT] OUTSIDE EU -> GRP[" . $this->getOutsideEuGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getOutsideEuGroupId()) . "]", null, 'euvatenhanced.log');
					return (int)$this->getOutsideEuGroupId();
				} else {
					if ($this->_debug) Mage::log("[EUVAT] DEFAULT -> GRP[" . $this->getDefaultGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getDefaultGroupId()) . "]", null, 'euvatenhanced.log');
					return (int)$this->getDefaultGroupId();
				}
			}

		}

		return false;
	}
}