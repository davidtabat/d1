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



class Mirasvit_Helpdesk_Model_Observer
{
    protected $cronChecked = false;

    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    public function checkCronStatus()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        if (!$admin) {
            return;
        }
        if (Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax')) {
            return;
        }
        if ($this->cronChecked) {
            return;
        }

        try {
            $gateways = Mage::getModel('helpdesk/gateway')->getCollection()
                        ->addFieldToFilter('is_active', true);
            if ($gateways->count() == 0) {
                return;
            }
        } catch (Exception $e) { //it's possible that tables are not created yet. so we have to catch this error.
            return;
        }
        if ($this->getConfig()->getGeneralIsDefaultCron()) {
            //@dva we need such double check, because of incompatibility with old versions of MstCore
            if (!Mage::helper('mstcore/cron')->isCronRunning('mirasvit_helpdesk')) {
                $message = Mage::helper('mstcore/cron')->checkCronStatus('mirasvit_helpdesk', false);
                $message = 'Help desk can\'t fetch emails. '.$message;
                $message .= Mage::helper('helpdesk')->__('<br> To temporary hide this message, disable all <a href="%s">help desk gateways</a>.', Mage::helper('adminhtml')->getUrl('adminhtml/helpdesk_gateway'));
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        } else {
            $gateways = Mage::getModel('helpdesk/gateway')->getCollection()
                        ->addFieldToFilter('is_active', true)
                        ->addFieldToFilter('fetched_at', array('gt' => Mage::getSingleton('core/date')->gmtDate(null, Mage::getSingleton('core/date')->timestamp() - 60 * 60 * 3)));

            if ($gateways->count() == 0) {
                $message = Mage::helper('helpdesk')->__('Help Desk can\'t fetch new emails. Please, check that you are running cron for script /shell/helpdesk.php.');
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }

        $this->cronChecked = true;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onUserLoadAfter($observer)
    {
        /** @var Mage_Admin_Model_User $user */
        $user = $observer->getObject();
        if (!$user->getId()) {
            return;
        }
        $helpdeskUser = Mage::helper('helpdesk')->getHelpdeskUser($user);
        if (!$helpdeskUser) {
            return;
        }
        $user->setSignature($helpdeskUser->getSignature());
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function onUserSaveAfter($observer)
    {
        /** @var Mage_Admin_Model_User $user */
        $user = $observer->getObject();
        if (!$user->getId()) {
            return;
        }
        $helpdeskUser = Mage::helper('helpdesk')->getHelpdeskUser($user);
        if (!$helpdeskUser) {
            return;
        }
        $helpdeskUser->setSignature($user->getSignature());
        $helpdeskUser->save();
    }
}
