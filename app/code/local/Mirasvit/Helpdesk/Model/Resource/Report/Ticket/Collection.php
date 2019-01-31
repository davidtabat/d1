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



class Mirasvit_Helpdesk_Model_Resource_Report_Ticket_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_filterData = array();
    protected $_selectedColumns = array();

    protected function _construct()
    {
        $this->_init('helpdesk/ticket');
    }

    protected function _getSelectedColumns()
    {
        $this->_selectedColumns = array(
            'period' => $this->getTZDate('period'),
            'user_id' => 'user_id',
            'new_ticket_cnt' => new Zend_Db_Expr('SUM(new_ticket_cnt)'),
            'solved_ticket_cnt' => new Zend_Db_Expr('SUM(solved_ticket_cnt)'),
            'changed_ticket_cnt' => new Zend_Db_Expr('SUM(changed_ticket_cnt)'),
            'total_reply_cnt' => new Zend_Db_Expr('SUM(total_reply_cnt)'),
            'first_reply_time' => new Zend_Db_Expr('AVG(NULLIF(first_reply_time, 0))'),
            'first_resolution_time' => new Zend_Db_Expr('AVG(NULLIF(first_resolution_time, 0))'),
            'full_resolution_time' => new Zend_Db_Expr('AVG(NULLIF(full_resolution_time, 0))'),

            'satisfaction_rate_1_cnt' => new Zend_Db_Expr('SUM(satisfaction_rate_1_cnt)'),
            'satisfaction_rate_2_cnt' => new Zend_Db_Expr('SUM(satisfaction_rate_2_cnt)'),
            'satisfaction_rate_3_cnt' => new Zend_Db_Expr('SUM(satisfaction_rate_3_cnt)'),
            'satisfaction_rate' => new Zend_Db_Expr('AVG(NULLIF(satisfaction_rate, 0))'),
            'satisfaction_response_cnt' => new Zend_Db_Expr('SUM(satisfaction_response_cnt)'),
            'satisfaction_rate' => new Zend_Db_Expr('
                (SUM(satisfaction_rate_1_cnt) * 1 + SUM(satisfaction_rate_2_cnt) * 2 + SUM(satisfaction_rate_3_cnt) * 3)
                    / (SUM(satisfaction_rate_1_cnt) + SUM(satisfaction_rate_2_cnt) + SUM(satisfaction_rate_3_cnt))
                    / 3
                    * 100'),
            'satisfaction_response_rate' => new Zend_Db_Expr('SUM(satisfaction_response_cnt) / SUM(total_reply_cnt) * 100'),
        );

        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {
        $select = $this->getSelect();
        $select->from(
                array('main_table' => $this->getMainTable()),
                $this->_getSelectedColumns()
            )
            ;

        return $this;
    }

    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->columns();

        $select = 'SELECT COUNT(*) FROM ('.$countSelect->__toString().') as cnt';

        return $select;
    }

    public function setFilterData($data)
    {
        $this->_filterData = $data;

        if ($data->getFrom()) {
            $this->getSelect()
                ->where($this->getTZDate('period').' >= ?', $data->getFrom());
        }

        if ($data->getTo()) {
            $this->getSelect()
                ->where($this->getTZDate('period').' <= ?', $data->getTo());
        }

        //fixme that function is not working correctly with real data
        $tzPeriod = $this->getTZDate('period');

        switch ($data->getPeriod()) {
            case 'day_of_week':
                $this->setMainTable($this->getTable('helpdesk/ticket_aggregated_day'));
                $periodExpr = new Zend_Db_Expr('WEEKDAY('.$tzPeriod.')');
                break;

            case 'hour_of_day':
                $this->setMainTable($this->getTable('helpdesk/ticket_aggregated_hour'));
                $periodExpr = new Zend_Db_Expr('HOUR('.$tzPeriod.')');
                break;

            case 'month':
                $this->setMainTable($this->getTable('helpdesk/ticket_aggregated_month'));
                $periodExpr = new Zend_Db_Expr('DATE_FORMAT('.$tzPeriod.', "%Y-%m-01 00:00:00")');
                break;

            case 'year':
                $this->setMainTable($this->getTable('helpdesk/ticket_aggregated_month'));
                $periodExpr = new Zend_Db_Expr('DATE_FORMAT('.$tzPeriod.', "%Y-01-01 00:00:00")');
                break;

            default:
                $this->setMainTable($this->getTable('helpdesk/ticket_aggregated_day'));
                $periodExpr = new Zend_Db_Expr('DATE_FORMAT('.$tzPeriod.', "%Y-%m-%d 00:00:00")');
                break;
        }

        $this->getSelect()
            ->group($periodExpr)
//            ->columns(array('period' => $periodExpr))
            // ->order($periodExpr)
            ;

        switch ($data->getGroupBy()) {
            case 'department':
                $expr = 'department_user_table.du_department_id';
                $this->getSelect()
                    ->joinLeft(array('department_user_table' => $this->getTable('helpdesk/department_user')),
                        'department_user_table.du_user_id = main_table.user_id',
                        array())
                    ->group($expr)
                    ->columns(array('group_by' => $expr))
                    ->where($expr.' > 0')
                    ->order($expr);
                break;

            case 'agent':
                $expr = 'main_table.user_id';
                $this->getSelect()
                    ->group('user_id')
                    ->columns(array('group_by' => $expr))
                    ->where($expr.' > 0')
                    ->order($expr);
                break;
        }

        return $this;
    }

    public function getFilterData()
    {
        return $this->_filterData;
    }

    protected function _afterLoad()
    {
        # clear group_by title
        if ($this->_filterData->getGroupBy()) {
            foreach ($this->_items as $item) {
                if (!$item->getParent()) {
                    foreach ($this->_items as $subitem) {
                        if ($item != $subitem && $item->getData('group_by') == $subitem->getData('group_by')) {
                            $subitem->setData('group_by', '');
                        }
                    }
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Convert column date to the current timezone.
     *
     * @param string $column
     *
     * @return string
     */
    public function getTZDate($column)
    {
        return 'DATE_ADD('.$column.', INTERVAL '.Mage::getModel('core/date')->getGmtOffset().' SECOND)';

//        $periods = $this->_getTZOffsetTransitions(
//            Mage::app()->getLocale()->storeDate(null)->toString(Zend_Date::TIMEZONE_NAME),
//            time() - 3 * 365 * 24 * 60 * 60,
//            null
//        );
//
//        if (!count($periods)) {
//            return $column;
//        }
//
//        $query = '';
//        $periodsCount = count($periods);
//
//        $i = 0;
//        foreach ($periods as $offset => $timestamps) {
//            $subParts = array();
//            foreach ($timestamps as $ts) {
//                $subParts[] = "($column between {$ts['from']} and {$ts['to']})";
//            }
//
//            $then = $this->getConnection()->getDateAddSql($column, $offset, Varien_Db_Adapter_Interface::INTERVAL_SECOND);
//
//            $query .= (++$i == $periodsCount) ? $then : 'CASE WHEN '.implode(' OR ', $subParts)." THEN $then ELSE ";
//        }
//
//        return new Zend_Db_Expr($query.str_repeat('END ', count($periods) - 1));
//    }
//
//    protected function _getTZOffsetTransitions($timezone, $from = null, $to = null)
//    {
//        $tzTransitions = array();
//        try {
//            if ($from == null) {
//                $from = new Zend_Date($from, Varien_Date::DATETIME_INTERNAL_FORMAT);
//                $from = $from->getTimestamp();
//            }
//
//            $to = new Zend_Date($to, Varien_Date::DATETIME_INTERNAL_FORMAT);
//            $nextPeriod = $this->getConnection()->formatDate($to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
//            $to = $to->getTimestamp();
//
//            $dtz = new DateTimeZone($timezone);
//            $transitions = $dtz->getTransitions();
//            $dateTimeObject = new Zend_Date('c');
//            for ($i = count($transitions) - 1; $i >= 0; --$i) {
//                $tr = $transitions[$i];
//                if (!$this->_isValidTransition($tr, $to)) {
//                    continue;
//                }
//
//                $dateTimeObject->set($tr['time']);
//                $tr['time'] = $this->getConnection()
//                    ->formatDate($dateTimeObject->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
//                $tzTransitions[$tr['offset']][] = array('from' => $tr['time'], 'to' => $nextPeriod);
//
//                if (!empty($from) && $tr['ts'] < $from) {
//                    break;
//                }
//                $nextPeriod = $tr['time'];
//            }
//        } catch (Exception $e) {
//            $this->_logException($e);
//        }
//
//        return $tzTransitions;
    }

//    protected function _isValidTransition($transition, $to)
//    {
//        $result = true;
//        $timeStamp = $transition['ts'];
//        $transitionYear = date('Y', $timeStamp);
//
//        if ($transitionYear > 10000 || $transitionYear < -10000) {
//            $result = false;
//        } elseif ($timeStamp > $to) {
//            $result = false;
//        }
//
//        return $result;
//    }

    public function addHavingFilter($field, $condition)
    {
        $field = $this->_columnExpression($field);

        $this->getSelect()
            ->having($this->_getConditionSql($field, $condition));

        return $this;
    }

    protected function _columnExpression($field)
    {
        $columns = $this->getSelect()->getPart(Zend_Db_Select::COLUMNS);
        foreach ($columns as $column) {
            if ($column[2] == $field) {
                if (is_object($column[1])) {
                    $expr = $column[1]->__toString();
                } else {
                    $expr = $column[1];
                }

                if (strpos($expr, 'COUNT(') !== false
                    || strpos($expr, 'AVG(') !== false
                    || strpos($expr, 'SUM(') !== false
                    || strpos($expr, 'CONCAT(') !== false) {
                    return $expr;
                }
            }
        }

        return $field;
    }
}
