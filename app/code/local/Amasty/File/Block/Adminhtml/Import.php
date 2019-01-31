<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'code_set_id';
		$this->_blockGroup = 'amfile';
		$this->_controller = 'adminhtml_import';
		$this->_mode = "";

		//$this->_buttons = array();
		$this->_headerText = $this->__('Mass File Import');

	}


	protected function _prepareLayout()
	{
		$this->removeButton('back');
		$this->_addButton('save', array(
			'label'     => Mage::helper('adminhtml')->__('Save'),
			'onclick'   => 'import_form.submit();$(this).addClassName(\'disabled\');$(this).setAttribute(\'disabled\',\'disabled\')',
			'class'     => 'save',
		), 1);
		if ($this->_blockGroup && $this->_controller) {
			$this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_form'));
		}
		return parent::_prepareLayout();
	}
}