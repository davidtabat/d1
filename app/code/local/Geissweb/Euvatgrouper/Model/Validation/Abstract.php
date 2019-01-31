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
class Geissweb_Euvatgrouper_Model_Validation_Abstract extends Varien_Object
{
    private $service_url = '';
	public $is_cron_validation = false;

	public function __construct()
	{
		$this->setShopCc(Mage::helper('euvatgrouper')->getShopVatCc());
		$this->setShopNr(substr(trim(str_replace(' ', '', Mage::helper('euvatgrouper')->getShopVatId())), 2));
	}

    public function getUserNr()
    {
        if(!is_null($this->user_nr)) {
            return Mage::helper('euvatgrouper')->cleanCustomerVatId($this->user_nr);
        }
        return null;
    }

    /**
     * @return bool
     */
	public function isServiceOnline()
	{
		$cs = curl_init();
		curl_setopt($cs, CURLOPT_URL, $this->service_url);
		curl_setopt($cs, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cs, CURLOPT_VERBOSE, false);
		curl_setopt($cs, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($cs, CURLOPT_TIMEOUT, 15);
		curl_exec($cs);
		$httpcode = curl_getinfo($cs, CURLINFO_HTTP_CODE);
		curl_close($cs);
		return ($httpcode == "200") ? true : false;
	}

    /**
     * @param $vatNumber    string "Complete VAT Number"
     * @param $countryCode  string "Country"
     *
     * @return bool
     */
	public function isSyntaxValid($vatNumber, $countryCode)
	{
		// Thanks to Baldwin bvba (Pieter Hoste)
		// based on http://ec.europa.eu/taxation_customs/vies/faq.html#item_11
		$regex = '';
		switch ($countryCode)
		{
			case 'AT': // Austria
				$regex = '(AT)?U[0-9]{8}';
				break;
			case 'BE': // Belgium
				$regex = '(BE)?0[0-9]{9}';
				break;
			case 'BG': // Bulgaria
				$regex = '(BG)?[0-9]{9,10}';
				break;
			case 'CY': // Cyprus
				$regex = '(CY)?[0-9]{8}[A-Z]';
				break;
			case 'CZ': // Czech Republic
				$regex = '(CZ)?[0-9]{8,10}';
				break;
			case 'DE': // Germany
				$regex = '(DE)?[0-9]{9}';
				break;
			case 'DK': // Denmark
				$regex = '(DK)?[0-9]{8}';
				break;
			case 'EE': // Estonia
				$regex = '(EE)?[0-9]{9}';
				break;
			case 'EL': // Greece
			case 'GR': // Greece
				$regex = '(EL|GR)?[0-9]{9}';
				break;
			case 'ES': // Spain
				$regex = '(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]';
				break;
			case 'FI': // Finland
				$regex = '(FI)?[0-9]{8}';
				break;
			case 'FR': // France
				$regex = '(FR)?[0-9A-Z]{2}[0-9]{9}';
				break;
			case 'GB': // United Kingdom
				$regex = '(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})';
				break;
			case 'HR': // Croatia
				$regex = '(HR)?[0-9]{11}';
				break;
			case 'HU': // Hungary
				$regex = '(HU)?[0-9]{8}';
				break;
			case 'IE': // Ireland
                $regex = '(IE)?(([0-9]{7}WI|[0-9][0-9A-Z\*\+][0-9]{5}[A-Z]{1,2}))';
				break;
			case 'IT': // Italy
				$regex = '(IT)?[0-9]{11}';
				break;
			case 'LT': // Lithuania
				$regex = '(LT)?([0-9]{9}|[0-9]{12})';
				break;
			case 'LU': // Luxembourg
				$regex = '(LU)?[0-9]{8}';
				break;
			case 'LV': // Latvia
				$regex = '(LV)?[0-9]{11}';
				break;
			case 'MT': // Malta
				$regex = '(MT)?[0-9]{8}';
				break;
			case 'NL': // Netherlands
				$regex = '(NL)?[0-9]{9}B[0-9]{2}';
				break;
			case 'PL': // Poland
				$regex = '(PL)?[0-9]{10}';
				break;
			case 'PT': // Portugal
				$regex = '(PT)?[0-9]{9}';
				break;
			case 'RO': // Romania
				$regex = '(RO)?[0-9]{2,10}';
				break;
			case 'SE': // Sweden
				$regex = '(SE)?[0-9]{12}';
				break;
			case 'SI': // Slovenia
				$regex = '(SI)?[0-9]{8}';
				break;
			case 'SK': // Slovakia
				$regex = '(SK)?[0-9]{10}';
				break;
		}

		if (preg_match('/^' . $regex . '$/i', $vatNumber))
			return true;

		return false;
	}

}