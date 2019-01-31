<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Adminhtml_Amfile_ImportController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$dir = Mage::helper('amfile')->getFtpImportDir();
		if(!is_dir($dir)) {
			$url = $this->getUrl('adminhtml/system_config/edit/section/amfile');
			Mage::getSingleton('adminhtml/session')->addError('Ftp import directory incorrect! Please check it <a href="'.$url.'" target="_blank">Settings</a>');
		}

		$this->loadLayout();
		$this->_title($this->__('Mass File Import'));
		$this->_setActiveMenu('catalog/amfile');
		$this->_addContent(
			$this->getLayout()->createBlock('amfile/adminhtml_import')
		);

		$this->_addLeft($this->getLayout()->createBlock('amfile/adminhtml_import_tabs'));

		$this->renderLayout();
	}

	public function importAction()
	{
		$importModel = Mage::getResourceModel('amfile/import');
		try {
			$importModel->uploadAndImport();
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('All Files successfully imported'));
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			if($importErrors = $importModel->getImportErrors()) {
				$countErrors = 0;
				foreach($importErrors as $error){
					$countErrors++;
					Mage::getSingleton('adminhtml/session')->addError($error);
					if($countErrors > 10) {
						break;
					}
				}
			}
		}

		$this->_redirect('*/*/index');
	}

	public function exportFilesCsvAction()
	{
		$fileName   = 'amasty_files_list.csv';
		$content    = $this->getLayout()->createBlock('amfile/adminhtml_import_tab_list_grid')
			->getCsvFile();

		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exampleFileCsvAction()
	{
		$fileName   = 'amasty_files_list_example.csv';
		$fields = Mage::helper("amfile/import")->getFieldsForImport();

		$this->_prepareDownloadResponse($fileName, implode(",", $fields));
	}

	public function gridListFilesAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('amfile/adminhtml_import_tab_list_grid')->toHtml()
		);
	}

	public function deleteFileAction()
	{
		$deleteId = $this->getRequest()->getParam('name');
		try {
			$file = Mage::getModel('amfile/ftpFile')->load($deleteId)->delete();
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*/index');
	}

	public function massDeleteFilesAction()
	{
		$deleteIds = $this->getRequest()->getParam('file_names');
		if(!is_array($deleteIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfile')->__('Please select file(s).'));
		} else {
			try {
				$collection = Mage::getModel('amfile/ftpFile')->getCollection()->addFieldToFilter("name", array('in'=>$deleteIds));
				foreach($collection as $item) {
					//var_dump($item);

					$item->delete();
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

		}
		$this->_redirect('*/*/index');
	}

	public function uploadAction()
	{
		$errors = array();
		$content = '';
		try {
			$file = Mage::getModel('amfile/ftpFile')->upload('file');
			$content = $this->__('File %s successfully uploaded', $file->getName());
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$this->getResponse()->setBody(
			Mage::helper('core')->jsonEncode(
				array(
					'errors' => $errors,
					'content' => $content,
				)
			)
		);
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed(
			'catalog/amfile/amfile_import'
		);
	}
}