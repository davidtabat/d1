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



/**
 * @method Mirasvit_Helpdesk_Model_Resource_Department_Collection|Mirasvit_Helpdesk_Model_Department[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Department load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Department setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Department setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Department getResource()
 * @method int[] getUserIds()
 * @method Mirasvit_Helpdesk_Model_Department setUserIds(array $ids)
 * @method bool getIsMembersNotificationEnabled()
 * @method Mirasvit_Helpdesk_Model_Department setIsMembersNotificationEnabled(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Department setSenderEmail(string $email)
 */
class Mirasvit_Helpdesk_Model_Department extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/department');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    public function getName()
    {
        return Mage::helper('helpdesk/storeview')->getStoreViewValue($this, 'name');
    }

    public function setName($value)
    {
        Mage::helper('helpdesk/storeview')->setStoreViewValue($this, 'name', $value);

        return $this;
    }

    /**
     * Overrides standard getSenderEmail() method to return proper department email, if customer migrating from older versions.
     *
     * @return string
     */
    public function getSenderEmail()
    {
        $senderEmail = parent::getSenderEmail();
        $emails = Mage::getSingleton('adminhtml/system_config_source_email_identity')->toOptionArray();
        foreach ($emails as $email) {
            $emailAddress = Mage::getStoreConfig("trans_email/ident_{$email['value']}/email");
            if ($email['value'] == $senderEmail) {
                $senderEmail = $emailAddress;
            }
        }

        return $senderEmail;
    }

    public function getNotificationEmail()
    {
        return Mage::helper('helpdesk/storeview')->getStoreViewValue($this, 'notification_email');
    }

    public function setNotificationEmail($value)
    {
        Mage::helper('helpdesk/storeview')->setStoreViewValue($this, 'notification_email', $value);

        return $this;
    }

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        if (isset($data['notification_email']) && strpos($data['notification_email'], 'a:') !== 0) {
            $this->setNotificationEmail($data['notification_email']);
            unset($data['notification_email']);
        }

        return parent::addData($data);
    }
    /************************/

    /**
     * @return Mage_Eav_Model_Entity_Collection_Abstract|Mage_Admin_Model_User[]
     */
    public function getUsers()
    {
        if (!$this->getUserIds()) {
            $this->getResource()->loadUserIds($this);
        }

        return Mage::getModel('admin/user')->getCollection()
            ->addFieldToFilter('user_id', $this->getUserIds())
            ->addFieldToFilter('is_active', true);
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * prepare collection for dropdowns.
     *
     * @param int|Mage_Core_Model_Store $store
     *
     * @return Mirasvit_Helpdesk_Model_Resource_Department_Collection|Mirasvit_Helpdesk_Model_Department[]
     */
    public function getPreparedCollection($store)
    {
        if (is_object($store)) {
            $store = $store->getStoreId();
        }

        return $this->getCollection()
            ->addStoreFilter($store)
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }


    /**
     * Overridden superclass function. Validate is current department available for deletion
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _beforeDelete()
    {
        $errors = array();

        if ($this->getId() == Mage::getSingleton('helpdesk/config')->getContactFormDefaultDepartment()) {
            $errors[] = 'Please, go to the Help Desk > Settings > Feedback Tab > Assign to Department and change department before deletion';
        }

        $gateways = Mage::getModel('helpdesk/gateway')->getCollection()
            ->addFieldToFilter('department_id', $this->getId());

        if ($gateways && $gateways->count()) {
            $errors[] = 'Some gateways are using this department. Please, change department first.';
        }

        $tickets = Mage::getModel('helpdesk/ticket')->getCollection()
            ->addFieldToFilter('department_id', $this->getId());

        if ($tickets && $tickets->count()) {
            $errors[] = 'Some tickets are using this department. Please, change department first.';
        }

        if ($errors) {
            throw new Mage_Core_Exception(implode('<br>', $errors));
        }

        return parent::_beforeDelete();
    }
}
