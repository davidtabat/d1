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



class Mirasvit_Helpdesk_Helper_Report extends Mage_Core_Helper_Abstract
{
    const TODAY = 'today';
    const YESTERDAY = 'yesterday';
    const THIS_WEEK = 'week';
    const PREVIOUS_WEEK = 'prev_week';
    const THIS_MONTH = 'month';
    const PREVIOUS_MONTH = 'prev_month';
    const THIS_QUARTER = 'quarter';
    const PREVIOUS_QUARTER = 'prev_quarter';
    const THIS_YEAR = 'year';
    const PREVIOUS_YEAR = 'prev_year';

    const LAST_24H = 'last_24h';
    const LAST_7D = 'last_7d';
    const LAST_30D = 'last_30d';
    const LAST_3M = 'last_3m';
    const LAST_12M = 'last_12m';

    const LIFETIME = 'lifetime';
    const CUSTOM = 'custom';

    public function dateFormat()
    {
        return Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
    }

    public function getSolvedStatuses()
    {
        return Mage::getSingleton('helpdesk/config')->getSolvedStatuses();
    }

    public function calendarDateFormat()
    {
        return Varien_Date::convertZendToStrFtime($this->dateFormat());
    }

    public function timeCallback($value, $row, $column)
    {
        // $value /= 1000;

        $s = $value % 60;
        $m = floor(($value % 3600) / 60);
        $h = floor(($value % 86400) / 3600);
        $d = floor(($value % 2592000) / 86400);
        $M = floor($value / 2592000);

        $output = array();

        if ($M > 0) {
            $output [] = "$M m";#.($M > 1 ? 'months' : 'month');
        }
        if ($d > 0) {
            $output [] = "$d d";#.($d > 1 ? 'days' : 'day');
        }
        if ($h > 0) {
            $output [] = "$h h";#.($h > 1 ? 'hours' : 'hour');
        }
        if ($m > 0) {
            $output [] = "$m m";#.($m > 1 ? 'mins' : 'min');
        }

        return implode(' ', $output);
    }

    public function periodCallback($value, $row, $column)
    {
        $column = $column->getGrid()->getCollection()->getFilterData()->getPeriod();

        if ($value === '') {
            return '';
        }

        switch ($column) {
            case 'month':
                $value = date('M, Y', strtotime($value));
                break;

            case 'day_of_week':
                $value = date('D', strtotime("Monday +$value days"));
                break;

            case 'hour_of_day':
                $value = date('h:00 A',  strtotime($value));
                break;

            default:
                $value = date('d M, Y', strtotime($value));
                break;
        }

        return $value;
    }

    public function groupByCallback($value, $row, $column)
    {
        $column = $column->getGrid()->getCollection()->getFilterData()->getGroupBy();

        if ($value === '') {
            return '';
        }

        switch ($column) {
            case 'agent':
                $value = $row->getUserId();
                $users = Mage::helper('helpdesk')->getAdminUserOptionArray();

                if (isset($users[$value])) {
                    $value = $users[$value];
                } else {
                    $value = '-';
                }
                break;

            case 'department':
                $department = Mage::getModel('helpdesk/department')->load($row->getGroupBy());

                if ($department->getId()) {
                    $value = $department->getName();
                } else {
                    $value = '-';
                }
                break;
        }

        return $value;
    }

    public function votesCallback($value, $row, $column)
    {
        $html = array();
        $rateColors = array('#f00', '#B58800', '#00B300');
        foreach ($rateColors as $idx => $color) {
            $html[] = '<span style="color:'.$color.'">'.$row->getData('satisfaction_rate_'.($idx + 1).'_cnt').'</span>';
        }

        return implode(' · ', $html);
    }

    public function percentCallback($value, $row, $column)
    {
        return round($value, 1).'%';
    }

