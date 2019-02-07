<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * https://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @category    Mage
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (https://www.geissweb.de)
 * @license     https://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Model_Validation_Vies extends Geissweb_Euvatgrouper_Model_Validation_Abstract
{
    private $service_url = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
    var $vies_params = array();

    /**
     * Connects to EU VIES
     */
    public function __construct()
    {
        parent::__construct();

        try {

			if(Mage::helper('euvatgrouper')->isIPv6Mode())
			{
				$opts = array('socket' => array('bindto' => Mage::helper('euvatgrouper')->getIPv4ToBindOn().':0'));
				$context = stream_context_create($opts);
				$this->setClient(new SoapClient($this->service_url, array(
					'exceptions' 	=> 0,
					'trace' 		=> false,
					'cache_wsdl' 	=> WSDL_CACHE_MEMORY,
					'soap_version' 	=> SOAP_1_1,
					'user_agent' 	=> 'Magento Webshop',
					'stream_context'=> $context
					)
				));

			} else {

				$this->setClient(new SoapClient($this->service_url, array(
					'soap_version'	=> SOAP_1_1,
					'user_agent' 	=> 'Magento Webshop',
					'cache_wsdl'	=> WSDL_CACHE_MEMORY
					)
				));
			}

            $this->_response = new stdClass();
            $this->_response->shop_cc = $this->getShopCc();


        } catch (SoapFault $s) {
			Mage::logException($s);
        }

    }

    /**
     * Validate
     */
    public function validate()
    {
        $this->_response->user_cc = $this->getUserCc();
        $this->_response->user_nr = $this->getUserNr();
        $this->_response->address_type = $this->getAddressType();

        try {

            if($this->getShopCc() != 'EU')
            {
                $this->vies_params = array(
                    'countryCode' => $this->getUserCc(),
                    'vatNumber' => Mage::helper('euvatgrouper')->cleanCustomerVatId($this->getUserNr()),
                    'requesterCountryCode' => $this->getShopCc(),
                    'requesterVatNumber' => Mage::helper('euvatgrouper')->cleanCustomerVatId($this->getShopNr()),
                );
                $this->_response = $this->getClient()->checkVatApprox($this->vies_params);

            } else {
                $this->vies_params = array(
                    'countryCode' => $this->getUserCc(),
                    'vatNumber' => Mage::helper('euvatgrouper')->cleanCustomerVatId($this->getUserNr()),
                );
                $this->_response = $this->getClient()->checkVat($this->vies_params);
            }

            // Set additional data for the result object
            $this->_response->shop_cc = $this->getShopCc();
            $this->_response->user_cc = $this->getUserCc();
            $this->_response->user_nr = $this->getUserNr();
            $this->_response->address_type = $this->getAddressType();
            $this->_response->vat_request_success = true;

            // Get proper result object
            $this->setResult( Mage::getSingleton('euvatgrouper/validation_result')->setViesData($this->_response, $this->getUserCc().$this->getUserNr()) );

            Mage::dispatchEvent('vat_check_after', array(
                'result' 	 => $this->getResult(),
                'address_id' => $this->getAddressId()
            ));



        } catch(SoapFault $e) {

            $this->_response->vat_request_success = false;
            $this->_response->requestDate = date("Y-m-d H:i:s", time());
            $this->_response->traderName = '';
            $this->_response->traderCompanyType = '';
            $this->_response->traderAddress = '';
            $this->_response->valid = false;
            $this->_response->validation_result = false;
            $this->_response->faultstring = $e->getMessage();

            switch($e->getMessage())
            {
                case 'MS_UNAVAILABLE':
                case 'SERVICE_UNAVAILABLE':
                case 'SERVER_BUSY':
                case 'TIMEOUT':
                    if(Mage::helper('euvatgrouper')->getOfflineValidate() && isset($this->vies_params['countryCode'], $this->vies_params['vatNumber']) )
                    {
                        if($this->isSyntaxValid($this->vies_params['vatNumber'], $this->vies_params['countryCode']))
                        {
                            $this->_response->valid = true;
                            $this->_response->validation_result = true;
                            $this->_response->requestIdentifier = "OFFLINE_VALIDATION: ".$e->getMessage();
                        }
                    }
                break;

                default:
                break;
            }

            $this->setResult( Mage::getSingleton('euvatgrouper/validation_result')->setViesData($this->_response, $this->getUserCc().$this->getUserNr()) );
            Mage::dispatchEvent('vat_check_after', array(
                'result' 	 => $this->getResult(),
                'address_id' => $this->getAddressId()
            ));

        }

    }

}