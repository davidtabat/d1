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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_LastExport extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    /**
     * Renderer
     * 
     * @param Varien_Object $row
     * @return string $html
     */
    public function render(Varien_Object $row){
        
        $html = '';
        
        $account = Mage::getModel('MarketPlace/Accounts')->load(Mage::registry('mp_country')->getmpac_account_id());
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration($account->getmpa_mp());
        if($config->getstockAttribute()){
            $method = 'get'.$config->getstockAttribute();
            $qty = $row->$method();
        }else{
            $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($row)->getQty();
        }
        
        $style = ($qty != $row->getmp_last_stock_sent()) ? 'style="color:red;"' : '';
        
        $html .= '<ul style="list-style-type:none;">';
        $html .= '<li '.$style.'>'.$this->__('Stock').' : '.$row->getmp_last_stock_sent().'</li>';
        $html .= '<li>'.$this->__('Price').' : '.$row->getmp_last_price_sent().'</li>';
        $html .= '<li>'.$this->__('Delais').' : '.$row->getmp_last_delay_sent().'</li>';
        $html .= '</ul>';
        
        return $html;
        
    }
    
}
