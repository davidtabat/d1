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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Stocks extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render
     * 
     * @param Varien_Object $row
     * @return type 
     * @todo : find another method (trop lourd)
     */
    public function render(Varien_Object $row) {

        $account = Mage::getModel('MarketPlace/Accounts')->load(Mage::registry('mp_country')->getmpac_account_id());
        $config = Mage::getModel('MarketPlace/Configuration')->getConfiguration($account->getmpa_mp());
        if($config->getstockAttribute()){
            $method = 'get'.$config->getstockAttribute();
            $qty = $row->$method();
        }else{
            $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($row)->getQty();
        }
        return number_format($qty);
        
    }

    /**
     * Render export
     *  
     * @param Varien_Object $row
     * @return int 
     */
    public function renderExport(Varien_Object $row) {

        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($row)->getQty();
        return number_format($qty);

    }

}
