<?php

/**
 * Class DevAll_ExportOrder_Model_Cron
 */
class DevAll_ExportUpsLabel_Model_Cron
{
    /**
     * System configurations
     */
    const ENABLED = 'sales/exportorder/enabled';
    const EMAIL = 'sales/exportorder/email';
    const SUBJECT = 'sales/exportorder/subject';

    /**
     * @param $cron
     * @return boolean|void
     * @throws Zend_Mail_Exception
     */
    public function processExporting($cron)
    {
//        if (!(int)Mage::getStoreConfig(self::ENABLED)) {
//            return false;
//        }

        $fromDate = date('Y-m-d', strtotime('-1 months'));
        $toDate = date('Y-m-d');

        $data = Mage::getModel('upslabel')
            ->getCollection()
            ->addFieldToFilter('created_time', array(
                'from' => $fromDate,
                'to' => $toDate,
                'date' => true,
            ));

        $orderIds = $data->getColumnValues('order_id');
//        $content = Mage::getModel('exporter/exportorders')->exportOrders($orderIds);

        $this->sendMail($orderIds);
    }

    /**
     * @param $file
     * @throws Zend_Mail_Exception
     */
    protected function sendMail($orderIds)
    {
        $mail = new Zend_Mail('utf-8');

        $recipients = array(
            Mage::getStoreConfig(self::EMAIL)
        );

        $mail
            ->setBodyHtml(Mage::helper('sales')->__($orderIds))
            ->setSubject(Mage::getStoreConfig(self::SUBJECT))
            ->addTo($recipients)
            ->setFrom(
                Mage::getStoreConfig('trans_email/ident_general/email'),
                Mage::getStoreConfig('trans_email/ident_general/name')
            );

        try {
            $mail->send();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}