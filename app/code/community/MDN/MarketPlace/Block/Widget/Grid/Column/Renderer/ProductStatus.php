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
class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_ProductStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Render
     * 
     * @param Varien_Object $row
     * @return string $html
     */
    public function render(Varien_Object $row) {

        /*$status = $row->getmp_marketplace_status();

        $html = "";

        switch($status){

            case MDN_MarketPlace_Helper_ProductCreation::kStatusInError:
                //onclick="showMpMessage(\'mp_message_'.$row->getmp_id().'\');return false;"
                $html = '<span style="color:red;">'.$this->__('error').'</span>';
                $html .= '<div><img src="'.$this->getSkinUrl('images/Marketplace/comments.gif').'" alt="infos" title="'.$row->getmp_message().'" /></div>';
                //$html .= '<div class="mp_message" id="mp_message_'.$row->getmp_id().'">'.$row->getmp_message().'</div>';
                break;

            case MDN_MarketPlace_Helper_ProductCreation::kStatusIncomplete:
                $html = '<span style="color:orange;">'.$this->__('incomplete').'</span>';
                break;

            case MDN_MarketPlace_Helper_ProductCreation::kStatusCreated:
                $html = '<span style="color:green;">'.$this->__('created').'</span>';
                break;

            case MDN_MarketPlace_Helper_ProductCreation::kStatusPending:
                $html = '<span style="color:orange;">'.$this->__('pending').'</span>';
                break;
        
            case MDN_MarketPlace_Helper_ProductCreation::kStatusActionRequired:
                $html = '<span style="color:orange;">'.$this->__('action required').'</span>';
                break;
            
            case MDN_MarketPlace_Helper_ProductCreation::kStatusNotCreated:
            default:
                $html = '<span style="color:black;">'.$this->__('notCreated').'</span>';
                break;
        }

        return $html;*/
    }

    /**
     * Render export
     * 
     * @param Varien_Object $row
     * @return string 
     */
    public function renderExport(Varien_Object $row) {
        return $row->getmp_marketplace_status();
    }

}

