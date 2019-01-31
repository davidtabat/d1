<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import_Tab_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_import_tab_list';
		$this->_blockGroup = 'amfile';
		$this->_headerText = '';
		parent::__construct();
	}

	public function getButtonsHtml($area = null)
	{
		$this->removeButton('add');
		parent::getButtonsHtml($area);
	}
}