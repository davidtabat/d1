<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Model_Config_Source_Location
{
    const LOCATION_TEXT_LIST = 0;
    const LOCATION_MAGENTO_TABS = 1;
    const LOCATION_PRODUCT_INFO = 2;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amfile');
        $vals = array(
            self::LOCATION_TEXT_LIST   => $hlp->__('Insert into any layout block'),
            self::LOCATION_MAGENTO_TABS  => $hlp->__('New tab in product.info.tabs block'),
            self::LOCATION_PRODUCT_INFO  => $hlp->__('New group in product.info block'),
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
