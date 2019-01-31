<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Report_Ticket_Container extends Mage_Adminhtml_Block_Template
{
    protected $_toolbar = null;
    /** @var Mirasvit_Helpdesk_Block_Adminhtml_Report_Ticket_Grid */
    protected $_grid = null;
    protected $_chart = null;
    /** @var Mage_Adminhtml_Block_Store_Switcher */
    protected $_storeSwitcher = null;

    protected $_collection = null;

    public function _prepareLayout()
    {
        $this->setHeaderText(Mage::helper('helpdesk')->__('Tickets Reports'));
        $this->_initStoreSwitcher()
            ->_initGrid()
            ->_initToolbar()
            ->_initChart()
            ;

        return parent::_prepareLayout();
    }

    public function getGrid()
    {
        return $this->_grid;
    }

    public function getToolbar()
    {
        return $this->_toolbar;
    }

    public function getChart()
    {
        return $this->_chart;
    }

    public function getStoreSwitcher()
    {
        return $this->_storeSwitcher;
    }

    protected function _initStoreSwitcher()
    {
        $this->_storeSwitcher = Mage::app()->getLayout()->createBlock('adminhtml/store_switcher');
        $this->_storeSwitcher->setStoreVarName('store_ids');

        return $this;
    }

    protected function _initToolbar()
    {
        $this->_toolbar = Mage::app()->getLayout()->createBlock('helpdesk/adminhtml_report_ticket_toolbar');

        $this->_toolbar
            ->setFilterData($this->getFilterData())
            ->setGrid($this->_grid)
            ;

        return $this;
    }

    protected function _initGrid()
    {
        $this->_grid = Mage::app()->getLayout()->createBlock('helpdesk/adminhtml_report_ticket_grid', get_class($this));

        $this->_grid->setContainer($this)
            ->setFilterData($this->getFilterData());
            // ->setId('report');

        $this->_grid->setCollection($this->getCollection());

        return $this;
    }

    protected function _initChart()
    {
        $this->_chart = Mage::app()->getLayout()->createBlock('helpdesk/adminhtml_report_ticket_chart');

        $this->_chart
            ->setGrid($this->getGrid())
            ->setCollection($this->getCollection());

        return $this;
    }

    public function getGridHtml()
    {
        if ($this->_grid) {
            return $this->_grid->toHtml();
        }
    }

    public function getToolbarHtml()
    {
        if ($this->_toolbar) {
            return $this->_toolbar->toHtml();
        }
    }

    public function getStoreSwitcherHtml()
    {
        if ($this->_storeSwitcher) {
            return $this->_storeSwitcher->toHtml();
        }
    }

    public function getChartHtml()
    {
        if ($this->_chart) {
            return $this->_chart->toHtml();
        }
    }

    public function getCollection()
    {
        if ($this->_collection == null) {
            $this->_collection = Mage::getResourceModel('helpdesk/report_ticket_collection')
                ->setFilterData($this->getFilterData())
                ;
        }

        return $this->_collection;
    }

    public function getFilterData()
    {
        if (!$this->hasData('filter_data')) {
            $data = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
            $data = $this->_filterDates($data, array('from', 'to'));

            $currentMonth = Mage::helper('helpdesk/report')->getInterval(Mirasvit_Helpdesk_Helper_Report::THIS_MONTH);

            if (!isset($data['from'])) {
                $data['from'] = $currentMonth->getFrom()->get(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }

            if (!isset($data['to'])) {
                $data['to'] = $currentMonth->getTo()->get(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }

            if (strpos($data['from'], ':') === false) {
                $data['from'] .= ' 00:00:00';
            }

            if (strpos($data['to'], ':') === false) {
                $data['to'] .= ' 23:59:59';
            }

            if (!isset($data['period'])) {
                $data['period'] = 'day';
            }

            $fromLocal = new Zend_Date(strtotime($data['from']) - Mage::getSingleton('core/date')->getGmtOffset());
            $data['from_local'] = $fromLocal->get(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $toLocal = new Zend_Date(strtotime($data['to']) - Mage::getSingleton('core/date')->getGmtOffset());
            $data['to_local'] = $toLocal->get(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $data['store_ids'] = array_filter(explode(',', $this->getRequest()->getParam('store_ids')));

            $data = array_filter($data);

            $this->setData('filter_data', new Varien_Object($data));
        }

        return $this->getData('filter_data');
    }

    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }

        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
        ));

        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT,
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }

        return $array;
    }
}
