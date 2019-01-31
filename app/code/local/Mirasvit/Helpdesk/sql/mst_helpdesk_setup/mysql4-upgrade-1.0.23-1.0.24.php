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
if ($version == '1.0.24') {
    return;
} elseif ($version != '1.0.23') {
    die('Please, run migration Helpdesk 1.0.23');
}
$installer->startSetup();
if (Mage::registry('mst_allow_drop_tables')) {
    $sql = '
    ';
    $installer->run($sql);
}
$sql = "
ALTER TABLE `{$this->getTable('helpdesk/ticket_aggregated')}` ADD COLUMN `total_reply_cnt` INT(11);
ALTER TABLE `{$this->getTable('helpdesk/ticket_aggregated')}` ADD COLUMN `changed_ticket_cnt` INT(11);
ALTER TABLE `{$this->getTable('helpdesk/ticket_aggregated')}` MODIFY period DATETIME;
";
$helper = Mage::helper('helpdesk/migration');
$helper->trySql($installer, $sql);

/*                                    **/

$installer->endSetup();
