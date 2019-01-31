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



class Mirasvit_Helpdesk_Block_Adminhtml_Report_Ticket_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('period');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns()
    {
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        if ($this->getFilterData()->getGroupBy()) {
            $this->addColumn('group_by', array(
                    'header' => Mage::helper('helpdesk')->__(''),
                    'type' => 'text',
                    'index' => 'group_by',
                    'filter' => false,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'groupByCallback'),
                )
            );
        }
//echo $this->getCollection()->getSelect();die;
        $this
            ->addColumn('period', array(
                    'header' => Mage::helper('helpdesk')->__('Period'),
                    'type' => 'text',
                    'index' => 'period',
                    'filter' => false,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'periodCallback'),
                )
            )
            ->addColumn('new_ticket_cnt', array(
                    'header' => Mage::helper('helpdesk')->__('New Tickets #'),
                    'type' => 'number',
                    'index' => 'new_ticket_cnt',
                    'chart' => true,
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ->addColumn('changed_ticket_cnt', array(
                    'header' => Mage::helper('helpdesk')->__('Changed Tickets #'),
                    'type' => 'number',
                    'index' => 'changed_ticket_cnt',
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ->addColumn('total_reply_cnt', array(
                    'header' => Mage::helper('helpdesk')->__('Replies #'),
                    'type' => 'number',
                    'index' => 'total_reply_cnt',
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ->addColumn('solved_ticket_cnt', array(
                    'header' => Mage::helper('helpdesk')->__('Solved Tickets #'),
                    'type' => 'number',
                    'index' => 'solved_ticket_cnt',
                    'chart' => true,
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )

            ->addColumn('first_reply_time', array(
                    'header' => Mage::helper('helpdesk')->__('1st Reply Time'),
                    'type' => 'number',
                    'index' => 'first_reply_time',
                    'filter' => false,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'timeCallback'),
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ->addColumn('full_resolution_time', array(
                    'header' => Mage::helper('helpdesk')->__('Full Resolution Time'),
                    'type' => 'number',
                    'index' => 'full_resolution_time',
                    'filter' => false,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'timeCallback'),
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )

            ->addColumn('satisfaction_rate_cnt', array(
                    'header' => Mage::helper('helpdesk')->__('Votes #'),
                    'type' => 'number',
                    'index' => 'satisfaction_rate_cnt',
                    'chart' => null,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'votesCallback'),
                    'is_system' => true,
                    'filter' => false,
                    'sortable' => false,
                )
            )
            ->addColumn('satisfaction_rate', array(
                    'header' => Mage::helper('helpdesk')->__('Satisfaction Score'),
                    'type' => 'number',
                    'index' => 'satisfaction_rate',
                    'chart' => false,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'percentCallback'),
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ->addColumn('satisfaction_response_cnt', array(
                    'header' => Mage::helper('helpdesk')->__('Total Responses #'),
                    'type' => 'number',
                    'index' => 'satisfaction_response_cnt',
                    'chart' => false,
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ->addColumn('satisfaction_response_rate', array(
                    'header' => Mage::helper('helpdesk')->__('Response Rate'),
                    'type' => 'number',
                    'index' => 'satisfaction_response_rate',
                    'chart' => false,
                    'frame_callback' => array(Mage::helper('helpdesk/report'), 'percentCallback'),
                    'filter_condition_callback' => array($this, '_havingFilter'),
                )
            )
            ;

        $this->addExportType('*/*/*/export/csv/', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/*/export/xml/', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _havingFilter($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();

        $collection->addHavingFilter($field, $column->getFilter()->getCondition());

        return $this;
    }

    /**
     * We need this function to disable LIMIT and ORDER of parent function.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        return $this;
    }

//    protected function _toHtml() {
//
//        $this->getCollection()->load();
//        echo $this->getCollection()->getSelect();
//        return parent::_toHtml();
//    }
}
