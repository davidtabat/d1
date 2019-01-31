<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Sociallogin_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
	protected $_customerAttributes = array();

	public function setCustomerAttributes($customerAttributes)
	{
		$this->_customerAttributes = $customerAttributes;

		return $this;
	}

	public function installCustomerAttributes()
	{
		foreach ($this->_customerAttributes as $code => $attr) {
			$this->addAttribute('customer', $code, $attr);
		}

		return $this;
	}

	public function removeCustomerAttributes()
	{
		foreach ($this->_customerAttributes as $code => $attr) {
			$this->removeAttribute('customer', $code);
		}

		return $this;
	}
}