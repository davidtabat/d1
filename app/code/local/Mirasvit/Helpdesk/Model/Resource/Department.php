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



class Mirasvit_Helpdesk_Model_Resource_Department extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/department', 'department_id');
    }

    public function loadUserIds(Mage_Core_Model_Abstract $object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Department $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('helpdesk/department_user'))
            ->where('du_department_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['du_user_id'];
            }
            $object->setData('user_ids', $array);
        }

        return $object;
    }

    protected function saveUserIds($object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Department $object */
        $condition = $this->_getWriteAdapter()->quoteInto('du_department_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('helpdesk/department_user'), $condition);
        foreach ((array) $object->getData('user_ids') as $id) {
            $objArray = array(
                'du_department_id' => $object->getId(),
                'du_user_id' => $id,
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('helpdesk/department_user'), $objArray);
        }
    }

    protected function loadStoreIds(Mage_Core_Model_Abstract $object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Department $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('helpdesk/department_store'))
            ->where('ds_department_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['ds_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    protected function saveStoreIds($object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Department $object */
        $condition = $this->_getWriteAdapter()->quoteInto('ds_department_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('helpdesk/department_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = array(
                'ds_department_id' => $object->getId(),
                'ds_store_id' => $id,
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('helpdesk/department_store'), $objArray);
        }
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Department $object */
        if (!$object->getIsMassDelete()) {
            $this->loadUserIds($object);
            $this->loadStoreIds($object);
        }

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Department $object */
        if (!$object->getId()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Department $object */
        if (!$object->getIsMassStatus()) {
            $this->saveUserIds($object);
            $this->saveStoreIds($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
