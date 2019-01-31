<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Block_Sync_Request_Grid_Answer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $value = Mage::helper('core')->escapeHtml($value);

        $status = $row->getData('status');

        $style = '';
        if ($status >= Mageshops_Rakuten_Model_Rakuten_Request::STATUS_ERROR) {
            $style = ' style="color:red"';
        }

        $js1 = 'e = $(this).childElements(\'pre\'); e[1].show()';
        $js2 = 'e = $(this).childElements(\'pre\'); e[1].hide()';

        $answer = '<div onmouseover="' . $js1 . '" onmouseout="' . $js2 . '">';
        $answer .= "<a{$style}>" . Mage::helper('rakuten')->__('Show answer...') . '</a>';
        $answer .= '<pre style="display:none; position:absolute; background:#fff; padding:4px; border:1px solid dodgerblue">' . $value . '</pre>';
        $answer .= '</div>';

        return $answer;
    }
}