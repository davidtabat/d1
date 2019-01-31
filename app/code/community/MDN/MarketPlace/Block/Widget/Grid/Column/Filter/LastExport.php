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
class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_LastExport extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {
    
    /**
     * get options
     * 
     * @return array $retour 
     */
    protected function _getOptions()
    {
    	$retour = array();
    	$retour[] = array('label' => '', 'value' => '');
    	$retour[] = array('label' => 'Yes', 'value' => '1');
        $retour[] = array('label' => 'No', 'value' => '0');
        return $retour;
    }

    /**
     * get condition
     * 
     * @return array 
     */
    public function getCondition()
    {
        
        $array = array();
        
        $collection = Mage::getModel('MarketPlace/Data')->getCollection()
                                ->addFieldToFilter('mp_marketplace_id', Mage::registry('mp_country')->getId())
                                ->addFieldToFilter('mp_marketplace_status', array('in' => 'created'));
        
        if($this->getValue()){                        
            
            foreach($collection as $item){
                
                $product = Mage::getModel('catalog/product')->load($item->getmp_product_id());
                $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
                
                if($qty != $item->getmp_last_stock_sent())
                    $array[] = $item->getmp_product_id();
                
            }
            
        }else{
            
            foreach($collection as $item){
                
                $product = Mage::getModel('catalog/product')->load($item->getmp_product_id());
                $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
                
                if($qty == $item->getmp_last_stock_sent())
                    $array[] = $item->getmp_product_id();
                
            }
            
            
        }
        
        return array('in' => $array);
        
    }
    
}
