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
 * @method Mirasvit_Helpdesk_Model_Resource_Priority_Collection|Mirasvit_Helpdesk_Model_Priority[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Priority load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Priority setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Priority setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Priority getResource()
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $param)
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $param)
 */
class Mirasvit_Helpdesk_Model_Priority extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/priority');
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

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        return parent::addData($data);
    }
    /************************/

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * prepare collection for dropdowns.
     *
     * @param int|Mage_Core_Model_Store $store
     *
     * @return Mirasvit_Helpdesk_Model_Resource_Priority_Collection|Mirasvit_Helpdesk_Model_Priority[]
     */
    public function getPreparedCollection($store)
    {
        if (is_object($store)) {
            $store = $store->getStoreId();
        }

        return $this->getCollection()
            ->addStoreFilter($store)
            ->setOrder('sort_order', 'asc');
    }
}
