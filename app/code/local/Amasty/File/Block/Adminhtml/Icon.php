<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Icon extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_icon';
        $this->_blockGroup = 'amfile';
        $this->_headerText = Mage::helper('amfile')->__('Icons');
        $this->_addButtonLabel = Mage::helper('amfile')->__('Add Icon');
        parent::__construct();
        

    }
}