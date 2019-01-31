<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_Mysql4_Import extends Mage_Core_Model_Mysql4_Abstract
{
	protected $_columns;
	protected $_importErrors = array();

	/**
	 * Resource initialization
	 */
	protected function _construct()
	{
		$this->_init('amfile/file', 'file_id');
	}


	public function uploadAndImport()
	{
		if (empty($_FILES['csv_file']['tmp_name'])) {
			Mage::throwException(Mage::helper('amfile')->__('Please, upload CSV file!'));
		}

		$this->_importErrors = array();

		$csvFile = $_FILES['csv_file']['tmp_name'];
		$info   = pathinfo($csvFile);
		if(strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION)) != 'csv') {
			Mage::throwException(Mage::helper('amfile')->__('Invalid File Extension. Please, upload CSV file!'));
		}

		$handle = fopen($_FILES['csv_file']['tmp_name'], "r");

		if($handle === false) {
			Mage::throwException(Mage::helper('amfile')->__('Upload File Error'));
		}


		$delimiter = ";";

		// check and skip headers
		$headers = $this->_readCSVLine($handle, $delimiter);
		if ($headers === false || count($headers) < 2) {
			rewind($handle);
			$delimiter=",";
			$headers = $this->_readCSVLine($handle, $delimiter);
			if ($headers === false || count($headers) < 2) {
				Mage::throwException(Mage::helper('amfile')->__('Invalid File Format'));
			}

		}

		$this->_columns = $headers;

		$adapter = $this->_getWriteAdapter();
		$adapter->beginTransaction();

		$rowNumber  = 2;
		$listFiles 	= array();
		$isErrors 	= false;
		try {
			while (false !== ($csvLine = $this->_readCSVLine($handle, $delimiter))) {
				if (empty($csvLine)) {
					continue;
				}

				$row = $this->validateCSVRow($csvLine, $rowNumber);
				if($row === false) {
					$isErrors = true;
				}

				// IF $isErrors == true - checked all rows in csv, yet does not uploading files
				if ($row !== false && !$isErrors) {
					$ftpFile = Mage::getModel('amfile/ftpFile')->load($row['file_name']);
					//$file->setProductId($row['product_id']);
					//$file->setTitle
					$file = Mage::getModel('amfile/file');
					$file->saveFile($row['file_name'], $ftpFile->getPath(), false);
					//$file->save();

					//Mage::helper('amfile/integration')->assignFile($row['product_id'], $file->getFileUrl(), $row['file_title'], $row['position']);
					$file->addData(array(
							'product_id' => $row['product_id'],
							'file_name' => $row['file_name'],
						))
						->save();

					Mage::getModel('amfile/store')
						->setData(array(
							'file_id' => $file->getId(),
							'visible' => 1,
							'label' => $row['file_title'],
							'position' => $row['position']
						))
						->save();

					$listFiles[] = $file->getFullName();
				}

				$rowNumber++;
			}

			if($isErrors) {
				Mage::throwException(Mage::helper('amfile')->__('An error occurred while import Files.'));
			}

			$adapter->commit();

			fclose($handle);

		} catch (Mage_Core_Exception $e) {
			$adapter->rollback();
			fclose($handle);
			$this->_deleteFiles($listFiles);
			Mage::throwException($e->getMessage());
		} catch (Exception $e) {
			$adapter->rollback();
			fclose($handle);
			$this->_deleteFiles($listFiles);
			Mage::logException($e);
			Mage::throwException(Mage::helper('amfile')->__('An error occurred while import Files.') . $e->getMessage());
		}
	}



	public function getImportErrors()
	{
		return $this->_importErrors;
	}


	public function validateCSVRow($line, $rowNumber)
	{
		if (count($this->_columns) != count($line)) {
			$this->_importErrors[] = Mage::helper('amfile')->__('Row %d has incorrect columns count.', $rowNumber);
			return false;
		}

		foreach($line as &$item) {
			$item = trim($item);
		}

		$row = array(
			'file_name' => $line[0],
			'product_id' => null,
			'file_title' => isset($line[2]) ? iconv("CP1251", "UTF-8//IGNORE", $line[2]) : null,
			'position' => isset($line[3]) ? $line[3] : 0,
		);

		if(!Mage::getModel('amfile/ftpFile')->exists($row['file_name'])) {
			$this->_importErrors[] = Mage::helper('amfile')->__('Row %d has incorrect filename. File does not exists', $rowNumber);
			return false;
		}


		$row['product_id'] = Mage::getModel('catalog/product')->getIdBySku($line[1]);
		if(empty($row['product_id'])) {
			$this->_importErrors[] = Mage::helper('amfile')->__('Row %d has incorrect product SKU. Product does not exists', $rowNumber);
			return false;
		}

		return $row;
	}


	protected function _deleteFiles($files)
	{
		foreach($files as $filePath) {
			unlink($filePath);
		}
	}


	protected function _readCSVLine($resource, $delimiter)
	{
		$line = fgets($resource);
		return ($line === false) ? $line : explode($delimiter, $line);
	}

}