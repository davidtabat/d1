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
 * @package   Advanced SEO Suite
 * @version   1.3.5
 * @build     1248
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Seo_Model_Resource_Redirect extends Mage_Core_Model_Mysql4_Abstract
{
   protected function _construct()
    {
        $this->_init('seo/redirect', 'redirect_id');
    }

    public function loadStore(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('seo/redirect_store'))
            ->where('redirect_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['store_id'];
            }
            $object->setData('store_ids', $array);
        }
        return $object;
    }
    protected function saveStore($object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('redirect_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('seo/redirect_store'), $condition);
        foreach ((array)$object->getData('store_ids') as $store) {
            $storeArray = array(
                'redirect_id'  => $object->getId(),
                'store_id' => $store
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('seo/redirect_store'), $storeArray);
        }
    }

   protected function _afterLoad(Mage_Core_Model_Abstract $object)
   {
        if (!$object->getIsMassDelete()) {
            $this->loadStore($object);
        }

        return parent::_afterLoad($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getIsMassStatus()) {
            $this->saveStore($object);
        }
        return parent::_afterSave($object);
    }
}