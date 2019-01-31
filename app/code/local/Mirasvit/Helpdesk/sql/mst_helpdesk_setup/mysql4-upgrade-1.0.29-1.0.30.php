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
if ($version == '1.0.30') {
    return;
} elseif ($version != '1.0.29') {
    die('Please, run migration Helpdesk 1.0.29');
}
$installer->startSetup();
$sql = "
ALTER TABLE `{$this->getTable('helpdesk/message')}` ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT '0' AFTER is_read;

ALTER TABLE `{$this->getTable('helpdesk/permission')}`
ADD COLUMN `is_message_edit_allowed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_ticket_remove_allowed`,
ADD COLUMN `is_message_remove_allowed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_message_edit_allowed`;
";
$helper = Mage::helper('helpdesk/migration');
$helper->trySql($installer, $sql);

/*                                    **/

$installer->endSetup();
