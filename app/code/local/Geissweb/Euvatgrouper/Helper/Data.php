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

use Geissweb_Euvatgrouper_Model_System_Config_Source_Validationperiod as ValidationPeriod;

class Geissweb_Euvatgrouper_Helper_Data extends Geissweb_Euvatgrouper_Helper_Abstract
{

    /**
     * @param null $lastVatRequestDate
     *
     * @return bool
     */
    public function isValidationOnEachLogin($lastVatRequestDate=null)
    {
        $doValidation = false;

        if(!is_null($lastVatRequestDate) && $lastVatRequestDate != '' && $this->isAutovalidationEnabled())
        {
            $lastDate = new DateTime(substr($lastVatRequestDate, 0, 10));
            $now = new DateTime('NOW');
            $diff = $now->diff($lastDate);

            switch($this->getAutovalidationPeriod())
            {
                case ValidationPeriod::EVERY_LOGIN:
                    $doValidation = true;
                    break;

                case ValidationPeriod::EVERY_MONTH:
                    if($diff->m >= 1)
                        $doValidation = true;
                    break;

                case ValidationPeriod::EVERY_SIX_MONTHS:
                    if($diff->m >= 6)
                        $doValidation = true;
                    break;

                case ValidationPeriod::EVERY_THREE_MONTHS:
                    if($diff->m >= 3)
                        $doValidation = true;
                    break;

                case ValidationPeriod::EVERY_YEAR:
                    if($diff->m >= 12)
                        $doValidation = true;
                    break;

                default:$doValidation = false;break;
            }

        }
        return $doValidation;
    }

	/**
	 * @param $vatNumber
	 * @param $address
	 *
	 * @return mixed|void
	 */
    public function quickValidate($vatNumber, $address)
    {
        if($vatNumber == '' || !is_object($address)) return;

        $vatNumber = $this->cleanCustomerVatId($vatNumber);

        $validator = Mage::getSingleton("euvatgrouper/validation_vies");
        $validator->setAddressId($address->getId());
        $validator->setAddressType($address->getAddressType());

        $validator->setUserCc(strtoupper(substr($vatNumber, 0, 2)));
        $validator->setUserNr(substr($vatNumber, 2));

        $validator->validate();
        $result = $validator->getResult();

        return $this->setValidationResultOnAddress($result, $address);

    }


    /**
     * Adds VAT info on address
     *
     * @param $billingAddress
     * @param $shippingAddress
     *
     * @return mixed
     */
    public function getVatBasedOnAddress($billingAddress, $shippingAddress)
    {
        //$vatInfo = Mage::getSingleton('customer/session')->getData('_vatinfo'); // VAT validation data
        $vatBasedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, Mage::app()->getStore()->getId());
        //$useAddress = ($vatBasedOn == 'shipping') ? $shippingAddress : $billingAddress;
        $basedOnAddress = ($vatBasedOn == 'shipping') ? $shippingAddress : $billingAddress;
        //if( $this->_debug ) Mage::log("[EUVAT] getVatBasedOnAddress: ".var_export($basedOnAddress->debug(), true), null, 'euvatenhanced.log');

        if(is_object($basedOnAddress))
        {
            $quote = $basedOnAddress->getQuote();
            if(is_object($quote))
            {
                if($quote->getVirtualItemsQty() == $quote->getItemsCount())
                    $basedOnAddress = $billingAddress;

                //if($this->_debug) Mage::log("[EUVAT] getVatBasedOnAddress quote is: ".var_export($quote->getData(),true), null, 'euvatenhanced.log');
            }
        }


        /*
        if(is_object($basedOnAddress)
            && (int)$basedOnAddress->getCustomerAddressId() > 0
            && $basedOnAddress->getVatId() != '')
        {
            $customerAddress = Mage::getSingleton('customer/address')->load($basedOnAddress->getCustomerAddressId());
            if($this->_debug) Mage::log("[EUVAT] LOADING [".$basedOnAddress->getCustomerAddressId()."] Country:[".$customerAddress->getCountry()."] Type:[".$customerAddress->getAddressType()."]", null, 'euvatenhanced.log');

            if($customerAddress->getCountry() == $basedOnAddress->getCountryId()
                && $basedOnAddress->getVatId() == $customerAddress->getVatId())
            {
                $basedOnAddress
                    ->setVatId($customerAddress->getVatId())
                    ->setVatIsValid($customerAddress->getVatIsValid())
                    ->setVatRequestSuccess($customerAddress->getVatRequestSuccess())
                    ->setVatRequestId($customerAddress->getVatRequestId())
                    ->setVatRequestDate($customerAddress->getVatRequestDate())
                    ->setVatTraderName($customerAddress->getVatTraderName())
                    ->setVatTraderAddress($customerAddress->getVatTraderAddress());
            }

        }
        */

