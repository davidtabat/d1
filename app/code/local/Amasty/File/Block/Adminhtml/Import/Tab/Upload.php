<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import_Tab_Upload extends Mage_Adminhtml_Block_Widget//Mage_Adminhtml_Block_Widget_Form
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('amfile/import/add_files.phtml');
	}


	public function getFieldsForImport()
	{
		return Mage::helper("amfile/import")->getFieldsForImport();
	}

	public function getRequiredFieldsForImport()
	{
		return Mage::helper("amfile/import")->getRequiredFieldsForImport();
	}


	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->setUseContainer(false);


		$fieldset = $form->addFieldset('upload_ftp', array(
			'htmlId'	=> 'upload_ftp',
			'legend'	=> $this->__('Upload Files By FTP'),
		));


		$block = new Mage_Core_Block_Template();

		$html = sprintf('<input type="hidden" name="amfile_product" value="%s" />', 0);
		$html .= sprintf('<input type="hidden" name="amfile_store" value="%s"/>', Mage::app()->getRequest()->getParam('store', 0));
		$html .= sprintf('<input type="hidden" name="amfile_ajax_action" value="%s" />', $this->getUrl('adminhtml/amfile_file/updateGrid'));
		$html .= sprintf('<input type="file" title="File" name="files[%d][file]" />', 0);
		$html .= sprintf('<input type="hidden" name="files[%d][use]" value="file" />', 0);
		$html .= sprintf('<input type="hidden" name="files[%d][file_name]" />', 0);
		$html .= $block->setTemplate('amfile/drag_and_drop_el.phtml')->toHtml();


		$fieldset = $form->addFieldset('upload', array(
			'htmlId'	=> 'upload',
			'legend'	=> Mage::helper('amfile')->__('Upload Files'),
		));

		$fieldset->addField('csv_file', 'text',
			array(
				//'label' => Mage::helper('amfile')->__('Upload CSV File'),
				'required'=>true,
				//'value' => $_event->getEventTitle(),
				//'name' => 'csv_file',
				'after_element_html' => $html
			)
		);



		/*$text = "<p class='note'>
		CSV file fields: filename,product-sku[,title(Optional)]</p>
		";

		$fieldset->addField('csv_file', 'file',
			array(
				'label' => Mage::helper('amfile')->__('Upload CSV File'),
				'required'=>true,
				//'value' => $_event->getEventTitle(),
				'name' => 'csv_file',
				'after_element_html' => $text
			)
		);*/

		$this->setForm($form);

		return parent::_prepareForm();
	}
}