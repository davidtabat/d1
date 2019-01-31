<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */ 
class Magebuzz_Sociallogin_Block_Facebook extends Mage_Core_Block_Template {
  public function getAppId() {
    return Mage::getStoreConfig('sociallogin/facebook/fbapp_id');
  }

  public function getSecretKey() {
    return Mage::getStoreConfig('sociallogin/facebook/fbapp_secret');
  }
	
  public function checkFbUser() {
    $user_id = Mage::getSingleton('customer/session')->getCustomer()->getId();  
    return $user_id;
  }  
}