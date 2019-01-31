<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import_Tab_Import extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->setUseContainer(false);

		$fieldset = $form->addFieldset('general', array(
			'htmlId'	=> 'general_information',
			'legend'	=> Mage::helper('amfile')->__('Import CSV'),
		));

		$text = "<p class='note'>
		CSV file fields: filename, product-sku [, title(Optional) [, Sort Order (optional) ] ]</p>
		";

		$fieldset->addField('csv_file', 'file',
			array(
				'label' => Mage::helper('amfile')->__('Upload CSV File'),
				'required'=>true,
				//'value' => $_event->getEventTitle(),
				'name' => 'csv_file',
				'after_element_html' => $text
			)
		);

		$this->setForm($form);

		return parent::_prepareForm();
	}
}