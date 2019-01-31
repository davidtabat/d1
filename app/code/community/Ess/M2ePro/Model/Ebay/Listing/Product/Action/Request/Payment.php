<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Payment
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    const PAYPAL = 'PayPal';

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    private $paymentTemplate = NULL;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'methods' => $this->getMethodsData()
        );

        if ($payPalData = $this->getPayPalData($data['methods'])) {
            $data['paypal'] = $payPalData;
        }

        return array('payment'=>$data);
    }

    //########################################

    /**
     * @return array
     */
    public function getMethodsData()
    {
        $methods = array();

        if ($this->getPaymentTemplate()->isPayPalEnabled()) {
            $methods[] = self::PAYPAL;
        }

        $services = $this->getPaymentTemplate()->getServices(true);

        foreach ($services as $service) {
            /** @var $service Ess_M2ePro_Model_Ebay_Template_Payment_Service */
            $methods[] = $service->getCodeName();
        }

        return $methods;
    }

    public function getPayPalData($methods)
    {
        if (!in_array(self::PAYPAL,$methods)) {
            return false;
        }

        return array(
            'email' => $this->getPaymentTemplate()->getPayPalEmailAddress(),
            'immediate_payment' => $this->getPaymentTemplate()->isPayPalImmediatePaymentEnabled()
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    private function getPaymentTemplate()
    {
        if (is_null($this->paymentTemplate)) {
            $this->paymentTemplate = $this->getListingProduct()
                                          ->getChildObject()
                                          ->getPaymentTemplate();
        }
        return $this->paymentTemplate;
    }

    //########################################
}