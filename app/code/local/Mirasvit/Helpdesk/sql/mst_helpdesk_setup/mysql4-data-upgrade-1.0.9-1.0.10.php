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
$count = $connection->fetchOne('SELECT count(*) FROM '.$this->getTable('helpdesk/permission'));
if ($count == 0) {
    $sql = "
   INSERT INTO `{$this->getTable('helpdesk/permission')}` (role_id,is_ticket_remove_allowed) VALUES (NULL,'1');

   INSERT INTO `{$this->getTable('helpdesk/permission_department')}` (permission_id,department_id) VALUES ('1', NULL);

";
    $installer->run($sql);
}
