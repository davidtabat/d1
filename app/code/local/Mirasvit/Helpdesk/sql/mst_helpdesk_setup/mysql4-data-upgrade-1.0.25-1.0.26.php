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
$collection = Mage::getModel('helpdesk/department')->getCollection();
foreach ($collection as $department) {
    $department->afterLoad();
    $department->setStoreIds(array(0))->save();
}

$collection = Mage::getModel('helpdesk/priority')->getCollection();
foreach ($collection as $priority) {
    $priority->afterLoad();
    $priority->setStoreIds(array(0))->save();
}

$collection = Mage::getModel('helpdesk/status')->getCollection();
foreach ($collection as $status) {
    $status->afterLoad();
    $status->setStoreIds(array(0))->save();
}
