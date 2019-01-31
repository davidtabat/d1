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
class MDN_MarketPlace_Block_CheckCron extends Mage_Adminhtml_Block_Widget_Form {
    
    /**
     * To HTML
     * 
     * @return string 
     * @todo : verification du nombre de tâches en statut running concernant marketplace
     */
    protected function _toHtml() {
        
        $retour = '';
        $html = '<div class="notification-global"> ';
        $html .= '<font color=red><b>Caution !! It seems that cron is not working on your server, MarketPlaces extensions require cron to work properly.</b></font>';
        $html .= ' (<a target="_blanck" href="http://www.boostmyshop.com/docs/controller.php?action=index&autoSelectPath=%2Fdocs%2FAmazon%2F7+-+FAQ%2F">View FAQ</a>)';
        $html .= '</div>'; 

        //get the latest cron execution date
        $sql = "select max(executed_at) from " . Mage::getConfig()->getTablePrefix() . "cron_schedule";
        $lastExecutionTime = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);

        //if return empty, check if there are records in table
        if ($lastExecutionTime == '') {
            $sql = "select count(*) from " . Mage::getConfig()->getTablePrefix() . "cron_schedule";
            $count = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
 
            if ($count == 0) {                
                $retour = $html;
            }
        }
        
        $timeStamp = strtotime($lastExecutionTime);
        if ((time() - $timeStamp) > 3600) {
            $retour = $html;
        }
        
        return $retour;
    }
    
}
