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



$blocks = array(
    'helpdesk/email_satisfaction',
);
$path = Mage::getBaseDir('code').'/core/Mage/Admin/Model/Block.php';
if (!file_exists($path)) {
    return;
}
foreach ($blocks as $block) {
    $collection = Mage::getModel('admin/block')->getCollection()
        ->addFieldToFilter('block_name', $block);
    if ($collection->count() == 0) {
        Mage::getModel('admin/block')
            ->setBlockName($block)
            ->setIsAllowed(1)
            ->save();
    }
}
