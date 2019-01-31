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



class Mirasvit_Helpdesk_Block_Adminhtml_Report_Ticket_Chart extends Mage_Adminhtml_Block_Template
{
    protected $_multiChart = false;

    public function _prepareLayout()
    {
        $this->setTemplate('mst_helpdesk/report/ticket/chart.phtml');

        return parent::_prepareLayout();
    }

    public function getCollection()
    {
        $collection = $this->getData('collection');

        $collection->clear()
            ->setPageSize(10000)
            ;
//echo $collection->getSelect();die;
        return $collection;
    }

    public function getCharts()
    {
        $this->_multiChart = false;

        foreach ($this->getCollection() as $itm) {
            if ($itm->getChildren()) {
                $this->_multiChart = true;
            }
        }

        if ($this->_multiChart) {
            return $this->getCollection()->count();
        }

        return 1;
    }

    public function getXAxisField()
    {
        return 'period';
    }

    public function getDataTable($idx = 0)
    {
        $array = array();

        $columns = $this->getGrid()->getColumns();

        $row = array();

        $row[] = $this->getXAxisField();
        foreach ($columns as $index => $column) {
            if (!in_array($column->getType(), array('number', 'currency'))) {
                continue;
            }

            if ($column->getChart() === 'none') {
                continue;
            }

            $row[] = $column->getHeader();
        }

        $array[] = $row;
        /** @var Mage_Adminhtml_Block_Widget_Grid_Column $xColumn */
        $xColumn = $columns[$this->getXAxisField()];

        $itmIdx = 0;
        foreach ($this->getCollection() as $itm) {
            if ($this->_multiChart && $itmIdx++ != $idx) {
                continue;
            }
            $row = array();

            $row[] = $xColumn->getRowField($itm);

            foreach ($columns as $index => $column) {
                if (!in_array($column->getType(), array('number', 'currency'))) {
                    continue;
                }

                if ($column->getChart() === 'none') {
                    continue;
                }

                $value = floatval($itm->getData($index));

                $row[] = $value;
            }
            $array[] = $row;

            if ($itm->getChildren()) {
                foreach ($itm->getChildren() as $subitem) {
                    $this->setMultirows(1);
                    $row = array();

                    $row[] = $xColumn->getRowField($subitem);

                    foreach ($columns as $index => $column) {
                        if (!in_array($column->getType(), array('number', 'currency'))) {
                            continue;
                        }

                        if ($column->getChart() === 'none') {
                            continue;
                        }

                        $value = floatval($subitem->getData($index));

                        $row[] = $value;
                    }
                    $array[] = $row;
                }
            }
        }

        return $array;
    }

    public function getDefaultSeries()
    {
        $series = array();

        $idx = 1;
        $total = 0;

        foreach ($this->getGrid()->getColumns() as $index => $column) {
            if (!in_array($column->getType(), array('number', 'currency'))) {
                continue;
            }

            if ($column->getChart() === 'none') {
                continue;
            }

            if ($column->getChart()) {
                $series[] = $idx;
            }

            ++$idx;
        }

        return $series;
    }

    public function getChartHeader($idx = 0)
    {
        if ($this->_multiChart) {
            $itmIdx = 0;
            $columns = $this->getGrid()->getColumns();
            $column = $columns['group_by'];

            foreach ($this->getCollection() as $itm) {
                if ($this->_multiChart && $itmIdx++ != $idx) {
                    continue;
                }

                return $column->getRowField($itm);
            }
        }

        return '';
    }
}
