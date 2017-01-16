<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Login\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Login\Form\LoginForm;
use Login\Form\NewuserForm;
use Login\Model\Create;
use Login\Model\Login;
use Application\Models\User;
use Zend\Session\Container;
use Login\Form\ForgotPasswordForm;
use Login\Model\ForgotPassword;
use Login\Model\ResetPassword;
use Login\Form\ResetPasswordForm;
use Login\Form\ResendWelcomeForm;
use Application\Models\UserActionTokens;
use Login\Model\WelcomeEmail;
//use Application\Models\UserLogins;
//use Application\Models\Wagers;

use Facebook\Facebook;
use Login\Model\Facebooklogin;


use Zend\Db\Adapter\AdapterInterface;

class IndexController extends AbstractActionController{
    
    protected $_userTable;
    protected $_userSession;
    protected $_fb;
    protected $_dbAdapter;


    public function __construct(AdapterInterface $dbAdapter) {
      $this->_dbAdapter = $dbAdapter;
    }
    
    public function indexAction(){
        //debuging
        //return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'index'));
        //
      $this->getUserSession()->validated = false; //debug delete
      if(isset($this->getUserSession()->validated)) {
        if($this->getUserSession()->validated){
          $this->registerAndLogin(null,$this->_dbAdapter);
          return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'index'));
          //return $this->redirect()->toRoute('backoffice', array('action' => 'index', 'controller' => 'index'));
        }
      } 
      
      $this->checkUsIp();
      
      $login = new LoginForm();
      $newuserForm = new NewuserForm();
      $forgotPasswordForm = new ForgotPasswordForm();
      $loginModel = new Login();
      
      $user = new User($this->_dbAdapter);//$this->getServiceLocator()
      
      $return = array();
      $request = $this->getRequest();
      if ($request->isPost()) {
        $formVersion = $request->getPost('form_type');
        if($formVersion == 'login'){
          $login->setInputFilter($loginModel->getInputFilter());
          $login->setData($request->getPost());
    
          if ($login->isValid()) {
            $checkLogin = $user->loginUser($request->getPost('email'), $request->getPost('password'));
            if($checkLogin){
              $this->getUserSession()->user = $checkLogin;
              
              // Prompt user to verify email and change username //
              $this->getUserSession()->isMessage = false;
              
              $this->getUserSession()->validated = true;
              $this->registerAndLogin();
              $return['failedLogin'] = 0;
              return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'index'));
            } else{
              $return['failedLogin'] = 1;
            }
          } else{
            $return['failedLogin'] = 1;
          }
          
          if($return['failedLogin']){
              $login->setMessages(array(
                  'password' => array(
                      'There was an error logging in. Please verify your username and password and try again.'
                  )
              ));
          }
          } else if($formVersion == 'newuser') {
          $model = new Create();
          $newuserForm->setInputFilter($model->getInputFilter());
          $newuserForm->setData($request->getPost());
    
          if ($newuserForm->isValid()) {
            if(!$user->checkExistingUser($request->getPost('email'))){
              $data = $newuserForm->getData();
              $data['password'] = hash('sha256', ($data['password']));
              $this->getUserSession()->user = $user->createNewUser($data);
              $welcomeEmail = new WelcomeEmail();
              $email = $welcomeEmail->sendWelcomeEmail($request->getPost('email'),$this->_dbAdapter);
              
              // Prompt user to verify email and change username //
              $this->getUserSession()->isMessage = true;
              
              $this->getUserSession()->validated = true;
              $this->registerAndLogin();
              return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'profile'));
            } else{
              $newuserForm->setMessages(array(
                  'email' => array(
                      'This email address is already registered with The Wager Wall.  Please try a different email account, or sign in to the existing account'
                  )
              ));
              $return['failedCreate'] = 1;
            }
          } else{
            $return['failedCreate'] = 1;
          }
        } else if($formVersion == 'forgot'){
            $model = new ForgotPassword();
            $forgotPasswordForm->setInputFilter($model->getInputFilter());
            $forgotPasswordForm->setData($request->getPost());
            
            if($forgotPasswordForm->isValid()) {
                if($user->checkExistingUser($request->getPost('email'))){
                    if($model->sendEmail($request->getPost('email'), $this->_dbAdapter)){
                        $return['successRemind'] = 1;
                    } else{
                        $return['failedRemind'] = 1;
                        $forgotPasswordForm->setMessages(array(
                            'email' => array(
                                'There was an error sending an email to this account. Please verify email and try sending again.'
                            )
                        ));
                    }
                } else {
                    $return['failedRemind'] = 1;
                    $forgotPasswordForm->setMessages(array(
                        'email' => array(
                            'There are no accounts for this email address'
                        )
                    ));
                }
            }
        }
      }

      //--
        $fb = new Facebooklogin();
        if ($request->isGet()) {
            $helper = $fb->getRedirectLoginHelper();
            try {
               $accessToken = $helper->getAccessToken();
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            if (isset($accessToken)) {
                 // Logged in!
                $data = array();
                $userGraph = $fb->getFacebookGraph($accessToken);
                if(isset($userGraph['email'])) {
                    if(!$user->checkExistingUser($userGraph['email'])) {
                        //Add facebook user
                        $data['email'] = $userGraph['email'];
                        $data['password'] = $userGraph['id'];

                        //$data = $newuserForm->getData();
                        $data['password'] = hash('sha256', ($data['password']));
                        $this->getUserSession()->user = $user->createNewUser($data);
                        $welcomeEmail = new WelcomeEmail();
                        $email = $welcomeEmail->sendWelcomeEmail($data['email'],$this->_dbAdapter);
                        
                        // Prompt user to verify email and change username //
                        $this->getUserSession()->isMessage = true;
                        
                        $this->getUserSession()->validated = true;
                        
                        $this->registerAndLogin();
                        //$return['verifymsg'] = 1;
                    } else {
                        //Logon Facebook User
                        $checkLogin = $user->loginUser($userGraph['email'], $userGraph['id']);
                        if($checkLogin){
                          $this->getUserSession()->user = $checkLogin;
                          
                          // Prompt user to verify email and change username //
                          $this->getUserSession()->isMessage = false;
                          
                          $this->getUserSession()->validated = true;
                          $this->registerAndLogin();
                          
                          $return['failedLogin'] = 0;
                          return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'index'));
                        } else{
                          $return['failedLogin'] = 1;
                        }   
                    }
                }else{
                    $return['failedCreate'] = 1;
                }
            } else {
               $return['loginUrl'] = $fb->getFacebookUrl();
            }
        }
      //--
      
