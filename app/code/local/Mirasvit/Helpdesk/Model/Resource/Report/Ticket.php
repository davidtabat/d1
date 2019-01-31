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



class Mirasvit_Helpdesk_Model_Resource_Report_Ticket extends Mage_Core_Model_Mysql4_Abstract
{
    const FLAG_CODE = 'report_ticket';

    protected function _construct()
    {
        $this->_init('helpdesk/ticket_aggregated', 'ticket_aggregated_id');

        $this->_setResource(array('read', 'write'));
    }

    public function aggregate($from = null)
    {
        if (!is_null($from)) {
            $from = $this->formatDate($from);
        }

        if ($from == null) {
            $from = new Zend_Date(
                Mage::getSingleton('core/date')->gmtTimestamp(),
                null,
                Mage::app()->getLocale()->getLocaleCode()
            );

            $from->subYear(10);

            $this->_aggregateTickets($from->get(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $this->_aggregateTickets($from);
        }

        $this->_refreshFlag();

        return $this;
    }

    protected function _refreshFlag()
    {
        $flag = Mage::getModel('reports/flag');
        $flag->setReportFlagCode(self::FLAG_CODE)
            ->unsetData()
            ->loadSelf()
            ->setLastUpdate($this->formatDate(time()))
            ->save()
        ;
    }

    protected function _aggregateTickets($from)
    {
        $adapter = $this->_getWriteAdapter();

        $ticketTable = $this->getTable('helpdesk/ticket');
        $messageTable = $this->getTable('helpdesk/message');
        $satisfactionTable = $this->getTable('helpdesk/satisfaction');

        $aggregateTables = array(
            $this->getTable('helpdesk/ticket_aggregated_hour') => '%Y-%m-%d %H:00:00',
            $this->getTable('helpdesk/ticket_aggregated_day') => '%Y-%m-%d 00:00:00',
            $this->getTable('helpdesk/ticket_aggregated_month') => '%Y-%m-01 00:00:00',
        );

        foreach ($aggregateTables as $tableName => $periodFormat) {
            // remove data before insert
            $adapter->delete($tableName, "period >= '$from'");

            $periodStatement = new Zend_Db_Expr('DATE_FORMAT(created_at, "'.$periodFormat.'")');

            $ticketSelect = $adapter->select()
                ->from(
                    array($ticketTable),
                    array(
                        'user_id' => 'user_id',
                        'period' => $periodStatement,
                    )
                )
                ->where('is_spam = 0')
                ->where('created_at >= ?', $from)
                ->group($periodStatement)
                ->group('user_id')
            ;

            $periodStatementByUpdatedAt = new Zend_Db_Expr('DATE_FORMAT(updated_at, "'.$periodFormat.'")');
            $ticketSelectByUpdatedAt = $adapter->select()
                ->from(
                    array($ticketTable),
                    array(
                        'user_id' => 'user_id',
                        'period' => $periodStatementByUpdatedAt,
                    )
                )
                ->where('is_spam = 0')
                ->where('updated_at >= ?', $from)
                ->group($periodStatementByUpdatedAt)
                ->group('user_id')
            ;

            $messageSelect = $adapter->select()
                ->from(
                    array($messageTable),
                    array(
                        'user_id' => 'user_id',
                        'period' => $periodStatement,
                    )
                )
                ->where('created_at >= ?', $from)
                ->group($periodStatement)
                ->group('user_id')
            ;

            $satisfactionSelect = $adapter->select()
                ->from(
                    array($satisfactionTable),
                    array(
                        'user_id' => 'user_id',
                        'period' => $periodStatement,
                    )
                )
                ->where('created_at >= ?', $from)
                ->group($periodStatement)
                ->group('user_id')
            ;

            // number of new tickes
            $newTicketSql = clone $ticketSelect;
            $newTicketSql
                ->columns(array('new_ticket_cnt' => new Zend_Db_Expr('COUNT(ticket_id)')))
            ;

            $this->_insertOnDublicate($tableName, $newTicketSql, array('new_ticket_cnt'));

            // number of changed tickets (number of unique tickets with replies)
            $changedTicketSql = clone $messageSelect;
            $changedTicketSql
                ->columns(array('changed_ticket_cnt' => new Zend_Db_Expr('COUNT(DISTINCT(ticket_id))')))
                ->where('triggered_by = ?', 'user')
            ;
            $this->_insertOnDublicate($tableName, $changedTicketSql, array('changed_ticket_cnt'));

            // number of replies
            $replyCntSql = clone $messageSelect;
            $replyCntSql
                ->columns(array('total_reply_cnt' => new Zend_Db_Expr('COUNT(message_id)')))
                ->where('triggered_by = ?', 'user')
            ;
            $this->_insertOnDublicate($tableName, $replyCntSql, array('total_reply_cnt'));

            // number of solved tickets
            $solvedTicketSql = clone $ticketSelectByUpdatedAt;
            $solvedTicketSql
                ->columns(array('solved_ticket_cnt' => new Zend_Db_Expr('COUNT(ticket_id)')))
                ->where('status_id IN(?)', Mage::helper('helpdesk/report')->getSolvedStatuses())
            ;
            $this->_insertOnDublicate($tableName, $solvedTicketSql, array('solved_ticket_cnt'));

            // average first reply time (seconds)
            $firstReplyTimeSql = clone $ticketSelect;
            $firstReplyTimeSql
                ->columns(array('first_reply_time' => new Zend_Db_Expr('AVG(UNIX_TIMESTAMP(first_reply_at) - UNIX_TIMESTAMP(created_at))')))
                ->where('first_reply_at IS NOT NULL')
            ;
            $this->_insertOnDublicate($tableName, $firstReplyTimeSql, array('first_reply_time'));

            // average full resolution time (status solved, seconds)
            $fullResolutionTimeSql = clone $ticketSelect;
            $fullResolutionTimeSql
                ->columns(array('full_resolution_time' => new Zend_Db_Expr('AVG(UNIX_TIMESTAMP(last_reply_at) - UNIX_TIMESTAMP(created_at))')))
                ->where('first_reply_at IS NOT NULL')
                ->where('last_reply_at IS NOT NULL')
                ->where('status_id IN(?)', Mage::helper('helpdesk/report')->getSolvedStatuses())
            ;
            $this->_insertOnDublicate($tableName, $fullResolutionTimeSql, array('full_resolution_time'));

            // number of rates (1, 2, 3)
            $satisfactionRateNSql = clone $satisfactionSelect;
            $satisfactionRateNSql
                ->columns(
                    array(
                        'satisfaction_rate_1_cnt' => new Zend_Db_Expr('SUM(IF(rate = 1, 1, 0))'),
                        'satisfaction_rate_2_cnt' => new Zend_Db_Expr('SUM(IF(rate = 2, 1, 0))'),
                        'satisfaction_rate_3_cnt' => new Zend_Db_Expr('SUM(IF(rate = 3, 1, 0))'),
                    )
                )
            ;
            $this->_insertOnDublicate($tableName, $satisfactionRateNSql, array(
                    'satisfaction_rate_1_cnt',
                    'satisfaction_rate_2_cnt',
                    'satisfaction_rate_3_cnt',
                )
            );

            // satisfaction rate
            $satisfactionRateSql = clone $satisfactionSelect;
            $satisfactionRateSql
                ->columns(
                    array(
                        'satisfaction_rate' => new Zend_Db_Expr('SUM(rate) / COUNT(rate) / 3 * 100'),
                    )
                )
            ;
            $this->_insertOnDublicate($tableName, $satisfactionRateSql, array(
                    'satisfaction_rate',
                )
            );

            // satisfaction response count
            $satisfactionResponseCntSql = clone $satisfactionSelect;
            $satisfactionResponseCntSql
                ->columns(
                    array(
                        'satisfaction_response_cnt' => new Zend_Db_Expr('COUNT(rate)'),
                    )
                )
            ;
            $this->_insertOnDublicate($tableName, $satisfactionResponseCntSql, array(
                    'satisfaction_response_cnt',
                )
            );

            // satisfaction response rate
            $adapter->query("UPDATE $tableName
                SET satisfaction_response_rate = IFNULL((satisfaction_response_cnt / total_reply_cnt) * 100, 0)");
        }

        return $this;
    }

    protected function _insertOnDublicate($tableName, $select, $columns)
    {
        $adapter = $this->_getWriteAdapter();

        $rows = $adapter->fetchAll($select);

        $adapter->insertOnDuplicate(
            $tableName,
            $rows,
            $columns
        );

        return $this;
    }
}