    public function getIntervals($subintervals = false, $lifetime = false, $custom = false)
    {
        $intervals = array();

        $intervals[self::TODAY] = 'Today';
        $intervals[self::YESTERDAY] = 'Yesterday';

        $intervals[self::THIS_WEEK] = 'This week';
        $intervals[self::PREVIOUS_WEEK] = 'Previous week';

        $intervals[self::THIS_MONTH] = 'This month';
        $intervals[self::PREVIOUS_MONTH] = 'Previous month';

        $intervals[self::THIS_QUARTER] = 'This quarter';
        $intervals[self::PREVIOUS_QUARTER] = 'Previous quarter';

        $intervals[self::THIS_YEAR] = 'This year';
        $intervals[self::PREVIOUS_YEAR] = 'Previous year';

        if ($subintervals) {
            $intervals[self::LAST_24H] = 'Last 24h hours';
            $intervals[self::LAST_7D] = 'Last 7 days';
            $intervals[self::LAST_30D] = 'Last 30 days';
            $intervals[self::LAST_3M] = 'Last 3 months';
            $intervals[self::LAST_12M] = 'Last 12 months';
        }

        if ($lifetime) {
            $intervals[self::LIFETIME] = 'Lifetime';
        }

        if ($custom) {
            $intervals[self::CUSTOM] = 'Custom';
        }

        foreach ($intervals as $code => $label) {
            $label = Mage::helper('helpdesk')->__($label);

            $hint = $this->getIntervalHint($code);

            if ($hint) {
                $label .= ' / '.$hint;
            }

            $intervals[$code] = $label;
        }

        return $intervals;
    }

    public function getIntervalHint($code)
    {
        $hint = '';

        $interval = $this->getInterval($code, true);
        $from = $interval->getFrom();
        $to = $interval->getTo();

        switch ($code) {
            case self::TODAY:
            case self::YESTERDAY:
                $hint = $from->get('MMM, d');
                break;

            case self::THIS_MONTH:
            case self::PREVIOUS_MONTH:
                $hint = $from->get('MMM');
                break;

            case self::THIS_QUARTER:
            case self::PREVIOUS_QUARTER:
                $hint = $from->get('MMM').' - '.$to->get('MMM');
                break;

            case self::THIS_YEAR:
            case self::PREVIOUS_YEAR:
                $hint = $from->get('YYYY');
                break;

            case self::LAST_24H:
                $hint = $from->get('MMM, d HH:mm').' - '.$to->get('MMM, d HH:mm');
                break;

            case self::THIS_WEEK:
            case self::PREVIOUS_WEEK:
            case self::LAST_7D:
            case self::LAST_30D:
            case self::LAST_3M:
            case self::LAST_12M:
                $hint = $from->get('MMM, d').' - '.$to->get('MMM, d');
                break;
        }

        return $hint;
    }

    public function getIntervalsAsOptions($subintervals = false, $lifetime = false, $custom = false)
    {
        $intervals = $this->getIntervals($subintervals, $lifetime, $custom);
        $options = array();

        foreach ($intervals as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label,
            );
        }

