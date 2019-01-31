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



/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$count = $connection->fetchOne('SELECT count(*) FROM '.$this->getTable('helpdesk/priority'));
if ($count == 0) {
    $sql = "
INSERT INTO `{$this->getTable('helpdesk/priority')}` (name,sort_order) VALUES ('High','30');
INSERT INTO `{$this->getTable('helpdesk/priority')}` (name,sort_order) VALUES ('Medium','20');
INSERT INTO `{$this->getTable('helpdesk/priority')}` (name,sort_order) VALUES ('Low','10');

INSERT INTO `{$this->getTable('helpdesk/status')}` (name,code,sort_order) VALUES ('Open','open','10');
INSERT INTO `{$this->getTable('helpdesk/status')}` (name,code,sort_order) VALUES ('In Progress','in_progress','20');
INSERT INTO `{$this->getTable('helpdesk/status')}` (name,code,sort_order) VALUES ('Closed','closed','30');

INSERT INTO `{$this->getTable('helpdesk/department')}` (name,sort_order,sender_email,is_notification_enabled,notification_email,is_active) VALUES ('Sales','10','sales','1','','1');
INSERT INTO `{$this->getTable('helpdesk/department')}` (name,sort_order,sender_email,is_notification_enabled,notification_email,is_active) VALUES ('Support','20','sales','1','','1');

";
    $installer->run($sql);
}

$userId = $connection->fetchOne('SELECT user_id FROM '.$this->getTable('admin/user').' LIMIT 0,1');
if ($userId) {
    $sql = "
INSERT INTO `{$this->getTable('helpdesk/department_user')}` (du_department_id,du_user_id) VALUES (1, $userId);
INSERT INTO `{$this->getTable('helpdesk/department_user')}` (du_department_id,du_user_id) VALUES (2, $userId);
";
    $helper = Mage::helper('helpdesk/migration');
    $helper->trySql($installer, $sql);
}
