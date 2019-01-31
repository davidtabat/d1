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
 * @method Mirasvit_Helpdesk_Model_Priority getFirstItem()
 * @method Mirasvit_Helpdesk_Model_Priority getLastItem()
 * @method Mirasvit_Helpdesk_Model_Resource_Priority_Collection|Mirasvit_Helpdesk_Model_Priority[] addFieldToFilter
 * @method Mirasvit_Helpdesk_Model_Resource_Priority_Collection|Mirasvit_Helpdesk_Model_Priority[] setOrder
 */
class Mirasvit_Helpdesk_Model_Resource_Priority_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/priority');
    }

    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Helpdesk_Model_Priority $item */
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
        /** @var Mirasvit_Helpdesk_Model_Priority $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('helpdesk/priority_store')}`
                AS `priority_store_table`
                WHERE main_table.priority_id = priority_store_table.ps_priority_id
                AND priority_store_table.ps_store_id in (?))", array(0, $storeId));

        return $this;
    }

    protected function initFields()
    {
        $select = $this->getSelect();
        $select->order(new Zend_Db_Expr('sort_order ASC'));
        // $select->columns(array('is_replied' => new Zend_Db_Expr("answer <> ''")));
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
