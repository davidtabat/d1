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

$sql = "
UPDATE `{$this->getTable('helpdesk/status')}` SET color='green' WHERE status_id=1;
UPDATE `{$this->getTable('helpdesk/status')}` SET color='red' WHERE status_id=2;
UPDATE `{$this->getTable('helpdesk/status')}` SET color='blue' WHERE status_id=3;
UPDATE `{$this->getTable('helpdesk/priority')}` SET color='red' WHERE priority_id=1;
UPDATE `{$this->getTable('helpdesk/priority')}` SET color='orange' WHERE priority_id=2;
UPDATE `{$this->getTable('helpdesk/priority')}` SET color='yellow' WHERE priority_id=3;
";
$installer->run($sql);
