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



$installer = $this;
$sql = "
DROP TABLE IF EXISTS `{$this->getTable('helpdesk/ticket_aggregated')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/ticket_aggregated_hour')}` (
    `period`                     DATETIME,
    `store_id`                   SMALLINT(5) NOT NULL DEFAULT '0',
    `user_id`                    INT(11)  NOT NULL DEFAULT '0',

    `new_ticket_cnt`             INT(11)  NOT NULL DEFAULT '0',
    `solved_ticket_cnt`          INT(11)  NOT NULL DEFAULT '0',
    `changed_ticket_cnt`         INT(11)  NOT NULL DEFAULT '0',

    `total_reply_cnt`            INT(11)  NOT NULL DEFAULT '0',

    `first_reply_time`           INT(11)  NOT NULL DEFAULT '0',
    `first_resolution_time`      INT(11)  NOT NULL DEFAULT '0',
    `full_resolution_time`       INT(11)  NOT NULL DEFAULT '0',

    `satisfaction_rate_1_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate_2_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate_3_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate`          INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_response_cnt`  INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_response_rate` INT(11)  NOT NULL DEFAULT '0',

    UNIQUE KEY `period_hour` (`period`, `store_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/ticket_aggregated_day')}` (
    `period`                     DATETIME,
    `store_id`                   SMALLINT(5) NOT NULL DEFAULT '0',
    `user_id`                    INT(11)  NOT NULL DEFAULT '0',

    `new_ticket_cnt`             INT(11)  NOT NULL DEFAULT '0',
    `solved_ticket_cnt`          INT(11)  NOT NULL DEFAULT '0',
    `changed_ticket_cnt`         INT(11)  NOT NULL DEFAULT '0',

    `total_reply_cnt`            INT(11)  NOT NULL DEFAULT '0',

    `first_reply_time`           INT(11)  NOT NULL DEFAULT '0',
    `first_resolution_time`      INT(11)  NOT NULL DEFAULT '0',
    `full_resolution_time`       INT(11)  NOT NULL DEFAULT '0',

    `satisfaction_rate_1_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate_2_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate_3_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate`          INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_response_cnt`  INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_response_rate` INT(11)  NOT NULL DEFAULT '0',

    UNIQUE KEY `period_hour` (`period`, `store_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/ticket_aggregated_month')}` (
    `period`                     DATETIME,
    `store_id`                   SMALLINT(5) NOT NULL DEFAULT '0',
    `user_id`                    INT(11)  NOT NULL DEFAULT '0',

    `new_ticket_cnt`             INT(11)  NOT NULL DEFAULT '0',
    `solved_ticket_cnt`          INT(11)  NOT NULL DEFAULT '0',
    `changed_ticket_cnt`         INT(11)  NOT NULL DEFAULT '0',

    `total_reply_cnt`            INT(11)  NOT NULL DEFAULT '0',

    `first_reply_time`           INT(11)  NOT NULL DEFAULT '0',
    `first_resolution_time`      INT(11)  NOT NULL DEFAULT '0',
    `full_resolution_time`       INT(11)  NOT NULL DEFAULT '0',

    `satisfaction_rate_1_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate_2_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate_3_cnt`    INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_rate`          INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_response_cnt`  INT(11)  NOT NULL DEFAULT '0',
    `satisfaction_response_rate` INT(11)  NOT NULL DEFAULT '0',

    UNIQUE KEY `period_hour` (`period`, `store_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";
$installer->run($sql);

$installer->endSetup();
