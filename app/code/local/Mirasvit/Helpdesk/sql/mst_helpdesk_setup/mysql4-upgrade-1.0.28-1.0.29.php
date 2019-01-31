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
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('mst_helpdesk');
if ($version == '1.0.29') {
    return;
} elseif ($version != '1.0.28' && $version != '1.0.27') {
    die('Please, run migration Helpdesk 1.0.27');
}
$installer->startSetup();
$sql = "
CREATE TABLE `{$this->getTable('helpdesk/desktopNotification')}` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `message_id` int(10) unsigned DEFAULT NULL,
  `notification_type` VARCHAR(20) NOT NULL,
  `read_by_user_ids` VARCHAR(255) NOT NULL  DEFAULT '', -- null will not work with LIKE here
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `gfhfhgfh_idx` (`ticket_id`),
  CONSTRAINT `fk_mage_m_helpdesk_notification_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `{$this->getTable('helpdesk/ticket')}` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

";
$helper = Mage::helper('helpdesk/migration');
$helper->trySql($installer, $sql);

/*                                    **/

$installer->endSetup();
