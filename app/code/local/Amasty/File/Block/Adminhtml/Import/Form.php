<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id'     => 'import_form',
			'action' => $this->getUrl('*/*/import'),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));

		$form->setUseContainer(true);
		$this->setForm($form);

		return parent::_prepareForm();

	}
}