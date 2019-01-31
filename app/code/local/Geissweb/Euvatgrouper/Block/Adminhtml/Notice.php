<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Block_Adminhtml_Notice extends Mage_Adminhtml_Block_Template
{
    /**
     * @return bool
     */
    public function isInstalled()
    {
        return Mage::getStoreConfigFlag('euvatgrouper/extension_info/is_installed');
    }

    /**
     * @return string
     */
    public function getSetupUrl()
    {
        return $this->getUrl('adminhtml/vat_setup');
    }

    /**
     * @return string
     */
    public function getSkipUrl()
    {
        return $this->getUrl('adminhtml/vat_setup/skip');
    }

}