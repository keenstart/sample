<?php

namespace Login\Model;

use Application\Models\SesEmail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Application\Models\User;
use Application\Models\UserActionTokens;

class WelcomeEmail {
    
  protected $_welcomeReplyEmail = 'no_reply@thegamerwall.com';
  protected $_welcomeFromEmail = 'no_reply@thegamerwall.com';
  protected $_bouncedEmail = 'no_reply@thegamerwall.com';
  protected $_authenticateLinkPathProd = 'http://www.thegamerwall.com/login/index/validateemail?token='; // change this to www. when launch
  protected $_authenticateLinkPathDev = 'http://osx.thewagerwall.com/login/index/validateemail?token=';
  protected $_userAuthenticateLink;
  
  public function sendWelcomeEmail($email,$dbAdapter/*Added*/){
      $awsEmail = new SesEmail();
      $awsEmail->setSender($this->_welcomeFromEmail);
      $awsEmail->setRecipients(array($email));
      $awsEmail->setSubject($this->getWelcomeEmailSubject());
      if($this->getAuthenticateLink($email,$dbAdapter/*Added*/)){
        $awsEmail->setEmailText($this->getWelcomeEmailText($this->getAuthenticateLink($email,$dbAdapter/*Added*/)));
        $awsEmail->setEmailHtml($this->getWelcomeEmailHtml($this->getAuthenticateLink($email,$dbAdapter/*Added*/)));
      }
      $awsEmail->setReplyToAddress(array($this->_welcomeReplyEmail));
      $awsEmail->setBounceEmailPoint($this->_bouncedEmail);
      return $awsEmail->sendEmail();
  }
  
  protected function getWelcomeEmailSubject(){
      return 'Welcome to The Wager Wall';
  }
  
  protected function getWelcomeEmailText($link){
      return "Welcome to The Wager Wall, where you can put your money where your mouth is.\r\n\r\nThe final step in setting up your account is to authenticate your email address. You can do so by clicking the link below:\r\n\r\n$link\r\n\r\nThis link is only good for the next 24 hours, at which point you will be required to request a new link authenticate your email address.\r\n\r\nBest regards,\r\nThe Wager Wall";
  }
  
  protected function getWelcomeEmailHtml($link){
      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
          'welcomeMailTemplate' => ROOT_PATH . '/module/Login/view/emails/welcome.phtml'
      ));
      
      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);
      
      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('welcomeMailTemplate')->setVariables(array(
          'authenticateLink' => $link
      ));
      
      return $view->render($viewModel);
  }
  
  protected function getAuthenticateLink($email,$dbAdapter/*Added*/){
      if(!$this->_userAuthenticateLink){
        //Create a unique hash
        $hash_token = hash('sha256', $email . '_' . time());
      
        //Load the user
        $user = new User($dbAdapter);
        $user = $user->checkExistingUser($email);
      
        if($user){
          //Set the deadline:
          $date = new \DateTime("now", new \DateTimeZone("UTC"));
          $date->add(new \DateInterval('PT24H'));
      
          //Load the UserActionTokens Model
          $token = new UserActionTokens($dbAdapter);
          $token->exchangeArray(array('token'=>$hash_token, 'user' => $user->id, 'action' => 'validateemail', 'deadline' => $date->format('Y-m-d H:i:s')));
          $token->addUserActionToken();
          $env = getenv('APPLICATION_ENV');
          if($env === 'production'){
            $this->_userAuthenticateLink = $this->_authenticateLinkPathProd . $hash_token;
          } else{
            $this->_userAuthenticateLink = $this->_authenticateLinkPathDev . $hash_token;
          }
        } else{
          $this->_userAuthenticateLink = false;
        }
      }
      return $this->_userAuthenticateLink;
  }
}