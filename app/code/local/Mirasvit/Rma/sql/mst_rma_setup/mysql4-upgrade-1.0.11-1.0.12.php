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
 * @package   RMA
 * @version   2.0.7
 * @build     1267
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('mst_rma');
if ($version == '1.0.12') {
    return;
} elseif ($version != '1.0.11') {
    die('Please, run migration Rma 1.0.11');
}
$installer->startSetup();
$sql = '
';
$installer->run($sql);

Mage::helper('mstcore')->copyConfigData('rma/general/return_period', 'rma/policy/return_period');

/*                                    **/

$installer->endSetup();
