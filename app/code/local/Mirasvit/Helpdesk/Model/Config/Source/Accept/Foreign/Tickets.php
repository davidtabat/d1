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



class Mirasvit_Helpdesk_Model_Config_Source_Accept_Foreign_Tickets
{
    public function toArray()
    {
        return array(
            Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_DISABLE => Mage::helper('helpdesk')->__('Disabled'),
            Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_AW => Mage::helper('helpdesk')->__('AW Helpdesk'),
            Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_MW => Mage::helper('helpdesk')->__('MW Helpdesk'),
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
