<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Model_Backend_Source_Customergroup
{
    protected static $_options = array();

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!empty(self::$_options)) {
            return self::$_options;
        }

        self::$_options[-1]['label'] = 'For All Users';
        self::$_options[-1]['value'] = '-1';

        $groups = Mage::getModel('customer/group')->getCollection();
        foreach ($groups as $group) {
            self::$_options[] = array(
                'label' => $group->getCustomerGroupCode(),
                'value' => $group->getId(),
            );
        }

        return self::$_options;
    }

}