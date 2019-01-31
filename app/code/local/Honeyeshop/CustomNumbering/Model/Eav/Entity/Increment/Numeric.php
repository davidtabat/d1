<?php
/**
 * @copyright Copyright (c) 2011 Honeyeshop.com by Adbee 
 */
class Honeyeshop_CustomNumbering_Model_Eav_Entity_Increment_Numeric extends Mage_Eav_Model_Entity_Increment_Numeric
{	
	var $eavValues = array(
		'order' => 4,
		'invoice' => 18,
		'shipment' => 24,
		'creditmemo' => 28
	);
	
	private function setCurrentIds() {
		$db = Mage::getSingleton('core/resource')->getConnection('core_read');		
		$tableName = (string) Mage::getConfig()->getTablePrefix() . 'eav_entity_type';
		$select = $db->select()
			->from($tableName)
			->where("entity_type_code IN ('order', 'invoice', 'shipment', 'creditmemo')");
		
		$eavData = $db->fetchAll($select);
		
		foreach ($eavData as $val) {
			switch($val['entity_type_code']) {
				case 'order':
					$this->eavValues['order'] = (int) $val['entity_type_id'];
					break;
				case 'invoice':
					$this->eavValues['invoice'] = (int) $val['entity_type_id'];
					break;
				case 'shipment':
					$this->eavValues['shipment'] = (int) $val['entity_type_id'];
					break;
				case 'creditmemo':
					$this->eavValues['creditmemo'] = (int) $val['entity_type_id'];
					break;
			}
		}
	}
	
	private function getConfigValues($entityTypeId) {
		$storeId = $this->getStoreId();
		
		if (!empty($storeId) && is_numeric($storeId)) {		
			$configValues = Mage::getStoreConfig('customnumbering', $storeId);
		}
		else {
			$configValues = Mage::getStoreConfig('customnumbering');
		}
		
		$eavCode = array_flip($this->eavValues);
		
		foreach ($configValues as $configKey => $configSetting) {
			if ($configKey == $eavCode[$entityTypeId]) {
				if ($configSetting['enabled'] == 0) {
					return false;
				}
				if (empty($configSetting['prefix'])) {
					return false;
				}	
				if (empty($configSetting['startnumber'])) {
					$configSetting['startnumber'] = 1;
				}
				if (empty($configSetting['increment'])) {
					$configSetting['increment'] = 1;
				}
				if (empty($configSetting['minlength'])) {
					$configSetting['minlength'] = 4;
				}
			
				return $configSetting;
			}
		}
		
		return false;
	}
		
	private function fixLength($nextId, $minLength) {
		$len = strlen($nextId);
		
		if ($len < $minLength) {
			for ($i=0; $i<($minLength-$len); $i++) {
				$nextId = '0' . $nextId;
			}
		}
		
		return $nextId;
	}
	
	private function addIncrement($cleanId, $incr) {
		return ($cleanId + $incr);
	}
	
	private function parseLastId($config) {
		$last = $this->getLastId();

		$db  = Mage::getSingleton('core/resource')->getConnection('core_read');		
		$tableName = (string) Mage::getConfig()->getTablePrefix() . 'eav_entity_store';
		$select = $db->select()->from($tableName);
		
		$eavData = $db->fetchAll($select);
		foreach ($eavData as $val) {
			if ($val['entity_type_id'] == $this->_data['entity_type_id'] && $last < $val['increment_last_id']) {
				if ($this->isCurrentFormat($val['increment_last_id'], $config)) {
					$last = $val['increment_last_id'];
				}
			}
		}
		
		if ($this->isCurrentFormat($last, $config)) {
			$oldPrefix = substr($last, 0, strlen($this->convertPrefix($config['prefix'])));
			
			if ($this->isPrefixOld($oldPrefix, $config)) {
				if (!empty($config['startnumber'])) {
					$last = $config['startnumber'];
				}
				else {
					$last = 1;
				}
			}
			else {
				$last = (int)substr($last, strlen($this->convertPrefix($config['prefix'])));

				$last = $this->addIncrement($last, $config['increment']);
			}
		}
		else {
			if (!empty($config['startnumber']) && is_numeric($config['startnumber']) && $config['startnumber'] >= 0) {
				$last = $config['startnumber'];
			}
			else {
				$last = 1;
			}
		}
		
		return $last;
	}
	
