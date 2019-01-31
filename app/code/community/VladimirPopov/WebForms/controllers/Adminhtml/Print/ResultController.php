<?php
require_once(Mage::getBaseDir('lib').'/Webforms/mpdf/mpdf.php');

class VladimirPopov_WebForms_Adminhtml_Print_ResultController
    extends Mage_Adminhtml_Controller_Action
{
    public function printAction()
    {
        $result_id = $this->getRequest()->getParam('result_id');
        $result = Mage::getModel('webforms/results')->load($result_id);


        $mpdf = new mPDF('utf-8', 'A4');
        $mpdf->WriteHTML($result->toPrintableHtml());


        $this->_prepareDownloadResponse('result'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s', $result->getCreatedTime()).
            '.pdf', $mpdf->Output('','S'), 'application/pdf');

    }
}