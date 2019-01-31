<?php
/**
 * Localized Magento Editions
 *
 * @category   IntegerNet
 * @package    IntegerNet_MagentoLocalized
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

class IntegerNet_MagentoLocalizedEbay_Adminhtml_MagentoLocalizedEbayController extends Mage_Adminhtml_Controller_Action
{
    public function paypalAction()
    {
        $this->_forward('edit', 'system_config', 'admin', array('section' => 'payment'));
    }

    public function paypalPopupAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function m2eAction()
    {
        if (Mage::getStoreConfig('magento_localized/installed_modules/ess/m2e-pro')) {
            $this->_forward('installation', 'adminhtml_wizard_installationEbay', 'M2ePro');
        } else {
            $this->loadLayout();
            $this->getLayout()->getBlock('content')->append(
                $this->getLayout()
                    ->createBlock('magento_localized_ebay/form', 'magento_localized_ebay.m2e')
                    ->setTemplate('magento_localized_ebay/m2e.phtml')
            );
            $this->renderLayout();
        }
    }

    public function installM2eAction()
    {
        Mage::getSingleton('magento_localized/installer')->installPackageByName('ess/m2e-pro');
        $this->_redirectReferer();
    }

    public function billsafeAction()
    {
        if (Mage::getStoreConfig('magento_localized/installed_modules/billsafe/billsafe')) {
            $this->_forward('edit', 'system_config', 'admin', array('section' => 'payment'));
        } else {
            $this->loadLayout();
            $this->getLayout()->getBlock('content')->append(
                $this->getLayout()
                    ->createBlock('magento_localized_ebay/form', 'magento_localized_ebay.billsafe')
                    ->setTemplate('magento_localized_ebay/billsafe.phtml')
            );
            $this->renderLayout();
        }
    }

    public function installBillsafeAction()
    {
        Mage::getSingleton('magento_localized/installer')->installPackageByName('billsafe/billsafe');
        $this->_redirectReferer();
    }
}
