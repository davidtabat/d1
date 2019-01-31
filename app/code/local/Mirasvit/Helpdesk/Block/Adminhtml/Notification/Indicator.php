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



class Mirasvit_Helpdesk_Block_Adminhtml_Notification_Indicator extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * @return Mirasvit_Helpdesk_Model_Config
     */
    protected function getConfig() {
        return Mage::getSingleton('helpdesk/config');
    }

    const NOTIFICATION_INTERVAL = 5;

    /**
     * @return int
     */
    public function getNotificationInterval()
    {
        return self::NOTIFICATION_INTERVAL;
    }

    /**
     * @return string
     */
    public function getCheckNotificationUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/helpdesk_ticket/checknotification');
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        if (!$this->getConfig()->getDesktopNotificationIsActive()) {
            return '';
        }
        return parent::_toHtml();
    }
}
