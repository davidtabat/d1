<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_FtpFile extends Varien_Object
{

	protected function _getFtpPath()
	{
		return Mage::helper('amfile')->getFtpImportDir();
	}

	public function getId(){
		return $this->getName();
	}

	public function getPath($filename = null)
	{
		if(is_null($filename)) {
			$filename = $this->getName();
		}
		if(!$filename) {
			return null;
		}
		return $this->_getFtpPath().$filename;
	}

	public function load($filename)
	{
		if(is_file($this->_getFtpPath().$filename)) {
			$this->addData(array(
				'name' => $filename,
				'path' => $this->_getFtpPath(),
			));
		}

		return $this;
	}

	public function delete()
	{
		if(is_file($this->getPath())) {
			unlink($this->getPath());
		}
		return $this;
	}

	public function getCollection()
	{
		return Mage::getModel('amfile/resource_ftpFile_collection');
	}

	public function exists($filename = null)
	{
		if(is_null($filename)) {
			$filename = $this->getName();
		}

		$filename = $this->_getFtpPath().$filename;
		return is_file($filename);
	}

	public function upload($fileField = 'file')
	{
		$uploader = new Varien_File_Uploader($fileField);
		$uploader->setAllowRenameFiles(true);
		$result = $uploader->save($this->_getFtpPath());
		Mage::log($this->_getFtpPath(), null, "asdasdasd.txt", true);
		if(!$result) {
			Mage::throwException(Mage::helper('amfile')->__('Error occurred save file'));
		}
		$this->setName($uploader->getUploadedFileName());

		return $this;
	}


	public function newFileName($fileName)
	{
		$i = 0;
		$newFileName = $fileName;

		while (file_exists($this->getPath($newFileName)))
			$newFileName = "(" . ++$i . ")" . $fileName;

		return $newFileName;
	}
}