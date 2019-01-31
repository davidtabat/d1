<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Sociallogin_Block_Sociallogin extends Mage_Core_Block_Template {

	protected $client = null;
	protected $userInfo = null;

	protected function _construct()
	{
		parent::_construct();
		$this->client = Mage::getSingleton('sociallogin/linkedin_oauth2_client');
		if(!($this->client->isEnabled())){
			return;
		}
		$this->userInfo = Mage::registry('magebuzz_sociallogin_linkedin_userinfo');
		Mage::getSingleton('core/session')->setLinkedinCsrf($csrf = md5(uniqid(rand(), true)));
		$this->client->setState($csrf);
		Mage::getSingleton('customer/session')
			->setSocialConnectRedirect(Mage::helper('core/url')->getCurrentUrl());
	}

	protected function _hasData()
	{
		return $this->userInfo->hasData();
	}

	protected function _getLinkedinId()
	{
		return $this->userInfo->getId();
	}

	protected function _getStatus()
	{
		$siteStandardProfileRequest = $this->userInfo->getSiteStandardProfileRequest();
		if($siteStandardProfileRequest && !empty($siteStandardProfileRequest->url)) {
			$link = '<a href="'.$siteStandardProfileRequest->url.'" target="_blank">'.
				$this->escapeHtml($this->_getName()).'</a>';
		} else {
			$link = $this->_getName();
		}

		return $link;
	}

	protected function _getPublicProfileUrl()
	{
		if($this->userInfo->getPublicProfileUrl()) {
			$link = '<a href="'.$this->userInfo->getPublicProfileUrl().'" target="_blank">'.
				$this->escapeHtml($this->userInfo->getPublicProfileUrl()).'</a>';

			return $link;
		}

		return null;
	}

	protected function _getEmail()
	{
		return $this->userInfo->getEmailAddress();
	}

	protected function _getPicture()
	{
		if($this->userInfo->getPictureUrl()) {
			return Mage::helper('sociallogin/linkedin')
				->getProperDimensionsPictureUrl($this->userInfo->getId(),
					$this->userInfo->getPictureUrl());
		}

		return null;
	}

	protected function _getName()
	{
		return sprintf(
			'%s %s',
			$this->userInfo->getFirstName(),
			$this->userInfo->getLastName()
		);
	}

	protected function _getButtonUrl()
	{
		return $this->client->createAuthUrl();
	}

  public function _prepareLayout() {
    return parent::_prepareLayout();
  }

  public function checkFbUser() {
    $user_id = Mage::getSingleton('customer/session')->getCustomer()->getId();  
    return $user_id;
  }



}