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



class Mirasvit_Helpdesk_Block_Contacts_Form extends Mage_Core_Block_Template
{
    public function isSecure()
    {
        $isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443);

        return $isHTTPS;
    }

    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    public function getContactUsIsActive()
    {
        return $this->getConfig()->getGeneralContactUsIsActive();
    }

    public function getFormAction()
    {
        return Mage::getUrl('helpdesk/form/post', array('_secure' => $this->isSecure()));
    }

    public function getFrontendIsAllowPriority()
    {
        return $this->getConfig()->getFrontendIsAllowPriority();
    }

    public function getFrontendIsAllowDepartment()
    {
        return $this->getConfig()->getFrontendIsAllowDepartment();
    }

    public function getPriorityCollection()
    {
        return Mage::getModel('helpdesk/priority')->getPreparedCollection(Mage::app()->getStore());
    }

    public function getDepartmentCollection()
    {
        return Mage::getModel('helpdesk/department')->getPreparedCollection(Mage::app()->getStore())
            ->addFieldToFilter('is_show_in_frontend', true);
    }

    public function getCustomFields()
    {
        $collection = Mage::helper('helpdesk/field')->getContactFormCollection();

        return $collection;
    }

    public function getInputHtml($field)
    {
        return Mage::helper('helpdesk/field')->getInputHtml($field);
    }
}