	private function convertPrefix($prefixPattern, $convertMandatory = false) {
		$prefixPattern = str_replace('%YYYY%', date('Y'), $prefixPattern);
		$prefixPattern = str_replace('%YY%', date('y'), $prefixPattern);
		
		$prefixPattern = str_replace('%STOREID%', $this->getStoreId(), $prefixPattern);
		
		if ($convertMandatory == false) {
			$prefixPattern = str_replace('%M%', date('m'), $prefixPattern);
			$prefixPattern = str_replace('%W%', date('W'), $prefixPattern);
			$prefixPattern = str_replace('%D%', date('d'), $prefixPattern);
		}
		else {
			$prefixPattern = $this->getPrefixPattern($prefixPattern);
		}

		return $prefixPattern;
	}
	
	private function getPrefixPattern($prefixPattern) {
		$prefixPattern = str_replace('%YYYY%', '(1|2)([0-9]{3})', $prefixPattern);
		$prefixPattern = str_replace('%YY%', '(1|2)([0-9]{1})', $prefixPattern);
		
		$prefixPattern = str_replace('%M%', date('((0([1-9]{1}))|(1([0-2]{1})))'), $prefixPattern);
		$prefixPattern = str_replace('%W%', date('(([0-5]{1})([0-9]{1}))'), $prefixPattern);
		$prefixPattern = str_replace('%D%', date('(([0-3]{1})([0-9]{1}))'), $prefixPattern);
		
		$prefixPattern = str_replace('%STOREID%', '([0-9]{1,})', $prefixPattern);

		return $prefixPattern;
	}
	
	private function isCurrentFormat($lastId, $config) {
		$pattern = '/^'.$this->getPrefixPattern($config['prefix']).'([0-9]{1,})/';
		
		if (preg_match($pattern, $lastId)) {
			return true;
		}
		
		return false;
	}
	
	private function isPrefixOld($oldPrefix, $config) {
		$prefixPattern = $config['prefix'];
		
		if ($config['continuousnumbering'] == 1) {
			$newPrefix = '/^'.$this->convertPrefix($prefixPattern, true).'/';
		}
		else {
			$newPrefix = '/^'.$this->convertPrefix($prefixPattern, false).'/';
		}
		
		if (!preg_match($newPrefix, $oldPrefix)) {
			return true;
		}

		return false;
	}
	
	private function addFinPrefix($nextId, $config) {
		$prefix = $this->convertPrefix($config['prefix']);
		
		return $prefix . $nextId;
	}
	
	private function newNumbering($curConfig) {
			
		$nextId = $this->parseLastId($curConfig);
		
		$nextId = $this->fixLength($nextId, $curConfig['minlength']);
		
		$nextId = $this->addFinPrefix($nextId, $curConfig);

		return $nextId;
	}
	
	public function getNextId() {
		$this->setCurrentIds();
		
		$curConfig = $this->getConfigValues($this->_data['entity_type_id']);

		if ($curConfig === false || empty($curConfig)) {
			//$next = $this->oldNumbering(); //zavolame rodica miesto okopcenia
			$next = parent::getNextId();
		}
		else {
			$next = $this->newNumbering($curConfig);
		}

		return $next;
	}
	
	private function oldNumbering() {
		$last = $this->getLastId();

		if (strpos($last, $this->getPrefix()) === 0) {
			$last = (int)substr($last, strlen($this->getPrefix()));
		} else {
			$last = (int)$last;
		}

		$next = $last+1;
		
		return $this->format($next);
	}
}