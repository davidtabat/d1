<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Sociallogin_Helper_Data extends Mage_Core_Helper_Abstract
{

  public function getLoginImg() {
    $img = Mage::getStoreConfig('sociallogin/facebook/imglogin');
    if (empty($img)) {
      $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
      'frontend/base/default/magebuzz/sociallogin/images/btn-fb-login.png';
    } else {
      $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
      'sociallogin/' . $img;
    }
    return $img;
  }

  public function getTwitterImg() {
    $img = Mage::getStoreConfig('sociallogin/twitter/imgtwitter');
    if (empty($img)) {
      $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
      'frontend/base/default/magebuzz/sociallogin/images/btn-twitter-login.png';
    } else {
      $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
      'sociallogin/' . $img;
    }
    return $img;
  }

  public function getGoogleImg() {
    $img = Mage::getStoreConfig('sociallogin/google/imggoogle');
    if (empty($img)) {
      $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
      'frontend/base/default/magebuzz/sociallogin/images/btn-google-login.png';
    } else {
      $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
      'sociallogin/' . $img;
    }
    return $img;
  }

	public function getLinkedinImg()
	{
		$img = Mage::getStoreConfig('sociallogin/linkedin/imglinkedin');
		if(empty($img)){
			$img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
				'frontend/base/default/magebuzz/sociallogin/images/btn-li-login.png';
		} else{
			$img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
				'sociallogin/' . $img;
		}
		return $img;
	}

	public function getLoginWithImg()
	{
		$img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
			'frontend/base/default/magebuzz/sociallogin/images/loginwith.png';

		return $img;
	}

  public function getEnableModule()
  {
    return Mage::getStoreConfig('sociallogin/general/enable_sociallogin');
  }

  public function getEnableFacebook()
  {
    return Mage::getStoreConfig('sociallogin/facebook/enable_fb');
  }

  public function getFb_APP_ID()
  {
    return Mage::getStoreConfig('sociallogin/facebook/fbapp_id');
  }

  public function getFb_App_Secret()
  {
    return Mage::getStoreConfig('sociallogin/facebook/fbapp_secret');
  }

  public function getEnableTwitter()
  {
    return Mage::getStoreConfig('sociallogin/twitter/enable_twitter');
  }

  public function getTwitterKey()
  {
    return Mage::getStoreConfig('sociallogin/twitter/tw_key');
  }

  public function getTwitterSecret()
  {
    return Mage::getStoreConfig('sociallogin/twitter/tw_secret');
  }


  public function getEnableGoogle()
  {
    return Mage::getStoreConfig('sociallogin/google/enable_google');
  }

  public function getProviderId($provider)
  {
	  $pro_id = array();
    $collection = Mage::getModel('customer/customer')->getCollection();
    $collection->addAttributeToFilter('provider', array('provider' =>$provider))
    ->addAttributeToSelect('*');
    foreach($collection as $col)
    {
      $pro_id[] = $col->getProvider();
    }
    return count($pro_id);
  }

  public function getLoginUrl()
  {
    return Mage::getUrl('sociallogin/index/popuplogin');
  }

  public function getAccountUrl()
  {
    $login = Mage::getSingleton('customer/session')->isLoggedIn() ;
    if($login){
      return Mage::getUrl('customer/account');
    }
    return Mage::getUrl('sociallogin/index/popuplogin');
  } 
  
  public function getFbSuccessUrl(){
    return Mage::getUrl('sociallogin/index/loginfacebook', array('loginsucc'=>1));
  }
  
  public function getFbCancelUrl(){
    return Mage::getUrl('sociallogin/fblogin/loginfacebook', array('cancel'=>1));
  }

	public static function log($message, $level = null, $file = '', $forceLog = false)
	{
		if(Mage::getIsDeveloperMode()) {
			Mage::log($message, $level, $file, $forceLog);
		}
	}

	public function getEnableLinkedin()
	{
		return Mage::getStoreConfig('sociallogin/linkedin/enabled');
	}
}
