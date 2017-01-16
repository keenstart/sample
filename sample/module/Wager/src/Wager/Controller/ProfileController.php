<?php


namespace Wager\Controller;

use Wager\Controller\AbstractWagerController;

use Zend\Session\Container;
use Wager\Form\ChangePasswordForm;
use Wager\Form\ChangeEmailForm;
use Wager\Form\ChangeUsernameForm;
use Wager\Form\ConsoleUsernameForm;
use Wager\Models\ChangePassword;
//suse BackOffice\Form\ChangeCurrencyForm;
use Application\Models\User;
use Login\Model\WelcomeEmail;
use Application\Models\UserLogins;
use Zend\Db\Adapter\AdapterInterface;

class ProfileController extends AbstractWagerController{

    protected $_passwordAttempts;

    public function __construct(AdapterInterface $dbAdapter){
         parent::__construct($dbAdapter);
    }
    
    public function indexAction(){
        $this->validateUser();
        $request = $this->getRequest();
        $changePassword = new ChangePasswordForm();
        $changeEmail = new ChangeEmailForm();
        $changeUsername = new ChangeUsernameForm();
        
        $data = array(
            'xboxGamertag' => $this->getUserSession()->user->xboxGamertag,
            'pSNUsername' => $this->getUserSession()->user->pSNUsername,
            
        );

        $consoleUsername = new ConsoleUsernameForm(null,$data);
        //$changeCurrency = new ChangeCurrencyForm($this->getUserSession()->user, $this->_dbAdapter);
        
        $logins = new UserLogins($this->_dbAdapter);
        
        $openTabs = array();
        
        if($request->isPost()){
            $openTabs[] = 'profile-settings';
            $user = new User($this->_dbAdapter);
            $formVersion = $request->getPost('form_type');
            if($formVersion == 'changeUsername'){
              $changeUsername->setData($request->getPost());
              if ($changeUsername->isValid()){
                  //We need to make sure the username isn't existing in the system already:
                  if(!$user->checkExistingUsername($request->getPost('username'))){
                    $data = $changeUsername->getData();
                    //Persist the username to a session value.
                    $saveAfterValidation = new Container('validation_hold');
                    $saveAfterValidation->post = $request->getPost();
                    
                    return $this->redirect()->toRoute('wager', array('action'=>'updateusername', 'controller'=>'profile'));
                  } else{
                    $openTabs[] = 'changeUsername';
                    $changeUsername->setMessages(array(
                        'username' => array(
                            'This username is not available. Please try another username.'
                        )
                    ));
                  }
              }
            } else if($formVersion == 'consoleUsername'){ 
              $consoleUsername->setData($request->getPost());
              if($consoleUsername->isValid()){
                $userCurrent = new User($this->_dbAdapter);
                $userCurrent = $user->getUser($this->getUserSession()->user->id);
                $userCurrent->xboxGamertag = $request->getPost('xboxGamertag');
                $userCurrent->pSNUsername = $request->getPost('pSNUsername');
                
                $user = new User($this->_dbAdapter);
                
//                $user->exchangeArray(Array('xboxGamertag' => $request->getPost('xboxGamertag'),
//                    'pSNUsername' => $request->getPost('pSNUsername')));

                $this->getUserSession()->user = $user->saveUserData(get_object_vars($userCurrent));
              }              
//            } else if($formVersion == 'changeCurrency'){ 
//              $changeCurrency->setData($request->getPost());
//              if($changeCurrency->isValid()){
//                $user = new User($this->_dbAdapter);
//                $user = $user->getUser($this->getUserSession()->user->id);
//                $user->exchangeArray(Array('currency' => $request->getPost('currency')));
//                $this->getUserSession()->user = $user->saveUser();
//              }
            } else if($formVersion == 'changeEmail'){
              $changeEmail->setData($request->getPost());
              if ($changeEmail->isValid()){
                  //We need to make sure the email isn't existing in the system already:
                  if(!$user->checkExistingUser($request->getPost('email'))){
                    $data = $changeEmail->getData();
                    //Persist the email to a session value.
                    $saveAfterValidation = new Container('validation_hold');
                    $saveAfterValidation->post = $request->getPost();
                    
                    return $this->redirect()->toRoute('wager', array('action'=>'updateemail', 'controller'=>'profile'));
                  } else{
                    $openTabs[] = 'changeEmail';
                    $changeEmail->setMessages(array(
                        'email' => array(
                            'This email address is already registered with The Wager Wall.  Please change the email address to proceed.'
                        )
                    ));
                  }
              }
            } else if($formVersion == 'changePassword'){
                $filter = new ChangePassword();
                $changePassword->setInputFilter($filter->getInputFilter());
                $changePassword->setData($request->getPost());
                $openTabs[] = 'changePassword';
                
                if($user->validateUserPassword($this->getUserSession()->user, $request->getPost('original_password'))){
                    if($changePassword->isValid()){
                        //Persist the email to a session value.
                        $saveAfterValidation = new Container('validation_hold');
                        $saveAfterValidation->post = $request->getPost();
                        
                        return $this->redirect()->toRoute('wager', array('action'=>'updatepassword', 'controller'=>'profile'));
                    }
                } else{
                    $this->getPasswordAttempts();
                    if(!$this->_passwordAttempts->tries){
                        $this->_passwordAttempts->tries = 1;
                    } else{
                        $this->_passwordAttempts->tries += 1;
                    }
                    
                    if($this->_passwordAttempts->tries > 2){
                        $this->_passwordAttempts->tries = 0;
                        return $this->redirect()->toRoute('wager', array('action' => 'logout', 'controller' => 'index'));
                    }
                    $changePassword->setMessages(array(
                        'original_password' => array(
                            'There was a problem validating your current password. Please try again. You will have three attempts before you are automatically logged out.'
                        )
                    ));
                }
            }
        }
        

        
        if($this->getUserSession()->resetEmailFail){
            $openTabs[] = 'profile-settings';
            $openTabs[] = 'changeEmail';
            
            $changeEmail->setMessages(array(
                'email' => array(
                    'There was an error sending mail to this address. Please modify the email address and try again.'
                )
            ));
            $this->getUserSession()->resetEmailFail = false;
        }
        
        if($this->getUserSession()->resetPasswordFail){
          $openTabs[] = 'profile-settings';
          $openTabs[] = 'changePassword';
        
          $changePassword->setMessages(array(
              'password' => array(
                  'There was a problem resetting your password. Please try again.'
              )
          ));
          $this->getUserSession()->resetPasswordFail = false;
        }
        
        $this->_view->setVariable('userEmail', $this->getUserSession()->user->email);
        $this->_view->setVariable('changePassword', $changePassword);
        $this->_view->setVariable('changeEmail', $changeEmail);
        $this->_view->setVariable('changeUsername', $changeUsername);
        $this->_view->setVariable('consoleUsername', $consoleUsername);
        
        $this->_view->setVariable('username', $this->getUserSession()->user->username);
        $this->_view->setVariable('xboxGamertag', $this->getUserSession()->user->xboxGamertag);
        $this->_view->setVariable('pSNUsername', $this->getUserSession()->user->pSNUsername);
        
        if(isset($this->getUserSession()->isMessage) &&
                $this->getUserSession()->isMessage)
        {
            $this->_view->setVariable('verifymsg', true);
        }
         
        //$this->_view->setVariable('changeCurrency', $changeCurrency);
        $this->_view->setVariable('tab_show', $openTabs);
        $this->_view->setVariable('logins', $logins->getRecentLogins($this->getUserSession()->user));
        
        return $this->_view;
    }
  
