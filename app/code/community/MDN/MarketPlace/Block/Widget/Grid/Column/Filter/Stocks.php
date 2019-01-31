<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Stocks extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range {

    /**
     * Get condition
     * 
     * @return array $array 
     */
    public function getCondition() {

        $array = array();

        $value = $this->getValue();

        if (!array_key_exists('from', $value) || $value['from'] == '')
            $value['from'] = 0;

        if (!array_key_exists('to', $value) || $value['to'] == '')
            $value['to'] = '999999999';

        $account = Mage::getModel('MarketPlace/Accounts')->load(Mage::registry('mp_country')->getmpac_account_id());
        $mp = $account->getmpa_mp();
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration($mp);

        $collection = Mage::getModel('catalog/product')->getCollection();

        if ($config->getstockAttribute())
            $collection->addAttributeToSelect($config->getstockAttribute());

        foreach ($collection as $item) {

            if ($config->getstockAttribute()) {
                $method = 'get' . $config->getstockAttribute();
                $qty = $item->$method();
            } else {
                $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item)->getQty();
            }

            if ($qty >= $value['from'] && $qty <= $value['to'])
                $array[] = $item->getId();
        }

        return (count($array) > 0) ? array('in', $array) : array('null');
    }

}
