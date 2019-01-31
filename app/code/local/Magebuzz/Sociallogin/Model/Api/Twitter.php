<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Sociallogin_Model_Api_Twitter extends Mage_Core_Model_Abstract {
	public function checkConnectionTwitter() {
		$consumerKey = Mage::helper('sociallogin')->getTwitterKey();
		$consumersecret = Mage::helper('sociallogin')->getTwitterSecret();
		$twitteroauth = new TwitterOAuth($consumerKey, $consumersecret);
		$request_token = $twitteroauth->getRequestToken(Mage::getUrl('sociallogin/index/twitterlogin'));
		if ($twitteroauth->http_code == 200) {
			return true;
		} else {
			return false;
		}
	}
}