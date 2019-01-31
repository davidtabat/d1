<?php
/* 
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

class MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_FreeShipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render 
     * 
     * @param Varien_Object $row
     * @return string $html
     */
    public function render(Varien_Object $row)
    {
    	$name='data['.$row->getId().'][mp_free_shipping]';

        $html = '<select name="'.$name.'" id="'.$name.'">';
        if($row->getmp_free_shipping() == 1){
            $html .= '<option value="0">No</option>';
            $html .= '<option value="1" selected >Yes</option>';
        }
        else{
            $html .= '<option value="0" selected >No</option>';
            $html .= '<option value="1">Yes</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Render export
     * 
     * @param Varien_Object $row
     * @return int 
     */
    public function renderExport(Varien_Object $row)
    {
    	return $row->getmp_free_shipping();
    }
}