        return $basedOnAddress;
    }

	/**
	 * Clears VAT info from address
	 *
	 * @param $address
	 *
	 * @return mixed
	 */
	public function clearVatInfoFromAddress($address)
	{
		$address->setVatId(null)
			->setVatIsValid(null)
			->setVatRequestId(null)
			->setVatRequestDate(null)
			->setVatRequestSuccess(null)
			->setVatTraderName(null)
			->setVatTraderAddress(null)
			->setVatTraderCompanyType(null);
		//if($this->_debug) Mage::log("[EUVAT] clearVatInfoFromAddress Address[".get_class($address)."]: ".var_export($address->debug(),true), null, 'euvatenhanced.log');
		return $address;
	}


	/**
	 * Simple debug view of the relevant address data
	 *
	 * @param $address
	 *
	 * @return array|bool
	 */
	public function stripAddressForDebug($address)
	{
		if(is_object($address))
		{
			$debug = array(
				'country_id' => $address->getCountryId(),
				'vat_id' => $address->getVatId(),
				'address_type' => $address->getAddressType(),
				'vat_is_valid' => $address->getVatIsValid()
			);
			return $debug;
		}

		return false;
	}


	/**
	 * Set the user to the default user group
	 *
	 * @param int    $address_id
	 * @param string $address_type
	 *
	 * @return Geissweb_Euvatgrouper_Model_Validation_Result
	 */
	public function assignDefault($address_id=0, $address_type='billing')
	{
		$result = Mage::getSingleton('euvatgrouper/validation_result');
		$result->setShopCc($this->getShopVatCc());
		$result->setUserNr(null);
		$result->setUserCc(null);
		$result->setCountryId(null);
		$result->setAddressType($address_type);
		$result->setTraderName(null);
		$result->setTraderCompanyType(null);
		$result->setTraderAddress(null);
		$result->setRequestDate(null);
		$result->setRequestIdentifier(null);
		$result->setVatRequestSuccess(null);
		$result->setValid(null);
		$result->setVatIsValid(null);
		$result->setIsVatFree(null);
		$result->setVatIdRemoved(true);

		//Save updated validation data to customer's address
		Mage::dispatchEvent('vat_check_after', array(
			'result' 			=> $result,
			'address_id' 		=> $address_id
		));

		return $result;
	}

	/**
	 * Compares two addresses if they are the same
	 * @param null $billing_address
	 * @param null $shipping_address
	 *
	 * @return bool
	 */
	public function isSameAddress($billing_address=null, $shipping_address=null)
	{
		/** @var $billing_address Mage_Sales_Model_Quote_Address */
		/** @var $shipping_address Mage_Sales_Model_Quote_Address */
		if(!is_null($billing_address) && !is_null($shipping_address))
		{
			$billingSerial = serialize(array(
				'postcode'  => $billing_address->getPostcode(),
				'city'      => $billing_address->getCity(),
				'region'	=> $billing_address->getRegion(),
				'country'	=> $billing_address->getCountryId(),
			));

			$shippingSerial = serialize(array(
				'postcode'  => $shipping_address->getPostcode(),
				'city'      => $shipping_address->getCity(),
				'region'	=> $shipping_address->getRegion(),
				'country'	=> $shipping_address->getCountryId(),
			));

			if (strcmp($billingSerial, $shippingSerial) != 0) {
				if($this->_debug) Mage::log("[EUVAT] isSameAddress? NO", null, 'euvatenhanced.log');
				return false;
			}

			if($this->_debug) Mage::log("[EUVAT] isSameAddress? YES", null, 'euvatenhanced.log');
			return true;
		}
	}


	/**
	 * Adds VAT validation result data on a address (or object)
	 * @param $result "Validation result"
	 * @param $address "Address or object"
	 *
	 * @return mixed
	 */
	public function setValidationResultOnAddress($result, $address)
	{
		if(is_object($result) && is_object($address))
		{
            $vatIdCc = $this->getVatIdCc($result->getVatId());
            $cleanVatId = $this->cleanCustomerVatId($result->getVatId());

			$address->setVatId($cleanVatId)
				->setVatIsValid((bool)$result->getVatIsValid())
				->setVatRequestId((string)$result->getVatRequestId())
				->setVatRequestDate((string)$result->getVatRequestDate())
				->setVatRequestSuccess((bool)$result->getVatRequestSuccess())
				->setVatTraderName((string)$result->getVatTraderName())
				->setVatTraderAddress((string)$result->getVatTraderAddress())
				->setVatTraderCompanyType((string)$result->getVatTraderCompanyType());

            //TODO
            if($vatIdCc != '' && strlen($vatIdCc) >= 2 && $address->getCountryId() == '')
                $address->setCountryId($vatIdCc);

			if($this->_debug) Mage::log('Added validation data on address (ID:'.$address->getId().')', null, 'euvatenhanced.log');
			return $address;
		}
		if($this->_debug) Mage::log('No result added on address.', null, 'euvatenhanced.log');
		return $address;
	}

	/**
	 * Evaluates if the given (quote) address has relevant address data set, or is a plain address object
	 * @param $address
	 * @return bool
	 */
	public function isPlainAddress($address)
	{
		if(is_object($address))
		{
			$countryId = $address->getCountryId();
			$vatId = $address->getVatId();

			if(empty($countryId) && empty($vatId))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Evaluates if the given (quote) address has relevant address data set, or is a plain address object
	 * @param $address
	 * @return bool
	 */
	public function isPlainQuote($address)
	{
		/**@var $address Mage_Sales_Model_Quote_Address */
		if(is_object($address))
		{
			$street = $address->getStreet1();
			$city = $address->getCity();
			$postcode = $address->getPostcode();

			if(empty($street) && empty($city) && empty($postcode))
			{
				return true;
			}
		}

		return false;
	}

}