    public function updateusernameAction(){
      $validateAction = new Container('validation_hold');
      
      $originalPost = $validateAction->post;
      $validateAction->post = NULL;
      
      $data['username'] = $originalPost->username;
      
      $user = new User($this->_dbAdapter);
      $user = $user->getUser($this->getUserSession()->user->id);
      $user->exchangeArray($data);
      $user->setAdapterDb($this->_dbAdapter); 
      $this->getUserSession()->user = $user->saveUser();
      
      return $this->redirect()->toRoute('wager', array('action'=>'index', 'controller'=>'profile'));
    }
    
    public function updateemailAction(){
        $validateAction = new Container('validation_hold');
            
        $originalPost = $validateAction->post;
        $validateAction->post = NULL;
        
        $data['email'] = $originalPost->email;
        $data['email_validated'] = false;
        
        $user = new User($this->_dbAdapter);
        $user = $user->getUser($this->getUserSession()->user->id);
        $user->exchangeArray($data);
        $user->setAdapterDb($this->_dbAdapter);
        $this->getUserSession()->user = $user->saveUser();
        
        //Send the email:
        $welcomeEmail = new WelcomeEmail();
        if($welcomeEmail->sendWelcomeEmail($this->getUserSession()->user->email)){
          $this->getUserSession()->validated = true;
          return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'index'));
        } else{
          $this->getUserSession()->emailSendFail = true;
          return $this->redirect()->toRoute('login', array('action'=>'validateemail', 'controller' => 'index'));
        }
    }

    public function updatepasswordAction(){
      $validateAction = new Container('validation_hold');

      $originalPost = $validateAction->post;
      $validateAction->post = NULL;
  
      $data['password'] = hash('sha256', ($originalPost->password));
      
      $user = new User($this->_dbAdapter);
      $user = $user->getUser($this->getUserSession()->user->id);
      $user->exchangeArray($data);
      $user->setAdapterDb($this->_dbAdapter);
      $this->getUserSession()->user = $user->saveUser();

      return $this->redirect()->toRoute('wager', array('action'=>'index', 'controller'=>'index'));
    }
    
    protected function getPasswordAttempts(){
        if(!$this->_passwordAttempts){
          $this->_passwordAttempts = new Container('password_attempts');
        }
        return $this->_passwordAttempts;
    }
}