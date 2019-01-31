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

class Geissweb_Euvatgrouper_Model_Validation_Result extends Varien_Object
{

	/**
	 * Sets data from VAT service
	 *
	 * @param      $response
	 * @param null $vat_id
	 *
	 * @return $this|bool
	 */
	public function setViesData($response, $vat_id=null)
	{
		if(is_object($response))
		{
			if(!property_exists($response, 'shop_cc')) $response->shop_cc = Mage::helper('euvatgrouper')->getShopVatCc();
			if(!property_exists($response, 'user_nr') && !is_null($vat_id)) $response->user_nr = $vat_id;
			if(!property_exists($response, 'user_cc') && !is_null($vat_id)) $response->user_cc = substr($vat_id,0,2);

            if(property_exists($response, 'faultstring')) $this->setFaultstring($response->faultstring);

			$this->setShopCc($response->shop_cc);
			$this->setUserNr(Mage::helper('euvatgrouper')->cleanCustomerVatId($response->user_nr));
			$this->setVatId(Mage::helper('euvatgrouper')->cleanCustomerVatId($response->user_cc.$response->user_nr));
			$this->setUserCc($response->user_cc);
			$this->setCountryId(Mage::helper('euvatgrouper')->getVatIdCc($response->user_cc));

			// Customer address data
			$this->setAddressType($response->address_type);

			$this->setVatTraderName( (property_exists($response, 'traderName')) ? $response->traderName : null );
			$this->setVatTraderCompanyType( (property_exists($response, 'traderCompanyType')) ? $response->traderCompanyType : null );
			$this->setVatTraderAddress( (property_exists($response, 'traderAddress')) ? $response->traderAddress : null );

			$this->setVatRequestDate($response->requestDate);
			$this->setVatRequestId( (property_exists($response, 'requestIdentifier')) ? $response->requestIdentifier : null );

			$this->setVatRequestSuccess( $response->vat_request_success );

			// Used for Javascript output
			if ($response->valid == 'true')
			{
				$this->setValid(true);

				if ($response->user_cc != $response->shop_cc) {
					$this->setVatIsValid(true);
					$this->setIsVatFree(true);

				} elseif ($response->user_cc == $response->shop_cc) {
					$this->setVatIsValid(true);
					$this->setIsVatFree(false);

				} else {
					$this->setVatIsValid(false);
					$this->setIsVatFree(false);
				}
			} else {
				$this->setValid(false);
				$this->setVatIsValid(false);
				$this->setIsVatFree(false);
			}

			$this->setGroup( Mage::helper('euvatgrouper/customer')->getCustomerGroupForAccount((array)$this->getData(), $response->user_cc) );
			//$this->setTaxGroup( Mage::helper('euvatgrouper/customer')->getCustomerGroupForOrder($this->getData()), $this->getUserCc());

			return $this;
		}

		return false;
	}


}