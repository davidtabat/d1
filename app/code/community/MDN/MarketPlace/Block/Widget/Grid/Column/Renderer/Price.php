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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render
     * 
     * @param Varien_Object $row
     * @return string $html
     */
    public function render(Varien_Object $row) {

        $html = "";

        $price = mage::helper('MarketPlace/Product')->getPrice($row);

        $price = str_replace(',', '.', $price);

        $color = ($price == $row->getprice()) ? 'black' : 'blue';

        $currency = mage::getStoreConfig('currency/options/base');

        $special = ($price == $row->getPrice()) ? false : true;

        $price = round($price, 2);

        if(!$special){
            $html = $price.' '.$currency;
        }
        else{
            $rowprice = round($row->getprice(), 2);
            $html = $rowprice.' '.$currency.'<br/><span style="color:'.$color.'"> ('.$price.' '.$currency.')</span>';
        }

        return $html;
    }

    /**
     * Render export
     * 
     * @param Varien_Object $row
     * @return float 
     */
    public function renderExport(Varien_Object $row) {

        return mage::helper('MarketPlace/Product')->getPrice($row);

    }

}

