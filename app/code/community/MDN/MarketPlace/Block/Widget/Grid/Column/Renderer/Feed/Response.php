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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Feed_Response extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render
     * 
     * @param Varien_Object $row
     * @return string $html 
     */
    public function render(Varien_Object $row){

        $html = "";

        $response = $row->getmp_response();
        if($response !== NULL && $response !== ""){
            $html .= '<a href="'.mage::helper('adminhtml')->getUrl('MarketPlace/Feed/downloadFeed', array('id'=>$row->getmp_id(), 'type'=>'response')).'">'.$this->__('Download').'</a>';
        }else{
            $html .= $this->__('Unavailable');
        }

        return $html;

    }

}
