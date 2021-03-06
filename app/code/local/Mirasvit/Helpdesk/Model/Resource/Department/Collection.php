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
 * @method Mirasvit_Helpdesk_Model_Department getFirstItem()
 * @method Mirasvit_Helpdesk_Model_Department getLastItem()
 * @method Mirasvit_Helpdesk_Model_Resource_Department_Collection|Mirasvit_Helpdesk_Model_Department[] addFieldToFilter
 * @method Mirasvit_Helpdesk_Model_Resource_Department_Collection|Mirasvit_Helpdesk_Model_Department[] setOrder
 */
class Mirasvit_Helpdesk_Model_Resource_Department_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/department');
    }

    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Helpdesk_Model_Department $item */
        foreach ($this as $item) {
            $arr[] = array('value' => $item->getId(), 'label' => $item->getName());
        }

        return $arr;
    }

    public function getOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = Mage::helper('helpdesk')->__('-- Please Select --');
        }
        /** @var Mirasvit_Helpdesk_Model_Department $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @param int $userId
     *
     * @return Mirasvit_Helpdesk_Model_Resource_Department_Collection|Mirasvit_Helpdesk_Model_Department[]
     */
    public function addUserFilter($userId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('helpdesk/department_user')}`
                AS `department_user_table`
                WHERE main_table.department_id = department_user_table.du_department_id
                AND department_user_table.du_user_id in (?))", array(0, $userId));

        return $this;
    }

    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('helpdesk/department_store')}`
                AS `department_store_table`
                WHERE main_table.department_id = department_store_table.ds_department_id
                AND department_store_table.ds_store_id in (?))", array(0, $storeId));

        return $this;
    }

    protected function initFields()
    {
        $select = $this->getSelect();
        $select->order(new Zend_Db_Expr('sort_order ASC'));
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }
    protected $storeId;
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    public function _afterLoad()
    {
        if ($this->storeId) {
            foreach ($this as $item) {
                $item->setStoreId($this->storeId);
            }
        }
    }

     /************************/
}