        return $options;
    }

    /**
     * Return interval (two GMT Zend_Date).
     *
     * @param $code
     * @param bool $timezone
     *
     * @return Varien_Object
     */
    public function getInterval($code, $timezone = false)
    {
        $timestamp = Mage::getSingleton('core/date')->gmtTimestamp();

        if ($timezone) {
            $timestamp = Mage::app()->getLocale()->date($timestamp);
        }

        $from = new Zend_Date(
            $timestamp,
            null,
            Mage::app()->getLocale()->getLocaleCode());
        $to = clone $from;

        $dateInterval = null;

        switch ($code) {
            case self::TODAY:
                $from->setTime('00:00:00');

                $to->setTime('23:59:59');

                break;

            case self::YESTERDAY:
                $from->subDay(1)
                    ->setTime('00:00:00');

                $to->subDay(1)
                    ->setTime('23:59:59');

                break;

            case self::THIS_MONTH:
                $from->setDay(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->addDay($to->get(Zend_Date::MONTH_DAYS) - 1)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_MONTH:
                $from->setDay(1)
                    ->subMonth(1)
                    ->setTime('00:00:00')
                    ;

                $to->setDay(1)
                    ->setTime('23:59:59')
                    ->subMonth(1)
                    ->addDay($to->get(Zend_Date::MONTH_DAYS) - 1);

                break;

            case self::THIS_QUARTER:
                $month = intval($from->get(Zend_Date::MONTH) / 4) * 3 + 1;
                $from->setDay(1)
                    ->setMonth($month)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth($month)
                    ->addMonth(3)
                    ->subDay(1)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_QUARTER:
                $month = intval($from->get(Zend_Date::MONTH) / 4) * 3 + 1;

                $from->setDay(1)
                    ->setMonth($month)
                    ->setTime('00:00:00')
                    ->subMonth(3);

                $to->setDay(1)
                    ->setMonth($month)
                    ->addMonth(3)
                    ->subDay(1)
                    ->setTime('23:59:59')
                    ->subMonth(3);

                break;

            case self::THIS_YEAR:
                $from->setDay(1)
                    ->setMonth(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth(1)
                    ->addDay($to->get(Zend_Date::LEAPYEAR) ? 365 : 364)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_YEAR:
                $from->setDay(1)
                    ->setMonth(1)
                    ->setTime('00:00:00')
                    ->subYear(1);

                $to->setDay(1)
                    ->setMonth(1)
                    ->addDay($to->get(Zend_Date::LEAPYEAR) ? 365 : 364)
                    ->setTime('23:59:59')
                    ->subYear(1);

                break;

            case self::LAST_24H:
                $from->subDay(1);

                break;

            case self::THIS_WEEK:
                $weekday = $from->get(Zend_Date::WEEKDAY_DIGIT); #0-6

                $from->setTime('00:00:00')
                    ->subDay($weekday);

                $to->setTime('23:59:59')
                    ->addDay(6 - $weekday);

                break;

            case self::PREVIOUS_WEEK:
                $weekday = $from->get(Zend_Date::WEEKDAY_DIGIT); #0-6

                $from->setTime('00:00:00')
                    ->subDay($weekday)
                    ->subWeek(1);

                $to->setTime('23:59:59')
                    ->addDay(6 - $weekday)
                    ->subWeek(1);

                break;

            case self::LAST_7D:
                $from->subDay(7);

                break;

            case self::LAST_30D:
                $from->subDay(30);

                break;

            case self::LAST_3M:
                $from->subMonth(3);

                break;

            case self::LAST_12M:
                $from->subYear(1);

                break;

            case self::LIFETIME:
                $from->subYear(10);

                $to->addYear(10);

                break;
        }

        return new Varien_Object(array(
            'from' => $from,
            'to' => $to, ));
    }

    public function getPreviousInterval($code, $offsetDays = 0, $timezone = false)
    {
        $interval = $this->getInterval($code, $timezone);

        $now = new Zend_Date(
            Mage::getSingleton('core/date')->gmtTimestamp(),
            null,
            Mage::app()->getLocale()->getLocaleCode());

        $diff = clone $interval->getTo();
        $diff->sub($interval->getFrom());

        if ($timezone) {
            $diff->sub(Mage::getSingleton('core/date')->getGmtOffset());
        }

        if ($interval->getTo()->getTimestamp() > $now->getTimestamp()) {
            $interval->getTo()->subTimestamp($interval->getTo()->getTimestamp() - $now->getTimestamp());
        }

        if (intval($offsetDays) > 0) {
            $interval->getFrom()->subDay($offsetDays);
            $interval->getTo()->subDay($offsetDays);
        } else {
            $interval->getFrom()->sub($diff);
            $interval->getTo()->sub($diff);
        }

        return $interval;
    }
}
