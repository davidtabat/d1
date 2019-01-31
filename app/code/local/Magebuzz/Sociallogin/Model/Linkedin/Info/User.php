<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Sociallogin_Model_Linkedin_Info_User extends Magebuzz_Sociallogin_Model_Linkedin_Info
	{
		protected $customer = null;

		public function load($id = null)
		{
			if(is_null($id) && Mage::getSingleton('customer/session')->isLoggedIn()) {
				$this->customer = Mage::getSingleton('customer/session')->getCustomer();
			} else if(is_int($id)){

				$this->customer = Mage::getModel('customer/customer')->load($id);

				// TODO: Implement
			}

			if(!$this->customer->getId()) {
				return $this;
			}

			if(!($socialloginLid = $this->customer->getMagebuzzSocialloginLid()) ||
				!($socialloginLtoken = $this->customer->getMagebuzzSocialloginLtoken())) {
				return $this;
			}

			$this->setAccessToken($socialloginLtoken);
			$this->_load();

			return $this;
		}

		protected function _onException($e) {
			parent::_onException($e);

			$helper = Mage::helper('sociallogin/linkedin');

			$helper->disconnect($this->customer);
		}
	}