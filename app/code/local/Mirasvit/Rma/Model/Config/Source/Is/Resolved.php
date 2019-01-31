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
 * @package   RMA
 * @version   2.0.7
 * @build     1267
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Config_Source_Is_Resolved
{
    public function toArray()
    {
        return array(
            Mirasvit_Rma_Model_Config::IS_RESOLVED_0 => Mage::helper('rma')->__('-- Please Select --'),
            Mirasvit_Rma_Model_Config::IS_RESOLVED_1 => Mage::helper('rma')->__('Mark as unresolved'),
            Mirasvit_Rma_Model_Config::IS_RESOLVED_2 => Mage::helper('rma')->__('Mark as resolved'),
        );
    }
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }

    /************************/
}
