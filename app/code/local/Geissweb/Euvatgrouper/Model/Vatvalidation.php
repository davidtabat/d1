<?php

/**
 * ||GEISSWEB| EU-VAT-GROUPER
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@geissweb.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support at support@geissweb.de.
 *
 * @category     Mage
 * @package      Geissweb_Euvatgrouper
 * @copyright    Copyright (c) 2012 GEISS Weblösungen (http://www.geissweb.de)
 * @license      http://www.geissweb.de/eula.html GEISSWEB End User License Agreement
 */
class Geissweb_Euvatgrouper_Model_Vatvalidation extends Varien_Object
{

    protected $_eventPrefix = 'euvatgrouper_vatvalidation';
    protected $_eventObject = 'vatvalidation';
    protected $_viesUrl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
    protected $_viesUrlBackup = 'http://www.geissweb.de/feeds/vies.wsdl'; //Known as working wsdl

    public $shop_nr = "";
    public $shop_cc = "";
    public $user_cc = "";
    public $user_nr = "";
    public $user_taxvat = "";

    /**
     * Initialise the validator
     */
    public function __construct()
    {
        parent::__construct();

        try {

			if(Mage::helper('euvatgrouper')->isIPv6Mode())
			{
				$opts = array('socket' => array('bindto' => Mage::helper('euvatgrouper')->getIPv4ToBindOn().':0'));
				$context = stream_context_create($opts);
				$this->setClient(new SoapClient($this->_viesUrl, array('exceptions' => 0, 'trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE, 'soap_version' => SOAP_1_1, 'user_agent' => 'Mozilla', 'stream_context' => $context)));
			} else {
				$this->setClient(new Zend_Soap_Client($this->_viesUrl, array('soap_version'=>SOAP_1_1, 'user_agent' => 'Magento Webshop', 'cache_wsdl'=> WSDL_CACHE_NONE)));
			}

        } catch (SoapFault $s) {

			Mage::logException($s);

			try { //Retry with known as working eu-vies wsdl definitions

				if(Mage::helper('euvatgrouper')->isIPv6Mode())
				{
					$opts = array('socket' => array('bindto' => Mage::helper('euvatgrouper')->getIPv4ToBindOn().':0'));
					$context = stream_context_create($opts);
					$this->setClient(new SoapClient($this->_viesUrlBackup, array('exceptions' => 0, 'trace' => false, 'cache_wsdl' => WSDL_CACHE_NONE, 'soap_version' => SOAP_1_1, 'user_agent' => 'Mozilla', 'stream_context' => $context)));
				} else {
					$this->setClient(new Zend_Soap_Client($this->_viesUrlBackup, array('soap_version'=>SOAP_1_1, 'user_agent' => 'Magento Webshop', 'cache_wsdl'=> WSDL_CACHE_NONE)));
				}
			} catch(SoapFault $s) {
				Mage::logException($s);
			}
        }

        $this->setShopCc(Mage::helper('euvatgrouper')->getShopVatCc());
        $this->setShopNr(substr(trim(str_replace(" ", "", Mage::helper('euvatgrouper')->getShopVatId())), 2));
    }

    /**
     * Do validation
     */
    public function validate()
    {
        if (Mage::helper('euvatgrouper')->isValidationEnabled() && $this->isViesOnline())
        {

            if ($this->getUserCc() != "" && $this->getUserNr() != "" && $this->getShopCc() != "" && $this->getShopNr() != "")
            {
                // Memorize the country code
                Mage::getSingleton('customer/session')->setData('_vatgrouper_cc', $this->getUserCc());

                // Advanced VAT validation function with return of request identifier
                $this->result = $this->getClient()->checkVatApprox(array(
                    'countryCode' => $this->getUserCc(),
                    'vatNumber' => $this->getUserNr(),
                    'requesterCountryCode' => $this->getShopCc(),
                    'requesterVatNumber' => $this->getShopNr(),
                ));

                // In case of error, try to use at least the normal validation if the advanced was not successful
                if (is_soap_fault($this->result))
				{
                    $this->result = $this->getClient()->checkVat(array('countryCode' => $this->getUserCc(),
                        'vatNumber' => $this->getUserNr()));
                }

                // When request was successful
                if (!is_soap_fault($this->result))
				{
                    $this->result->last_vat_validation_date = date("Y-m-d H:i:s", time());
                    $this->result->validresult = (int)$this->result->valid;

                    //Update ResultLog
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                        if ($customer->getViesResultData() != "") {
                            $this->result->viesdata = $customer->getViesResultData();
                        }
                    }
                    $this->result->viesdata .= "DATE[" . $this->result->requestDate . "] VATID[" . $this->result->countryCode . $this->result->vatNumber . "] ";
                    $this->result->viesdata .= ($this->result->valid == 'true') ? 'VALID[YES] ' : 'VALID[[NO] ';
                    $this->result->viesdata .= (isset($this->result->requestIdentifier)) ? "REQID[" . $this->result->requestIdentifier . "] " : '';
                    if (isset($this->result->traderName) && isset($this->result->traderAddress)) {
                        $this->result->viesdata .= "DETAILS[";
                        $this->result->viesdata .= ($this->result->traderName != "") ? str_replace(array("\r\n", "\r", "\n", "\t"), "", $this->result->traderName) . " -- " : "";
                        $this->result->viesdata .= ($this->result->traderAddress != "") ? str_replace(array("\r\n", "\r", "\n", "\t"), " ", $this->result->traderAddress) : "";
                        $this->result->viesdata .= "]";
                    } else {
                        $this->result->traderName = "";
                        $this->result->traderAddress = "";
                    }
                    $this->result->viesdata .= "\n";


                    if ($this->result->valid == 'true') {
                        if ($this->getUserCc() != $this->getShopCc()) {
                            $this->result->valid_vat = 1;
                            $this->result->is_vat_free = 1;
                        } elseif ($this->getUserCc() == $this->getShopCc()) {
                            $this->result->valid_vat = 1;
                            $this->result->is_vat_free = 0;
                        } else {
                            $this->result->valid_vat = 0;
                            $this->result->is_vat_free = 0;
                        }
                    } else {
                        $this->result->valid_vat = 0;
                        $this->result->is_vat_free = 0;
                    }

                    // Validation data for the user session
                    Mage::getSingleton('customer/session')->setData('_vatgrouper', array(
                        'last_vat_validation_date' => $this->result->last_vat_validation_date,
                        'vies_result_data' => $this->result->viesdata,
                        'vat_validation_result' => $this->result->valid_vat,
                        'customer_taxvat_is_valid' => ($this->result->valid_vat == 1) ? true : false,
                        'customer_is_vat_free' => ($this->result->is_vat_free == 1) ? true : false,
                        'customer_taxvat_from_validation' => $this->result->countryCode . $this->result->vatNumber,
                        'traderName' => ($this->result->traderName != "") ? $this->result->traderName : "",
						'customer_taxvat_cc' => $this->getUserCc(),
                        'traderAddress' => ($this->result->traderAddress != "") ? $this->result->traderAddress : "",
                    ));

                    //Save updated validation data to customer's account
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                        Mage::dispatchEvent('logged_in_vat_check_after', array('customer' => $customer,
                            'validation_result' => $this->result,
                            'vatgrouper_data' => Mage::getSingleton('customer/session')->getData('_vatgrouper')));
                    }

                    //Send successmail in case of real successful validation
                    if (($this->result->valid_vat == 1 && $this->result->is_vat_free == 1) && !$this->getIsCronValidation()) {
                        Mage::dispatchEvent('geissweb_vat_check_success', array(
                                'customer' => Mage::getSingleton('customer/session')->getCustomer(),
                                'customer_session' => Mage::getSingleton('customer/session'),
                                'validation_result' => $this->result)
                        );
                    }

                } else {
                    $this->viesres = $this->result;
                    $this->result = new stdClass();
                    $this->result->last_vat_validation_date = date("Y-m-d H:i:s", time());
                    $this->result->countryCode = (isset($this->viesres->countryCode)) ? $this->viesres->countryCode : "";
                    $this->result->vatNumber = (isset($this->viesres->vatNumber)) ? $this->viesres->vatNumber : "";
                    $this->result->requestDate = date("Y-m-d H:i:s", time());
                    $this->result->valid = false;
                    $this->result->valid_vat = 0;
                    $this->result->is_vat_free = 0;
                    $this->result->validresult = 0;
                    $this->result->viesdata = "";
                    $this->result->faultstring = strtoupper($this->viesres->faultstring);
                    Mage::getSingleton('customer/session')->setData('_vatgrouper', array(
                        'last_vat_validation_date' => $this->result->last_vat_validation_date,
                        'vies_result_data' => $this->viesres,
                        'vat_validation_result' => 0,
                        'customer_taxvat_cc' => "",
                        'customer_taxvat_is_valid' => false,
                        'customer_is_vat_free' => false,
                        'customer_taxvat_from_validation' => "",
                        'faultstring' => $this->result->faultstring
                    ));
                }

            } else { // If not all Params are set
                $this->result = new stdClass();
                $this->result->countryCode = "";
                $this->result->vatNumber = "";
                $this->result->requestDate = "";
                $this->result->valid = false;
                $this->result->valid_vat = 0;
                $this->result->is_vat_free = 0;
				$this->result->countryCode = (isset($this->viesres->countryCode)) ? $this->viesres->countryCode : $this->getUserCc();
                $this->result->last_vat_validation_date = date("Y-m-d H:i:s", time());
                $this->result->validresult = 0;
                $this->result->viesdata = "";
                Mage::getSingleton('customer/session')->setData('_vatgrouper', array(
                    'last_vat_validation_date' => $this->result->last_vat_validation_date,
                    'vies_result_data' => "",
                    'vat_validation_result' => 0,
                    'customer_taxvat_cc' => "",
                    'customer_taxvat_is_valid' => false,
                    'customer_is_vat_free' => false,
                    'customer_taxvat_from_validation' => "",
                    'faultstring' => "INVALID_INPUT"
                ));

                // Regroup the customer in case the VAT-ID is not valid anymore
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    Mage::dispatchEvent('logged_in_vat_check_after', array('customer' => $customer,
                        'validation_result' => $this->result,
                        'vatgrouper_data' => Mage::getSingleton('customer/session')->getData('_vatgrouper')));
                }
            }
            //endif
        } else { //endif enabled

            $this->result = new stdClass();
            $this->result->countryCode = $this->getUserCc();
            $this->result->vatNumber = $this->getUserNr();
            $this->result->requestDate = date("Y-m-d H:i:s", time());

            $this->result->valid = (Mage::helper('euvatgrouper')->getOfflineValidate()) ? true : false;
            $this->result->valid_vat = (Mage::helper('euvatgrouper')->getOfflineValidate()) ? true : false;
            $this->result->is_vat_free = ($this->getShopCc() != $this->getUserCc()) ? true : false;

            $this->result->last_vat_validation_date = date("Y-m-d H:i:s", time());
            $this->result->validresult = 0;
            $this->result->viesdata = "";
			$this->result->countryCode = (isset($this->viesres->countryCode)) ? $this->viesres->countryCode : $this->getUserCc();

            if (!Mage::helper('euvatgrouper')->getOfflineValidate()) {
                $this->result->faultstring = "SERVICE_UNAVAILABLE";
            }

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer->getViesResultData() != "") {
                    $this->result->viesdata = $customer->getViesResultData();
                }
            }

