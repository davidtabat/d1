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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Margin extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render
     * 
     * @param Varien_Object $row
     * @return string $html
     */
    public function render(Varien_Object $row) {

        $margin = 0;
        $margin_without_special = 0;
        $html = "";
        $content = "";

        $price = mage::helper('MarketPlace/Product')->getPrice($row);
        $price = str_replace(',', '.', $price);
        $price_without_special = $row->getprice();

        $cost = $row->getcost();

        $color = ($row->getmp_force_export() == 1) ? 'red' : 'orange';
        
        if($price != 0 && $cost != 0){

            // no special price
            if($price == $row->getprice()){

                $margin = round(($price - $cost) / $price * 100, 2);
                $style = ($margin < Mage::getStoreConfig('marketplace/'.strtolower($row->getmp_marketplace_id()).'/margin_min')) ? 'style="background-color:'.$color.';"' : '';
                $html = '<span '.$style.'>'.$margin.' %</span>';

            } else {

                // with special price
                $margin_without_special = round(($price_without_special - $cost) / $price_without_special * 100, 2);
                $margin = round(($price - $cost) / $price * 100, 2);
                $style = ($margin < Mage::getStoreConfig('marketplace/'.strtolower($row->getmp_marketplace_id()).'/margin_min')) ? 'style="background-color:'.$color.';"' : 'style="color:blue"';
                $html = $margin_without_special.' % <br/><span '.$style.'>('.$margin.' %)</span>';

            }
            

        }
        else{
            $html = '0 %';
       }

       $checked = ($row->getmp_force_export() == 1) ? 'checked' : '';
       $html .= '<div><input '.$checked.' type="checkbox" name="data['.$row->getId().'][mp_force_export]" id="force_export_'.$row->getId().'"/> <label for="force_export_'.$row->getId().'"><i>force export</i></label></div>';
        
        return $html;
    }

    /**
     * Render export
     * 
     * @param Varien_Object $row
     * @return float $margin 
     */
    public function renderExport(Varien_Object $row) {

        $price = mage::helper('MarketPlace/Product')->getPrice($row);
        $cost = $row->getcost();

        if($price != 0 && $cost != 0){

            $margin = round(($price - $cost) / $price * 100, 2);

        }
        else
            $margin = 0;

        return $margin;

    }

}
