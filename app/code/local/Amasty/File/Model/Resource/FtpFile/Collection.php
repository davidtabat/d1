<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_Resource_FtpFile_Collection extends Varien_Data_Collection
{

	public function __construct() {
		$this->setItemObjectClass('amfile/ftpFile');
		parent::__construct();
	}


	/**
	 * @param $field
	 * @param $condition
	 *
	 * @return $this
	 */
	public function addFieldToFilter($field, $condition)
	{
		if(is_array($condition)) {
			if(isset($condition['in'])) {
				$values = $condition['in'];
			} elseif(isset($condition['eq'])) {
				$values = array($condition['eq']);
			} elseif(isset($condition['like'])) {
				//var_dump($condition);
				//new Zend_Db_Expr()
				$values = $condition['like']->__toString();
				//var_dump($values);
				$values = mb_substr($values, 2);
				$values = mb_substr($values, 0, -2);
				//var_dump($condition['like']->__toString());
				//var_dump($values);
			}

		} else {
			$values = array($condition);
		}
		$this->addFilter($field, $values);

		return $this;
	}


	public function loadData($printQuery = false, $logQuery = false)
	{


		if($this->isLoaded()){
			return $this;
		}
		$dir = $this->_getFtpDir();
		// Открыть известный каталог и начать считывать его содержимое
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if(!is_file($dir.$file)) {
						continue;
					}
					if($file == '.htaccess') {
						continue;
					}
					//echo "файл: $file : тип: " . filetype($dir . $file) . "\n";
					$data = array(
						'name' => $file,
						'path' => $dir."/".$file,
					);

					$filterName = $this->getFilter('name');
					//var_dump($filterName);
					if(!empty($filterName['value'])) {
						if(is_array($filterName['value'])) {
							if(!in_array($data['name'], $filterName['value'])) {
								continue;
							}
						} elseif(is_string($filterName['value']) && strpos(mb_strtolower($data['name']), mb_strtolower($filterName['value'])) === false) {
							continue;
						}
					}

					$item = $this->getNewEmptyItem();
					//var_dump(get_class($item));
					$item->addData($data);
					$this->addItem($item);
				}
				closedir($dh);
			}
		}

		$this->_setIsLoaded();

		return $this;
	}


	protected function _getFtpDir()
	{
		return Mage::helper('amfile')->getFtpImportDir();
	}

}