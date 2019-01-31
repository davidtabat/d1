<?php
/*
 * @copyright   Copyright (c) 2015 www.magebuzz.com
 */
require_once(Mage::getBaseDir('lib') . '/Magebuzz/Facebook/facebook.php');
require (Mage::getBaseDir('lib') . '/Magebuzz/Twitter/twitteroauth.php');
require_once (Mage::getBaseDir('lib') . DS . 'Magebuzz' . DS . 'Google' . DS . 'Google_Client.php');
require_once (Mage::getBaseDir('lib') . DS . 'Magebuzz' . DS . 'Google' . DS . 'contrib' . DS . 'Google_Oauth2Service.php');
require_once (Mage::getBaseDir('lib') . DS . 'Magebuzz' . DS . 'Google' . DS . 'contrib' . DS . 'Google_PlusService.php');

class Magebuzz_Sociallogin_IndexController extends Mage_Core_Controller_Front_Action {

  public function indexAction(){  
    $this->loadLayout();  
    $this->renderLayout();
  }

  public function loginAction(){
    $this->loadLayout();
    $this->renderLayout();
  }

  public function popuploginAction(){     
    $this->loadLayout();  
    $this->renderLayout();   
  }

  public function popupregisterAction(){
    $this->loadLayout();
    $this->renderLayout();
  }

	public function loginForm()
	{
		$this->_redirect('customer/account/login');
	}

  public function LogoutAction()
  {
    $session = Mage::getSingleton('customer/session');
    $session->logout()
    ->setBeforeAuthUrl(Mage::getUrl());
    $this->_redirect('customer/account/logoutSuccess');
  }