//        $requestUser = null;
//        if($this->getUserSession()->user) $requestUser = $this->getUserSession()->user->id;
      
        //--$wagers = new Wagers($this->_dbAdapter);
      
        //--$return['activeWagers'] = $wagers->getAllActiveWagersForWagerWall($requestUser, $request);
      
        $return['forgot'] = $forgotPasswordForm;
        $return['login'] = $login;
      
        $return['newuser'] = $newuserForm;
        return $return;
    }
    
    
    public function resetpasswordAction(){
        $resetPasswordForm = new ResetPasswordForm();
        $model = new ResetPassword();
        $request = $this->getRequest();
        
        $token = $this->params()->fromQuery('token') ? $this->params()->fromQuery('token') : $this->params()->fromPost('token');
        
        //Set the session data based on the token
        $userActionToken = new UserActionTokens($this->_dbAdapter);//$this->getServiceLocator()
        $validToken = $userActionToken->checkToken($token, $this->params('action'));
        if($validToken['success']){
          $user = $validToken['user'];
        } else{
          return array('error' => $validToken['error']);
        }
        
        if ($request->isPost()){
            $resetPasswordForm->setInputFilter($model->getInputFilter());
            $resetPasswordForm->setData($request->getPost());
            
            if ($resetPasswordForm->isValid()) {
                $data = $resetPasswordForm->getData();
                $data['password'] = hash('sha256', ($data['password']));
                $this->getUserSession()->passwordReset = $data['password'];
                $this->getUserSession()->user = $validToken['user'];
                $userActionToken->deleteToken($validToken['tokenId']);
                
                $this->getUserSession()->validated = true;
                $this->registerAndLogin();
            }
        } else{
            $this->getUserSession();
            $this->_userSession->getManager()->getStorage()->clear('user');
            $this->_userSession->getManager()->getStorage()->clear('passwordReset');
        }
        
        return array('resetPassword'=>$resetPasswordForm, 'token' => $token);
    }
    
    public function validateemailAction(){
        $this->layout('layout/basic.phtml');
      
        $returnArray = array();
        $resendWelcomeEmail = new ResendWelcomeForm();
        $request = $this->getRequest();
        
        if($this->params()->fromQuery('token')){
            //Set the session data based on the token
            $userActionToken = new UserActionTokens($this->_dbAdapter);//$this->getServiceLocator()
            $validToken = $userActionToken->checkToken($this->params()->fromQuery('token'), $this->params('action'));
            if($validToken['success']){
              //Update the user record.
              $this->getUserSession()->user = $validToken['user'];
              
              // Prompt user to verify email and change username //
              $this->getUserSession()->isMessage = true;
                        
              $this->getUserSession()->user->email_validated = 1;
              $user = new User($this->_dbAdapter);
              $data = array();
              foreach($this->getUserSession()->user as $key => $value){
                $data[$key] = $value;
              }
              $user->exchangeArray($data);
              $this->getUserSession()->user = $user->saveUser();
              $this->getUserSession()->validated = true;
              $userActionToken->deleteToken($validToken['tokenId']);
              $returnArray['redirectLink'] = $this->url()->fromRoute('wager', array('action'=>'index', 'controller' => 'profile'));
              $returnArray['validToken'] = true;
              return $this->redirectToProfile();// registerAndLogin();
            } else {
              return array('error' => $validToken['error']);
            }
        }
        
        if($request->isPost()){
            $resendWelcomeEmail->setData($request->getPost());
            $resendWelcomeEmail->get('email')->setAttribute('value', $request->getPost('email'));
            if($resendWelcomeEmail->isValid()){
                $user = $this->getUserSession()->user;
                if($request->getPost('email') === $user->email){
                    $welcomeEmail = new WelcomeEmail();
                    if($welcomeEmail->sendWelcomeEmail($request->getPost('email'),$this->_dbAdapter)) {
                        $returnArray['resentMessage'] = true;
                    } else{
                        $resendWelcomeEmail->setMessages(array(
                            'email' => array(
                                'There was a problem sending the email. Please validate address and try again.'
                            )
                        ));
                    }
                } else{
                    $checkUser = new User($this->_dbAdapter);
                    if(!$checkUser->checkExistingUser($request->getPost('email'))){
                      $user = new User($this->_dbAdapter);
                      $user = $user->getUser($this->getUserSession()->user->id);
                      $data = Array(
                      	'email' => $request->getPost('email'),
                        'email_validated' => 0
                      );
                      $user->exchangeArray($data);
                      $this->getUserSession()->user = $user->saveUser();
                      
                      $welcomeEmail = new WelcomeEmail();
                      if($welcomeEmail->sendWelcomeEmail($this->getUserSession()->user->email,$this->_dbAdapter)){
                        $this->getUserSession()->validated = false;
                      } else{
                        $this->getUserSession()->emailSendFail = true;
                      }
                      return $this->redirect()->toRoute('login', array('action'=>'validateemail', 'controller' => 'index'));
                    } else{
                        $resendWelcomeEmail->setMessages(array(
                            'email' => array(
                                'This email address is already registered with The Wager Wall.  Please try a different email account, or sign in to the existing account'
                            )
                        ));
                    }
                }
            }
        } else{
            $resendWelcomeEmail->get('email')->setAttribute('value', $this->getUserSession()->user->email);
            $this->getUserSession()->validated = false;
        }
        
        if($this->getUserSession()->emailSendFail){
            $resendWelcomeEmail->setMessages(array(
                'email' => array(
                    'There was a problem sending the email. Please validate address and try again.'
                )
            ));
        }
                
        $returnArray['resendWelcomeForm'] = $resendWelcomeEmail;
        return $returnArray; 
    }
    
    protected function registerAndLogin(){
        $login = new Login($this->_dbAdapter);
        $login->registerAndLogin(null,$this->_dbAdapter);
        return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'index'));
    }
    
    protected function redirectToProfile(){
        $login = new Login($this->_dbAdapter);
        $login->registerAndLogin(null,$this->_dbAdapter);
        return $this->redirect()->toRoute('wager', array('action' => 'index', 
            'controller' => 'profile'));
    }
    
    protected function validateUser(){
      $userSession = $this->getUserSession();
      if(isset($userSession->validated)){
        if($userSession->validated){
          if(!isset($userSession->user->email_validated) || !$userSession->user->email_validated){
            return $this->redirect()->toRoute('login', array('action' => 'validateemail', 'controller' => 'index'));
          }
          return true;
        } else{
          return $this->redirect()->toRoute('login', array('action' => 'index', 'controller' => 'index'));
        }
      } else{
        return $this->redirect()->toRoute('login', array('action' => 'index', 'controller' => 'index'));
      }
    }
    
    protected function logoutAction(){
      $userSession = $this->getUserSession();
      if(isset($userSession)){
          $userSession->getManager()->getStorage()->clear('user');
          return $this->redirect()->toRoute('login', array('action' => 'index', 'controller' => 'index'));
      } else{
        return $this->redirect()->toRoute('login', array('action' => 'index', 'controller' => 'index'));
      }
    }    
    
    protected function getUserSession(){
      if(!$this->_userSession){
        $this->_userSession = new Container('user');
      }
      return $this->_userSession;
    }
    
    protected function checkUsIp(){
      $session = new \Zend\Session\Container('user');
      //Check the IP Address:
      $ip = new \Application\Models\Ip();
      $session->usIp = $ip->checkIpAddress($_SERVER['REMOTE_ADDR']);      
    }
    

}