<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_Adminhtml_Import_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('import_form');
		$this->setTitle(Mage::helper('amfile')->__('Import'));
	}

	protected function _beforeToHtml()
	{
		$tabs = array(
			'import'		=> 'Import CSV',
			'list'			=> 'Uploaded files',
			'upload'		=> 'Upload Files',
		);

		foreach ($tabs as $code => $label){
			$label = Mage::helper('amfile')->__($label);
			$content = $this->getLayout()->createBlock('amfile/adminhtml_import_tab_' . $code)
				->setTitle($label)
				->toHtml();

			$this->addTab($code, array(
				'label'     => $label,
				'content'   => $content,
			));
		}

		return parent::_beforeToHtml();
	}
}