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
 * @method Mirasvit_Helpdesk_Model_Template getFirstItem()
 * @method Mirasvit_Helpdesk_Model_Template getLastItem()
 * @method Mirasvit_Helpdesk_Model_Resource_Template_Collection|Mirasvit_Helpdesk_Model_Template[] addFieldToFilter
 * @method Mirasvit_Helpdesk_Model_Resource_Template_Collection|Mirasvit_Helpdesk_Model_Template[] setOrder
 */
class Mirasvit_Helpdesk_Model_Resource_Template_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/template');
    }

    public function toOptionGroupArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        foreach ($this as $item) {
            /* @var $item Mirasvit_Helpdesk_Model_Template */
            $item->afterLoad();
            foreach ($item->getStoreIds() as $storeId) {
                if (!isset($arr[$storeId])) {
                    $store = Mage::getModel('core/store')->load($storeId);
                    $arr[$storeId]['value'] = array();
                    $arr[$storeId]['label'] = $store->getName();
                }
                $arr[$storeId]['value'][] = array('value' => $item->getId(), 'label' => $item->getName());
            }
        }

        return $arr;
    }

    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Helpdesk_Model_Template $item */
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
        /** @var Mirasvit_Helpdesk_Model_Template $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('helpdesk/template_store')}`
                AS `template_store_table`
                WHERE main_table.template_id = template_store_table.ts_template_id
                AND template_store_table.ts_store_id in (?))", array(0, $storeId));

        return $this;
    }

    protected function initFields()
    {
        $select = $this->getSelect();
        // $select->columns(array('is_replied' => new Zend_Db_Expr("answer <> ''")));
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/
}
