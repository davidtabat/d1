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
 * @package   RMA
 * @version   2.0.7
 * @build     1267
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Report_Rma_Product_Grid extends Mirasvit_Rma_Block_Adminhtml_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'created_at';

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        // $this->setCountSubTotals(true);
    }

    public function getResourceCollectionName()
    {
        return 'rma/report_rma_product_collection';
    }

    protected function _prepareColumns()
    {
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('reports')->__('Period'),
            'index' => 'created_at',
            'width' => 100,
            'sortable' => false,
            'period_type' => $this->getPeriodType(),
            'renderer' => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label' => Mage::helper('adminhtml')->__('Total'),
            'subtotals_label' => Mage::helper('adminhtml')->__('SubTotal'),
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('rma')->__('Product SKU'),
            'index' => 'product_sku',
            'type' => 'text',
            'sortable' => false,
            'totals_label' => '',
            )
        );

        $this->addColumn('product_name', array(
            'header' => Mage::helper('rma')->__('Product Name'),
            'index' => 'product_name',
            'type' => 'text',
            'sortable' => false,
            'totals_label' => '',
            )
        );
        $this->addColumn('qty_returns', array(
            'header' => Mage::helper('rma')->__('Number of returns'),
            'index' => 'qty_returns',
            'type' => 'number',
            'sortable' => false,
            )
        );
        $this->addColumn('qty_items', array(
            'header' => Mage::helper('rma')->__('Number of returned items'),
            'index' => 'qty_items',
            'type' => 'number',
            'sortable' => false,
            )
        );
        $reportType = $this->getFilterData()->getReportType();

        if ($reportType == 'resolution') {
            $reasons = Mage::getModel('rma/resolution')->getCollection()
                ->addFieldToFilter('is_active', true);
        } elseif ($reportType == 'condition') {
                $reasons = Mage::getModel('rma/condition')->getCollection()
                    ->addFieldToFilter('is_active', true);
        } else {
            $reasons = Mage::getModel('rma/reason')->getCollection()
                ->addFieldToFilter('is_active', true);

        }
        foreach ($reasons as $reason) {
            $this->addColumn('reason' . $reason->getId(), array(
                    'header' => $reason->getName(),
                    'index' => 'reason' . $reason->getId(),
                    'type' => 'number',
                    'sortable' => false,
                )
            );
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function getFilterData()
    {
        $date = Mage::getSingleton('core/date');
        $data = parent::getFilterData();

        if (!$data->hasData('from')) {
            $data->setData('from', $date->gmtDate(null, $date->gmtTimestamp() - 30 * 24 * 60 * 60));
        }

        if (!$data->hasData('to')) {
            $data->setData('to', $date->gmtDate(null, $date->gmtTimestamp()));
        }

        if (!$data->hasData('period_type')) {
            $data->setData('period_type', 'day');
        }

        if (!$data->hasData('report_type')) {
            $data->setData('report_type', 'all');
        }

        return $data;
    }

    /************************/
}
