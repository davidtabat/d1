<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Sociallogin_Model_Linkedin_Info extends Varien_Object
	{
		protected $params = array(
			'~' => ''
		);

		protected $fields = array(
			'id',
			'first-name',
			'last-name',
			'email-address',
			'picture-url',
			'public-profile-url',
			'site-standard-profile-request'
		);

		protected $client = null;

		public function _construct() {
			parent::_construct();

			$this->client = Mage::getSingleton('sociallogin/linkedin_oauth2_client');
			if(!($this->client->isEnabled())) {
				return $this;

			}
		}

		public function getClient()
		{
			return $this->client;
		}

		public function setClient(Magebuzz_Sociallogin_Model_Linkedin_Oauth2_Client $client)
		{
			$this->client = $client;
		}

		public function setAccessToken($token)
		{
			$this->client->setAccessToken($token);
		}

		public function getAccessToken()
		{
			return $this->client->getAccessToken();
		}

		public function load($id = null)
		{
			$this->_load();

			return $this;
		}

		protected function _load()
		{
			try{
				$response = $this->client->api(
					'/people',
					'GET',
					$this->params,
					$this->fields
				);

				foreach ($response as $key => $value) {
					$this->{$key} = $value;
				}

			} catch(Magebuzz_Sociallogin_Linkedin_Oauth2_Exception $e) {
				$this->_onException($e);
			} catch(Exception $e) {
				$this->_onException($e);
			}

		}

		protected function _onException($e)
		{
			if($e instanceof Magebuzz_Sociallogin_Linkedin_Oauth2_Exception) {
				Mage::getSingleton('core/session')->addNotice($e->getMessage());
			} else {
				Mage::getSingleton('core/session')->addError($e->getMessage());
			}
	}
	}
