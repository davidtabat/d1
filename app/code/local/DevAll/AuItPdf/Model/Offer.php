<?php

/**
 * Class DevAll_AuItPdf_Model_Offer
 */
class DevAll_AuItPdf_Model_Offer extends AuIt_Pdf_Model_Offer
{
    /**
     * @return mixed
     */
    protected function getBuildAfterTableText()
    {
        $variables = $this->_processor->getVariables();
        $invoice = isset($variables['invoice']) ? $variables['invoice'] : false;
        $payment = $invoice ? $invoice->getOrder()->getPayment() : false;

        $this->_processor->setVariables([
            'ppp_pui_instruction_type' => $payment->getData('ppp_pui_instruction_type'),
            'ppp_pui_account_holder_name' => $payment->getData('ppp_pui_account_holder_name'),
            'ppp_pui_international_bank_account_number' => $payment->getData('ppp_pui_international_bank_account_number'),
            'ppp_pui_bank_identifier_code' => $payment->getData('ppp_pui_bank_identifier_code'),
            'ppp_pui_reference_number' => $payment->getData('ppp_pui_reference_number')
        ]);

        return parent::getBuildAfterTableText();
    }
}