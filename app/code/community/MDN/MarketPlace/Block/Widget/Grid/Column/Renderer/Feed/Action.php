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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Feed_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    /**
     * render
     * 
     * @param Varien_Object $row
     * @return string 
     */
    public function render(Varien_Object $row){
        
        $html = '';        
            
        $html .= '<a href="'.Mage::Helper('adminhtml')->getUrl('MarketPlace/Feed/downloadResult', array('type' => $row->getmp_type(), 'country' => $row->getmp_country(), 'feed_id'=>$row->getmp_feed_id())).'">'.$this->__('Download result').'</a>';                   
        $html .= '<br><a href="'.Mage::Helper('adminhtml')->getUrl('MarketPlace/Feed/delete', array('type' => $row->getmp_type(), 'country' => $row->getmp_country(), 'feed_id'=>$row->getId())).'">'.$this->__('Delete').'</a>';

        return $html;
        
    }
    
}
