<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Block_Adminhtml_Renderer_File extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     * @return string html element
     */
    public function render(Varien_Object $row, $fileId = -1)
    {

        $block = new Mage_Core_Block_Template();

        $html = sprintf('<input type="hidden" name="amfile_product" value="%s" />', $row->getId());
        $html .= sprintf('<input type="hidden" name="amfile_store" value="%s"/>', Mage::app()->getRequest()->getParam('store', 0));
        $html .= sprintf('<input type="hidden" name="amfile_ajax_action" value="%s" />', $this->getUrl('adminhtml/amfile_file/updateGrid'));
        $html .= sprintf('<input type="file" title="File" name="files[%d][file]" />', $fileId);
        $html .= sprintf('<input type="hidden" name="files[%d][use]" value="file" />', $fileId);
        $html .= sprintf('<input type="hidden" name="files[%d][file_name]" />', $fileId);
        $html .= $block->setTemplate('amfile/drag_and_drop_el.phtml')->toHtml();

        return $html;

    }
}