<?php

/**
 * Class DevAll_ExportUpsLabel_Model_Cron
 */
class DevAll_ExportUpsLabel_Model_Cron
{
    /**
     * System configurations
     */
    const ENABLED = 'sales/export_upslabel/enabled';
    const EMAIL = 'sales/export_upslabel/email';
    const SUBJECT = 'sales/export_upslabel/subject';

    /**
     * @param $cron
     * @return boolean|void
     * @throws Zend_Mail_Exception
     */
    public function processExporting($cron)
    {
        if (!(int)Mage::getStoreConfig(self::ENABLED)) {
            return false;
        }

        $fromDate = date('Y-m-d', strtotime('first day of this month'));
        $toDate = date('Y-m-d');

        $orderAddressTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address');

        $data = Mage::getModel('upslabel/upslabel')
            ->getCollection()
            ->addFieldToSelect('order_id')
            ->addFieldToFilter('created_time', array(
                'from' => $fromDate,
                'to' => $toDate,
                'date' => true,
            ));

        $data->getSelect()
            ->join(
                ['order_address' => $orderAddressTable],
                'main_table.order_id = order_address.parent_id',
                'order_address.country_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('order_address.country_id, COUNT(*) as count')
            ->where("order_address.address_type = 'shipping'")
            ->group('order_address.country_id')
            ->order('count DESC');

        $content = Mage::helper('sales')->__('Country Packages from %s to %s', $fromDate, $toDate) . "\n\n";
        $total = 0;

        foreach ($data as $item) {
            $content .= sprintf('%s: %s', Mage::getModel('directory/country')->loadByCode($item->getCountryId())->getName(), $item->getCount()) . "\n";
            $total += (int)$item->getCount();
        }

        $content .= Mage::helper('sales')->__('%sTotal this Month: %s', "\n", $total);

        $this->sendMail($content);
    }

    /**
     * @param $content
     * @throws Zend_Mail_Exception
     */
    protected function sendMail($content)
    {
        $mail = new Zend_Mail('utf-8');

        $recipients = array(
            Mage::getStoreConfig(self::EMAIL)
        );

        $mail
            ->setBodyText($content)
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