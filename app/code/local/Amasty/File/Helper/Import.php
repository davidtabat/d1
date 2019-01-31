<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Helper_Import extends Mage_Core_Helper_Abstract
{
	/**
	 * @return array
	 */
	public function getFieldsForImport()
	{
		return array(
			"file_name" 	=> $this->__("File Name"),
			"product_sku" 	=> $this->__("Product SKU"),
			"file_title" 	=> $this->__("File Title"),
			"sort_order" 	=> $this->__("Sort Order"),
		);
	}

	/**
	 * @return array
	 */
	public function getRequiredFieldsForImport()
	{
		return array("file_name","product_sku" );
	}
}