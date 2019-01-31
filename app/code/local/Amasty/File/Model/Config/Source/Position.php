<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Model_Config_Source_Position
{
    const POSITION_BEFORE = 0;
    const POSITION_AFTER = 1;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amfile');
        $vals = array(
            self::POSITION_BEFORE   => $hlp->__('Before sibling'),
            self::POSITION_AFTER  => $hlp->__('After sibling'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                'value' => $k,
                'label' => $v
            );

        return $options;
    }
}
