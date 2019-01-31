<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Sync extends Mage_Adminhtml_Block_Template
{
    protected $_productsCount;

    public function getSyncButtonHtml($buttonId)
    {
        return $this->getChildHtml($buttonId);
    }

    protected function _prepareLayout()
    {
        $helper = Mage::helper('rakuten');

        $this->setChild('get_rakuten_categories_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Get Categories List From Rakuten'),
                    'element_name' => 'action',
                    'type'      => 'submit',
                    'value'     => 'get_categories',
                ))
        );

        $this->setChild('sync_categories_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Synchronize Categories'),
                    'element_name' => 'action',
                    'type'      => 'submit',
                    'value'     => 'sync_categories',
                ))
        );

        if ($syncTime = $helper->checkSync()) {
            $this->setChild('clear_sync_lock_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => $helper->__('Clear synchronization lock'),
                        'element_name' => 'action',
                        'type'      => 'submit',
                        'value'     => 'clear_lock',
                    ))
            );
        }
    }

    public function getActionUrl()
    {
        return $this->getUrl('*/*/sync', array('_current' => true));
    }

    public function cronEnabled()
    {
        return Mage::getStoreConfig('nn_market/rakuten/enable_cron');
    }

    public function isCronRunning()
    {
        $schedules_pending = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
            ->load();
        $schedules_complete = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_SUCCESS)
            ->load();

        if (sizeof($schedules_pending) == 0 || sizeof($schedules_complete) == 0) {
            return false;
        }

        return true;
    }

    private function _getCronData($status, $field)
    {
        $cron = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', $status)
            ->addFieldToFilter('job_code', 'nn_market_rakuten_sync_all')
            ->getFirstItem();

        return $cron->getData($field);
    }

    public function getLastCronRun()
    {
        if ($time = $this->_getCronData(Mage_Cron_Model_Schedule::STATUS_SUCCESS, 'executed_at')) {
            return Mage::helper('core')->formatDate($time, 'medium', true);
        }
        return Mage::helper('rakuten')->__('Never');
    }

    public function getNextCronRun()
    {
        if ($time = $this->_getCronData(Mage_Cron_Model_Schedule::STATUS_PENDING, 'scheduled_at')) {
            return Mage::helper('core')->formatDate($time, 'medium', true);
        }
        return Mage::helper('rakuten')->__('Not yet planned');
    }

    public function isProductsFromCategories()
    {
        if (Mage::helper('rakuten')->productsFromCategories()) {
            return $this->__('Yes');
        } else {
            return $this->__('No');
        }
    }

    public function getProductsCount()
    {
        $productSyncHelper = Mage::helper('rakuten/product');
        
        if (!$this->_productsCount) {
            $this->_productsCount = $productSyncHelper->getProductsCountToSynchronize();
        }
        
        return $this->_productsCount;
    }

    public function getMinExecutionTime()
    {
        $minExecutionTime = round(round($this->getProductsCount() / 200) * 120);
        if (ini_get('max_execution_time') < $minExecutionTime) {
            return $minExecutionTime;
        }
        return 1800;
    }

    public function getMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        $number = (int) $memoryLimit;
        switch (strtoupper(substr($memoryLimit, -1))) {
            case 'K':
                $memoryLimit = $number * 1024;
                break;
            case 'M':
                $memoryLimit = $number * pow(1024, 2);
                break;
            case 'G':
                $memoryLimit = $number * pow(1024, 3);
                break;
            default:
                $memoryLimit = $number;
                break;
        }

        return (int) $memoryLimit;
    }

    public function getCreateCsv()
    {
        return Mage::helper('rakuten')->getCreateCsv();
    }

    public function isCsvFileExists()
    {
        if (file_exists(Mage::getBaseDir('media') . '/rakuten_products.csv')) {
            return true;
        }
        return false;
    }

    public function getCsvFileMtime()
    {
        if (file_exists(Mage::getBaseDir('media') . '/rakuten_products.csv')) {
            $date = date ("Y-m-d H:i:s.", filemtime(Mage::getBaseDir('media') . '/rakuten_products.csv'));
            return Mage::helper('core')->formatDate($date, 'medium', true);
        }
        return false;
    }

    public function getCsvFile()
    {
        return Mage::getBaseUrl('media') . '/rakuten_products.csv';
    }
}