  protected function _loginPostRedirect()
  {
    $session = $this->_getCustomerSession();
    if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
      $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
      if ($session->isLoggedIn()) {
        if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
          if ($referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME)) {
            $referer = Mage::helper('core')->urlDecode($referer);
            if ($this->_isUrlInternal($referer)) {
              $session->setBeforeAuthUrl($referer);
            }
          }
        } else if ($session->getAfterAuthUrl()) {
            $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
          }
      } else {
        $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
      }
    } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
        $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
      } else {
        if (!$session->getAfterAuthUrl()) {
          $session->setAfterAuthUrl($session->getBeforeAuthUrl());
        }
        if ($session->isLoggedIn()) {
          $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
        }
    }
    return $session->getBeforeAuthUrl(true);
  }

  private function _getCustomerSession() {
    return Mage::getSingleton('customer/session');
  }

  public function loginpostAction() {
    $session = $this->_getCustomerSession();
    $socialmodle = Mage::getModel('sociallogin/sociallogin');
    $login['username'] = $_GET['username'];  
    $login['password'] = $_GET['password'];    
    try {
      $session->login($login['username'], $login['password']);
      echo $this->_loginPostRedirect();     
    } catch (Mage_Core_Exception $e) {
      switch ($e->getCode()) {
        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
          $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
          echo $message = Mage::helper('sociallogin')->__('Account Not Confirmed %s', $value);
          break;
        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
          echo $message = Mage::helper('sociallogin')->__('Invalid Email Address or Password');
          break;
        default:
          echo $message = $e->getMessage();
      }
      $session->setUsername($login['username']);
    }     
    exit();
  }

  public function _helper()
  {
    return Mage::helper('sociallogin');
  }         

  public function forgotpasswordAction()
  {
    $email = (string) $this->getRequest()->getPost('email');
    if ($email) {
      if (!Zend_Validate::is($email, 'EmailAddress')) {
        $this->_getCustomerSession()->setForgottenEmail($email);
        $this->_getCustomerSession()->addError(Mage::helper('sociallogin')->__('Invalid email address.'));
        echo Mage::helper('sociallogin')->__('Invalid email address.');
        return;
      }
      $customer = Mage::getModel('customer/customer')
      ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
      ->loadByEmail($email);
      if ($customer->getId()) {
        try {
          $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
          $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
          $customer->sendPasswordResetConfirmationEmail();
          echo "okforgot";
          return;
        } catch (Exception $exception) {
          $this->_getCustomerSession()->addError($exception->getMessage());
          echo Mage::helper('sociallogin')->__('Forgot password fail.');
          return;
        }
      }else
      {
        echo Mage::helper('sociallogin')->__('Forgot password fail because Email not exsit');
        return;
      }     
    } else {
      echo Mage::helper('sociallogin')->__('Please enter your email.');            
      return;
    }
  }

  public function registeraccountAction()
  {
    $data = $this->getRequest()->getParams();  
    $customer = Mage::getSingleton('customer/customer');  
    $customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
    ->loadByEmail($data['email']);
    if(!$customerbyemail->getId())
    {        
      $customer->setId(null)
      ->setSkipConfirmationIfEmail($data['email'])
      ->setFirstname($data['firstname'])
      ->setLastname($data['lastname'])
      ->setEmail($data['email'])
      ->setPassword($data['password'])
      ->setConfirmation($data['confirmation']);
      //->setProvider($data['providerid']);
      if ($this->getRequest()->getParam('is_subscribed', false)) {
        $customer->setIsSubscribed(1);
      }
      $errors = array();
      $validationCustomer = $customer->validate();
      if (is_array($validationCustomer)) {
        $errors = array_merge($validationCustomer, $errors);
        echo $validationCustomer;
        return;
      }
      $validationResult = true;
      if (true === $validationResult) {
        $customer->save();
        $customerAfterRegister = Mage::getModel('customer/customer')->load($customer->getId());      
        $customerAfterRegister->setConfirmation($data['confirmation']) ;
        $redirect = $this->_successProcessRegistration($customerAfterRegister) ;
        echo $redirect;
        return;
      } else {
        if (is_array($errors)) {
          foreach ($errors as $errorMessage) {
            $this->_getCustomerSession()->addError($errorMessage);
          }
        }
        echo Mage::getUrl('customer/account/create');    
        return;
      }
    }else{
      //$url = Mage::getUrl('customer/account/create');
      $message = Mage::helper('sociallogin')->__('There is already an account with this email address. If you are
      sure that it is your email address, please comeback to login or get your password and access your account.');
      echo $message;
      return;
    }
  }

  protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
  {
    $session = $this->_getCustomerSession();
    if ($customer->isConfirmationRequired()) {            
      $app =  Mage::app();            
      $store = $app->getStore();
      $customer->sendNewAccountEmail(
      'confirmation',
      $session->getBeforeAuthUrl(),
      $store->getId()
      );
      $customerHelper = Mage::helper('customer');
      $session->addSuccess(Mage::helper('sociallogin')->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
      $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
    } else {
      $this->_getCustomerSession()->addSuccess(
      Mage::helper('sociallogin')->__('Thank you for registering with %s', Mage::app()->getStore()->getFrontendName())
      );
      $customer->sendNewAccountEmail(); 
      $session->setCustomerAsLoggedIn($customer);            
    }
    $url = $redirect = $this->_loginPostRedirect();          
    return $url;
  }


	//facebook
	public function loginfacebookAction(){
		$emailcustomer = Mage::getSingleton('core/session')->getEmailCustomer();
		$facebookModel = Mage::getSingleton('sociallogin/api_facebook');
		$app_key = Mage::helper('sociallogin')->getFb_APP_ID();
		$fbapp_Secret = Mage::helper('sociallogin')->getFb_App_Secret();
		$me = null;
		$facebook = new Facebook(array(
			'appId'  => $app_key,
			'secret' => $fbapp_Secret,
		));
		$user = $facebook->getUser();

		if($user){
			$me = $facebook->api("/me");
			if (!is_null($me)) {
				$data = array('firstname'=>$me['first_name'],'lastname'=>$me['last_name'],'email'=>$me['email']);
				if($data['email'] == '')
				{
					$data['email'] = $emailcustomer;
				}
				$link = $this->customerfacebookAction($data);
				$this->_redirectUrl($link);
			}else{
				die("<script>window.close();</script>");
			}
		}
	}

	public function customerfacebookAction($data)
	{
		$customer = Mage::getModel('customer/customer');
		$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($data['email']);
		if($customerbyemail->getId()) {
			$this->_getCustomerSession()->setCustomerAsLoggedIn($customerbyemail);
			$message = '';
			if ($customerbyemail->getConfirmation() && $customerbyemail->isConfirmationRequired()) {
				$value = Mage::helper('customer')->getEmailConfirmationUrl($customerbyemail->getEmail());
				$message = Mage::helper('sociallogin')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
			}
			if($message != ''){
				$this->_getCustomerSession()->addError($message);
			}
			$redirect = $this->_loginPostRedirect();
			return $redirect;
		}else
		{
			return $this->autoregisterfacebookAction($data);
		}
	}

	public function autoregisterfacebookAction($data)
	{
		$customer = Mage::getModel('customer/customer');
		$customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
		$randomPassword = $customer->generatePassword(8);
		$prefix = Mage::getStoreConfig('sociallogin/facebook/prefix');
		$customer->setId(null)
			->setSkipConfirmationIfEmail($data['email'])
			->setFirstname($data['firstname'])
			->setLastname($data['lastname'])
			->setEmail($data['email'])
			->setPrefix($prefix)
			->setPassword($randomPassword)
			->setConfirmation($randomPassword)
			->setProvider('facebook');
		if ($this->getRequest()->getParam('is_subscribed', false)) {
			$customer->setIsSubscribed(1);
		}
		$errors = array();
		$validationCustomer = $customer->validate();
		if (is_array($validationCustomer)) {
			$errors = array_merge($validationCustomer, $errors);
		}
		$validationResult = true;
		if (true === $validationResult) {
			$customer->save();
			$customerAfterRegister = Mage::getModel('customer/customer')->load($customer->getId());
			$customerAfterRegister->setConfirmation($customer->getConfirmation()) ;
			$redirect = $this->_successProcessRegistration($customerAfterRegister);
			return $redirect;
		} else {
			if (is_array($errors)) {
				foreach ($errors as $errorMessage) {
					$this->_getCustomerSession()->addError($errorMessage);
				}
			}
			return $this->_redirect('customer/account/create');
		}
	}

	public function facebookcheckAction()
	{
		$email = $this->getRequest()->getPost('email_value');
		$customer = Mage::getSingleton('customer/customer');
		$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($email);
		Mage::getSingleton('core/session')->setEmailCustomer($email);
		if($customerbyemail->getId())
		{
			if(($customerbyemail->getProvider()=='') || ($customerbyemail->getProvider()=='twitter') ||
				($customerbyemail->getProvider()=='google') || ($customerbyemail->getProvider() == 'linkedin'))
			{
				echo Mage::helper('sociallogin')->__('Customer email address exsit');
				return false;
			} else if($customerbyemail->getProvider() == 'facebook')
			{
				$_url = Mage::helper('core')->urlEncode(Mage::helper('core/url')->getCurrentUrl());
				echo Mage::getUrl('sociallogin/index/loginfacebook').'?referer='.$_url;
				return true;
			}

		} else{
			$_url = Mage::helper('core')->urlEncode(Mage::helper('core/url')->getCurrentUrl());
			echo Mage::getUrl('sociallogin/index/loginfacebook').'?referer='.$_url;
		}

		exit();
	}

	//google

	public function google_callbackAction(){

		$code = $this->getRequest()->getParam('code');

		if ($code){
			$client = $this->getGoogleClient();
			$google_oauthV2 = new Google_Oauth2Service($client);
			$client->authenticate($code);
			if ($client->getAccessToken()){
				$profile = $google_oauthV2->userinfo->get();

				$customer = Mage::getSingleton('customer/customer');
				$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
					->loadByEmail($profile['email']);
				if($customerbyemail->getId())
				{
					if(($customerbyemail->getProvider()=='') || ($customerbyemail->getProvider()=='facebook') ||
						($customerbyemail->getProvider() == 'linkedin') || ($customerbyemail->getProvider() == 'twitter'))
					{
						Mage::getSingleton('core/session')->addError('Customer email already exsit. Please login again with
						email and password, or your account social login');
					}
				} else {
				$email = $profile['email'];
				$first_name = $profile['given_name'];
				$last_name = $profile['family_name'];
				$data=array('firstname'=>$first_name,'lastname'=>$last_name,'email'=>$email);
				$redirect=$this->customergoogleAction($data);
				die("<script>window.close();window.opener.location = '$redirect';</script>");
				}
			}
			else{
				$this->getSession()->addError($this->__('Could not connect to Google. Refresh the page or try again later.'));
			}
		}else{
			$this->getSession()->addError($this->__('Could not connect to Google. Refresh the page or try again later.'));
		}
		$redirect = Mage::getBaseUrl();
		die("<script>window.close();window.opener.location = '$redirect';</script>");
	}

	private function getGoogleClient(){

		$client = new Google_Client();
		$client->setApplicationName($this->__('Login with Google'));
		$client->setClientId(Mage::getStoreConfig('sociallogin/google/google_app_id'));
		$client->setClientSecret(Mage::getStoreConfig('sociallogin/google/google_app_secret'));


		$callback_params = array('_secure' => true);

		$callback_url = Mage::getUrl('sociallogin/index/google_callback');
		$client->setRedirectUri($callback_url);
		return $client;
	}

	public function googleloginAction() {

		$client = $this->getGoogleClient();
		$google_oauthV2 = new Google_Oauth2Service($client);
		$auth_url = $client->createAuthUrl();

		if ($this->getRequest()->getParam('referer', '')) {
			Mage::getSingleton('core/session')->setData('referer', $this->getRequest()->getParam('referer'));
		}

		return $this->_redirectUrl($auth_url);
	}

	public function customergoogleAction($data)
	{
		$customer = Mage::getModel('customer/customer');
		$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($data['email']);
		if($customerbyemail->getId()) {
			$this->_getCustomerSession()->setCustomerAsLoggedIn($customerbyemail);
			$message = '';
			if ($customerbyemail->getConfirmation() && $customerbyemail->isConfirmationRequired()) {
				$value = Mage::helper('customer')->getEmailConfirmationUrl($customerbyemail->getEmail());
				$message = Mage::helper('sociallogin')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
			}
			if($message != ''){
				$this->_getCustomerSession()->addError($message);
			}
			$redirect = $this->_loginPostRedirect();
			return $redirect;
		}else
		{
			return $this->autoregistergoogleAction($data);
		}
	}

	public function autoregistergoogleAction($data)
	{
		$customer = Mage::getModel('customer/customer');
		$customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
		$randomPassword = $customer->generatePassword(8);
		$prefix = Mage::getStoreConfig('sociallogin/google/prefix');
		$customer->setId(null)
			->setSkipConfirmationIfEmail($data['email'])
			->setFirstname($data['firstname'])
			->setLastname($data['lastname'])
			->setEmail($data['email'])
			->setPrefix($prefix)
			->setPassword($randomPassword)
			->setConfirmation($randomPassword)
			->setProvider('google');
		if ($this->getRequest()->getParam('is_subscribed', false)) {
			$customer->setIsSubscribed(1);
		}
		$errors = array();
		$validationCustomer = $customer->validate();
		if (is_array($validationCustomer)) {
			$errors = array_merge($validationCustomer, $errors);
		}
		$validationResult = true;
		if (true === $validationResult) {
			$customer->save();
			$customerAfterRegister = Mage::getModel('customer/customer')->load($customer->getId());
			$customerAfterRegister->setConfirmation($customer->getConfirmation()) ;
			$redirect = $this->_successProcessRegistration($customerAfterRegister);
			return $redirect;
		} else {
			if (is_array($errors)) {
				foreach ($errors as $errorMessage) {
					$this->_getCustomerSession()->addError($errorMessage);
				}
			}
			return $this->_redirect('customer/account/create');
		}
	}

	public function googlecheckAction()
	{
		$email = $this->getRequest()->getPost('email_value');
		$customer = Mage::getSingleton('customer/customer');
		$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($email);
		if($customerbyemail->getId())
		{
			if(($customerbyemail->getProvider()=='') || ($customerbyemail->getProvider()=='twitter') ||
				($customerbyemail->getProvider()=='facebook'))
			{
				echo Mage::helper('sociallogin')->__('Customer email address exsit');
				return false;
			} else if($customerbyemail->getProvider() == 'google')
			{
				echo Mage::getUrl('sociallogin/index/googlelogin',array('referer'=>Mage::helper('core')->urlEncode(Mage::helper
					('core/url')->getCurrentUrl())));
				return true;
			}
		}else
		{
			echo Mage::getUrl('sociallogin/index/googlelogin',array('referer'=>Mage::helper('core')->urlEncode(Mage::helper
					('core/url')->getCurrentUrl())));
		}

		exit();
	}

	//twitter
	public function twitterloginAction() {
		$tw_oauth_token = Mage::getSingleton('core/session')->getOauthToken();
		$tw_oauth_token_secret = Mage::getSingleton('core/session')->getOauthTokenSecret();
		$consumerKey = Mage::helper('sociallogin')->getTwitterKey();
		$consumersecret = Mage::helper('sociallogin')->getTwitterSecret();
		if (!empty($_GET['oauth_verifier']) && !empty($tw_oauth_token) && !empty($tw_oauth_token_secret)) {
			$twitteroauth = new TwitterOAuth($consumerKey, $consumersecret, $tw_oauth_token, $tw_oauth_token_secret);
			$access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
			Mage::getSingleton('core/session')->setAccessToken($access_token);
			$user_info = $twitteroauth->get('account/verify_credentials');
			if (isset($user_info->error)) {
				echo 'Invalid Email Address';
			}
			else {
				$firstname = $user_info->name;
				$provider_id = $user_info->id;
				$emailcustomer = Mage::getSingleton('core/session')->getEmailCustomer();
				$data = array('firstname'=>$firstname,'lastname'=>'','email'=>$emailcustomer,'providerid'=>$provider_id);
				$customer = Mage::getSingleton('customer/customer');
				$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
					->loadByEmail($emailcustomer);
				$provideExist = Mage::helper('sociallogin')->getProviderId($provider_id);
				if(!$customerbyemail->getId()){
					if($provideExist >0){
						$this->_getCustomerSession()->addError(Mage::helper('sociallogin')->__('Provider Id are required.'));
						$redirect = $this->_loginPostRedirect();
						die("<script>window.close();window.opener.location = '$redirect';</script>");
						return;
					}else
					{
						$url_register = $this->autoregistertwitterAction($data);
						die("<script>window.close();window.opener.location = '$url_register';</script>");
					}
				}else{
					//if($customerbyemail->getProvider()==$provider_id){
						$url_login = $this->customertwitterAction($data);
						die("<script>window.close();window.opener.location = '$url_login';</script>");
					//}else{
						//$this->_getCustomerSession()->addError(Mage::helper('sociallogin')->__('Provider email and customer
						//email different.'));
						//$redirect = $this->_loginPostRedirect();
						//die("<script>window.close();window.opener.location = '$redirect';</script>");
						//return;
					//}
				}
			}
		}
		else {
			die("<script>window.close();</script>");
			return;
		}
	}

	public function twitterpostAction() {
		$email = $this->getRequest()->getPost('email_value');
		$customer = Mage::getSingleton('customer/customer');
		$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($email);
		if($customerbyemail->getId())
		{
			if(($customerbyemail->getProvider()=='') || ($customerbyemail->getProvider()=='google') ||
				($customerbyemail->getProvider()=='facebook') || ($customerbyemail->getProvider() == 'linkedin'))
			{
				echo Mage::helper('sociallogin')->__('Customer email address exsit');
				return;
			}
		}
		if(!$customerbyemail->getId() || $customerbyemail->getProvider() != '')
		{
			Mage::getSingleton('core/session')->setEmailCustomer($email);
			$consumerKey = Mage::helper('sociallogin')->getTwitterKey();
			$consumersecret = Mage::helper('sociallogin')->getTwitterSecret();
			$twitteroauth = new TwitterOAuth($consumerKey, $consumersecret);
			$request_token = $twitteroauth->getRequestToken(Mage::getUrl('sociallogin/index/twitterlogin'));
			if ($twitteroauth->http_code == 200) {
				Mage::getSingleton('core/session')->setOauthToken($request_token['oauth_token']);
				Mage::getSingleton('core/session')->setOauthTokenSecret($request_token['oauth_token_secret']);
				echo $url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
			} else {
				echo Mage::helper('sociallogin')->__('Could not connect to Twitter. Refresh the page or try again later.');
			}
		}
		exit();
	}

	public function twitterformAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	public function customertwitterAction($data)
	{
		$customer = Mage::getModel('customer/customer');
		$customerbyemail = $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($data['email']);
		if($customerbyemail->getId()) {
			$this->_getCustomerSession()->setCustomerAsLoggedIn($customerbyemail);
			$message = '';
			if ($customerbyemail->getConfirmation() && $customerbyemail->isConfirmationRequired()) {
				$value = Mage::helper('customer')->getEmailConfirmationUrl($customerbyemail->getEmail());
				$message = Mage::helper('sociallogin')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
			}
			if($message != ''){
				$this->_getCustomerSession()->addError($message);
			}
			$redirect = $this->_loginPostRedirect();
			return $redirect;
		}else
		{
			return $this->autoregistertwitterAction($data);
		}
	}

	public function autoregistertwitterAction($data)
	{
		$name = explode(' ', $data['firstname'], 2);
		if(count($name) > 1) {
			$firstName = $name[0];
			$lastName = $name[1];
		} else {
			$firstName = $name[0];
			$lastName = $name[0];
		}
		$customer = Mage::getModel('customer/customer');
		$customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
		$randomPassword = $customer->generatePassword(8);
		$prefix = Mage::getStoreConfig('sociallogin/twitter/prefix');

		$customer->setId(null)
			->setSkipConfirmationIfEmail($data['email'])
			->setFirstname($firstName)
			->setLastname($lastName)
			->setEmail($data['email'])
			->setPrefix($prefix)
			->setPassword($randomPassword)
			->setConfirmation($randomPassword)
			->setProvider('twitter');
		if ($this->getRequest()->getParam('is_subscribed', false)) {
			$customer->setIsSubscribed(1);
		}
		$errors = array();
		$validationCustomer = $customer->validate();
		if (is_array($validationCustomer)) {
			$errors = array_merge($validationCustomer, $errors);
		}
		$validationResult = true;
		if (true === $validationResult) {
			$customer->save();
			$customerAfterRegister = Mage::getModel('customer/customer')->load($customer->getId());
			$customerAfterRegister->setConfirmation($customer->getConfirmation()) ;
			$redirect = $this->_successProcessRegistration($customerAfterRegister);
			return $redirect;
		} else {
			if (is_array($errors)) {
				foreach ($errors as $errorMessage) {
					$this->_getCustomerSession()->addError($errorMessage);
				}
			}
			return $this->_redirect('customer/account/create');
		}
	}

	//linkedin

//	public function preDispatch()
//	{
//		parent::preDispatch();
//
//		if (!$this->getRequest()->isDispatched()) {
//			return $this;
//		}
//		if (!Mage::getSingleton('customer/session')
//			->unsBeforeAuthUrl()
//			->unsAfterAuthUrl()
//			->authenticate($this)) {
//			$this->setFlag('', 'no-dispatch', true);
//		}
//	}

	public function linkedinAction()
	{
		$userInfo = Mage::getSingleton('sociallogin/linkedin_info_user')->load();

		Mage::register('magebuzz_sociallogin_linkedin_userinfo', $userInfo);

		$this->loadLayout();
		$this->renderLayout();
	}

	public function connectAction()
	{
		try {
			$this->_connectCallback();
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		$this->_loginPost();
	}

	public function _loginPost()
	{
		$session = $this->_getCustomerSession();

		if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
			// Set default URL to redirect customer to
			$session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());

			// Redirect customer to the last page visited after logging in
			if ($session->isLoggedIn()) {
				if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
					$referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
					if ($referer) {
						// Rebuild referer URL to handle the case when SID was changed
						$referer = $this->_getModel('core/url')
							->getRebuiltUrl( $this->_getHelper('core')->urlDecode($referer));
						if ($this->_isUrlInternal($referer)) {
							$session->setBeforeAuthUrl($referer);
						}
					}

				} else if ($session->getAfterAuthUrl()) {
					$session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
				}
			} else {
				$session->setBeforeAuthUrl( $this->_getHelper('customer')->getLoginUrl());
			}
		} else if ($session->getBeforeAuthUrl() ==  $this->_getHelper('customer')->getLogoutUrl()) {
			$session->setBeforeAuthUrl( $this->_getHelper('customer')->getDashboardUrl());
		} else {
			if (!$session->getAfterAuthUrl()) {
				$session->setAfterAuthUrl($session->getBeforeAuthUrl());
			}
			if ($session->isLoggedIn()) {
				$session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
			}
		}
		$this->_redirectUrl($session->getBeforeAuthUrl(true));
	}

		protected function _getHelper($path)
		{
			return Mage::helper($path);
		}

	public function disconnectAction()
	{
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		try {
			$this->_disconnectCallback($customer);
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}

		$this->_loginPostRedirect();
	}

	protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
		Mage::helper('sociallogin/linkedin')->disconnect($customer);

		Mage::getSingleton('core/session')
			->addSuccess(
				$this->__('You have successfully disconnected your Linkedin account from our store account.')
			);
	}

	protected function _connectCallback() {
		$errorCode = $this->getRequest()->getParam('error');
		$code = $this->getRequest()->getParam('code');
		$state = $this->getRequest()->getParam('state');
		if(!($errorCode || $code) && !$state) {
			// Direct route access - deny
			return $this;
		}

		if(!$state || $state != Mage::getSingleton('core/session')->getLinkedinCsrf()) {
			return $this;
		}
		if($errorCode) {
			// Linkedin API read light - abort
			if($errorCode === 'access_denied') {
				Mage::getSingleton('core/session')
					->addNotice(
						$this->__('Linkedin Connect process aborted.')
					);

				return $this;
			}

			throw new Exception(
				sprintf(
					$this->__('Sorry, "%s" error occured. Please try again.'),
					$errorCode
				)
			);
		}

		if ($code) {
			$info = Mage::getModel('sociallogin/linkedin_info')->load();


			$token = $info->getClient()->getAccessToken();

			$customersByLinkedinId = Mage::helper('sociallogin/linkedin')
				->getCustomersByLinkedinId($info->getId());

			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
				if($customersByLinkedinId->getSize()) {
					Mage::getSingleton('core/session')
						->addNotice(
							$this->__('Your Linkedin account is already connected to one of our store accounts.')
						);

					return $this;
				}
				// Connect from account dashboard - attach
				$customer = Mage::getSingleton('customer/session')->getCustomer();

				Mage::helper('sociallogin/linkedin')->connectByLinkedinId(
					$customer,
					$info->getId(),
					$token
				);

				Mage::getSingleton('core/session')->addSuccess(
					$this->__('Your Linkedin account is now connected to your store account. You can now login using our LinkedIn Login button or using store account credentials you will receive to your email address.')
				);

				return $this;
			}

			if($customersByLinkedinId->getSize()) {
				// Existing connected user - login
				$customer = $customersByLinkedinId->getFirstItem();
				Mage::helper('sociallogin/linkedin')->loginByCustomer($customer);

				Mage::getSingleton('core/session')
					->addSuccess(
						$this->__('You have successfully logged in using your Linkedin account.')
					);

				return $this;
			}

			$customersByEmail = Mage::helper('sociallogin/linkedin')
				->getCustomersByEmail($info->getEmailAddress());

			if($customersByEmail->getSize()) {
				// Email account already exists - attach, login
				$customer = $customersByEmail->getFirstItem();

//				Mage::helper('sociallogin/linkedin')->connectByLinkedinId(
//					$customer,
//					$info->getId(),
//					$token
//				);

				Mage::getSingleton('core/session')->addError(
					$this->__('Customer email already exsit. Please login again with
						email and password, or your account social login')
				);

				return $this;
			}

			// New connection - create, attach, login
			$firstName = $info->getFirstName();
			if(empty($firstName)) {
				throw new Exception(
					$this->__('Sorry, could not retrieve your Linkedin first name. Please try again.')
				);
			}

			$lastName = $info->getLastName();
			if(empty($lastName)) {
				throw new Exception(
					$this->__('Sorry, could not retrieve your Linkedin last name. Please try again.')
				);
			}

			Mage::helper('sociallogin/linkedin')->connectByCreatingAccount(
				$info->getEmailAddress(),
				$info->getFirstName(),
				$info->getLastName(),
				$info->getId(),
				$token
			);

			Mage::getSingleton('core/session')->addSuccess(
				$this->__('Thank you for registering with account Linkedin')
			);
		}
	}


}

















