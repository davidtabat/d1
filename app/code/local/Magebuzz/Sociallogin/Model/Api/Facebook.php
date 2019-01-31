<?php
/*
*  @copyright   Copyright (c) 2015 www.magebuzz.com
*/
require_once(Mage::getBaseDir('lib') . '/Magebuzz/Facebook/facebook.php');
class Magebuzz_Sociallogin_Model_Api_Facebook extends Mage_Core_Model_Abstract {
	public function get_facebook_cookie($app_id, $app_secret) {
    if ($_COOKIE['fbsr_' . $app_id] != '') {
      return $this->get_new_facebook_cookie($app_id, $app_secret);
    } else {
      return $this->get_old_facebook_cookie($app_id, $app_secret);
    }
  }

  public function get_old_facebook_cookie($app_id, $app_secret) {
    $args = array();
    parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
    ksort($args);
    $payload = '';
    foreach ($args as $key => $value) {
      if ($key != 'sig') {
        $payload .= $key . '=' . $value;
      }
    }
    if (md5($payload . $app_secret) != $args['sig']) {
      return array();
    }
    return $args;
  }

  public function get_new_facebook_cookie($app_id, $app_secret) {
    $signed_request = $this->parse_signed_request($_COOKIE['fbsr_' . $app_id], $app_secret);    
    $signed_request['uid'] = $signed_request['user_id']; 
    if (!is_null($signed_request)) {
      $access_token_response = $this->getFbData("https://graph.facebook.com/oauth/access_token?client_id=$app_id&redirect_uri=&client_secret=$app_secret&code=$signed_request[code]");
      parse_str($access_token_response);
      $signed_request['access_token'] = $access_token;
      $signed_request['expires'] = time() + $expires;
    }

    return $signed_request;
  }
	
	public function parse_signed_request($signed_request, $secret) {
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);
    $sig = $this->base64_url_decode($encoded_sig);
    $data = json_decode($this->base64_url_decode($payload), true);
    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
      error_log('Unknown algorithm. Expected HMAC-SHA256');
      return null;
    }
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) {
      error_log('Bad Signed JSON signature!');
      return null;
    }
    return $data;
  }

  public function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
  }

  public function getFbData($url)
  {
    $data = null;
    if (ini_get('allow_url_fopen') && function_exists('file_get_contents')) {
      $data = file_get_contents($url);
    } else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
    }
    return $data;
  }
}