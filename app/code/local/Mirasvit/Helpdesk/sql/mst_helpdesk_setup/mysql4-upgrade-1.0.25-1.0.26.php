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
if ($version == '1.0.26') {
    return;
} elseif ($version != '1.0.25') {
    die('Please, run migration Helpdesk 1.0.25');
}
$installer->startSetup();

if (Mage::registry('mst_allow_drop_tables')) {
    $sql = "
       DROP TABLE IF EXISTS `{$this->getTable('helpdesk/priority_store')}`;
       DROP TABLE IF EXISTS `{$this->getTable('helpdesk/status_store')}`;
       DROP TABLE IF EXISTS `{$this->getTable('helpdesk/department_store')}`;
    ";
    $installer->run($sql);
}

$sql = "
ALTER TABLE `{$this->getTable('helpdesk/user')}` ADD COLUMN `store_id` SMALLINT(5) unsigned;

ALTER TABLE  {$this->getTable('helpdesk/user')}  ADD  FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE;
";
$helper = Mage::helper('helpdesk/migration');
$helper->trySql($installer, $sql);

$sql = "
CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/priority_store')}` (
    `priority_store_id` int(11) NOT NULL AUTO_INCREMENT,
    `ps_priority_id` INT(11) NOT NULL,
    `ps_store_id` SMALLINT(5) unsigned NOT NULL,
    KEY `fk_helpdesk_priority_store_priority_id` (`ps_priority_id`),
    CONSTRAINT `mst_fb5f1ca249f85b05a666db4b5ed371f9` FOREIGN KEY (`ps_priority_id`) REFERENCES `{$this->getTable('helpdesk/priority')}` (`priority_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `fk_helpdesk_priority_store_store_id` (`ps_store_id`),
    CONSTRAINT `mst_d9fcc4cb51d05058e1eef0a725e86c8a` FOREIGN KEY (`ps_store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`priority_store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/status_store')}` (
    `status_store_id` int(11) NOT NULL AUTO_INCREMENT,
    `ss_status_id` INT(11) NOT NULL,
    `ss_store_id` SMALLINT(5) unsigned NOT NULL,
    KEY `fk_helpdesk_status_store_status_id` (`ss_status_id`),
    CONSTRAINT `mst_1a81d169d679d606f6f13a00ef551f0c` FOREIGN KEY (`ss_status_id`) REFERENCES `{$this->getTable('helpdesk/status')}` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `fk_helpdesk_status_store_store_id` (`ss_store_id`),
    CONSTRAINT `mst_46a07dfa1c31892cc3136b8a804bfd80` FOREIGN KEY (`ss_store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`status_store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/department_store')}` (
    `department_store_id` int(11) NOT NULL AUTO_INCREMENT,
    `ds_department_id` INT(11) NOT NULL,
    `ds_store_id` SMALLINT(5) unsigned NOT NULL,
    KEY `fk_helpdesk_department_store_department_id` (`ds_department_id`),
    CONSTRAINT `mst_2388d44582d6138f413365ac15e09711` FOREIGN KEY (`ds_department_id`) REFERENCES `{$this->getTable('helpdesk/department')}` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `fk_helpdesk_department_store_store_id` (`ds_store_id`),
    CONSTRAINT `mst_8544a39d25983557b836067a79f8180c` FOREIGN KEY (`ds_store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`department_store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('helpdesk/department')}` ADD COLUMN `is_show_in_frontend` TINYINT(1) NOT NULL DEFAULT 1;
";
$helper = Mage::helper('helpdesk/migration');
$helper->trySql($installer, $sql);

/*                                    **/

$installer->endSetup();
