<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Block_Adminhtml_Renderer_Attachments extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{

    protected $delimiter = '<br/>';

    protected $_maxDisplay = 3;

    public function render(Varien_Object $row)
    {

        $productId = $row->getData('entity_id');
        $storeId = Mage::app()->getStore()->getId();

        $files = Mage::getResourceModel('amfile/file_collection')->getFilesAdmin($productId, $storeId);

        $this->setData('files', $files);
        $this->setData('product_id', $productId);

        $html = $this->setTemplate('amfile/attached_files.phtml')->toHtml();
        return $html;
    }
}