            Mage::getSingleton('customer/session')->setData('_vatgrouper', array(
                'last_vat_validation_date' => $this->result->last_vat_validation_date,
                'vies_result_data' => $this->result->viesdata . "\nOFFLINE VALIDATED VATID[" . $this->getUserCc() . $this->getUserNr() . "] DATE[" . $this->result->last_vat_validation_date . "]",
                'vat_validation_result' => $this->result->valid_vat,
                'customer_taxvat_cc' => $this->getUserCc(),
                'customer_taxvat_is_valid' => $this->result->valid,
                'customer_is_vat_free' => $this->result->is_vat_free,
                'customer_taxvat_from_validation' => $this->getUserCc() . $this->getUserNr(),
                'faultstring' => (Mage::helper('euvatgrouper')->getOfflineValidate()) ? "" : "SERVICE_UNAVAILABLE"
            ));
        }
    }

//endfunction

    /**
     * Assigns the user to the default user group
     * @return bool
     */
    public function assignDefault()
    {
        $this->result = new stdClass();
        $this->result->countryCode = "";
        $this->result->vatNumber = "";
        $this->result->requestDate = "";
        $this->result->valid = false;
        $this->result->valid_vat = 0;
        $this->result->is_vat_free = 0;
        $this->result->last_vat_validation_date = date("Y-m-d H:i:s", time());
        $this->result->validresult = 0;
        $this->result->viesdata = "";
		$this->result->countryCode = (isset($this->viesres->countryCode)) ? $this->viesres->countryCode : $this->getUserCc();

        Mage::getSingleton('customer/session')->setData('_vatgrouper', array(
            'last_vat_validation_date' => "",
            'vies_result_data' => "",
            'vat_validation_result' => 0,
            'customer_taxvat_was_validated' => false,
            'customer_taxvat_cc' => "",
            'customer_taxvat_is_valid' => false,
            'customer_is_vat_free' => false,
            'customer_taxvat_from_validation' => "",
            'trader_name' => "",
            'trader_address' => ""
        ));

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            Mage::dispatchEvent('logged_in_vat_check_after', array('customer' => $customer,
                'validation_result' => $this->result,
                'vatgrouper_data' => Mage::getSingleton('customer/session')->getData('_vatgrouper')));
        }
        return true;
    }

    public function isViesOnline()
    {
        $cs = curl_init();
        curl_setopt($cs, CURLOPT_URL, $this->_viesUrl);
        curl_setopt($cs, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cs, CURLOPT_VERBOSE, false);
		curl_setopt($cs, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($cs, CURLOPT_TIMEOUT, 10);
        curl_exec($cs);
        $httpcode = curl_getinfo($cs, CURLINFO_HTTP_CODE);
        curl_close($cs);
        return ($httpcode == "200") ? true : false;
    }

}
