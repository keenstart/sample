<?php

namespace Login\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Application\Models\SesEmail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Application\Models\User;
use Application\Models\UserActionTokens;

class ForgotPassword implements InputFilterAwareInterface{
    
  protected $inputFilter;
  protected $_forgotMessageReplyEmail = 'no_reply@thegamerwall.com';
  protected $_forgotMessageFromEmail = 'no_reply@thegamerwall.com';
  protected $_bouncedEmail = 'no_reply@thegamerwall.com';
  protected $_resetLinkPath = 'http://www.thegamerwall.com/login/index/resetpassword?token=';
  protected $_resetLinkPathDev = 'http://osx.thewagerwall.com/login/index/resetpassword?token=';
  protected $_userResetLink;

  public function setInputFilter(InputFilterInterface $inputFilter){
    throw new \Exception("Not used");
  }

  public function getInputFilter(){
    if(!$this->inputFilter){
      $inputFilter = new InputFilter();
      
      $inputFilter->add(array(
          'name' => 'email',
          'required' => true
      ));
      
      $this->inputFilter = $inputFilter;
    }
    return $this->inputFilter;
  }
  
  public function sendEmail($email, $dbAdapter){
      $awsEmail = new SesEmail();
      $awsEmail->setSender($this->_forgotMessageFromEmail);
      $awsEmail->setRecipients(array($email));
      $awsEmail->setSubject($this->getForgotPasswordSubject());
      if($this->getResetLink($email, $dbAdapter)){
          $awsEmail->setEmailText($this->getForgotPasswordText($this->getResetLink($email, $dbAdapter)));
          $awsEmail->setEmailHtml($this->getForgotPasswordHtml($this->getResetLink($email, $dbAdapter)));
      }
      $awsEmail->setReplyToAddress(array($this->_forgotMessageReplyEmail));
      $awsEmail->setBounceEmailPoint($this->_bouncedEmail);
      return $awsEmail->sendEmail();
  }
  
  protected function getForgotPasswordText($resetUrl){
      return "This is an email to reset your password for accessing your WagerWall account. If you did not request to have your password reset, please disregard this email. If you did request a password reset, please click or visit the URL below to reset:\r\n\r\n$resetUrl\r\n\r\nThis link is only good for the next hour, at which point you will be required to request a new link to complete the password reset process.\r\n\r\nBest regards,\r\nWagerWall";
  }
  
  protected function getForgotPasswordHtml($resetUrl){
      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
      	'forgotPasswordMailTemplate' => ROOT_PATH . '/module/Login/view/emails/forgot_password.phtml'
      ));
      
      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);
      
      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('forgotPasswordMailTemplate')->setVariables(array(
        'resetLink' => $resetUrl
      ));
      
      return $view->render($viewModel);
  }
  
  protected function getForgotPasswordSubject(){
      return 'Wager Wall Password Reset';
  }
  
  protected function getResetLink($email, $dbAdapter){
      if(!$this->_userResetLink){
          //Create a unique hash
          $hash_token = hash('sha256', $email . '_' . time());

          //Load the user
          $user = new User($dbAdapter);
          $user = $user->checkExistingUser($email);
          
          if($user){
                //Set the deadline:
                $date = new \DateTime("now", new \DateTimeZone("UTC"));
                $date->add(new \DateInterval('PT1H'));

                //Load the UserActionTokens Model
                $token = new UserActionTokens($dbAdapter);
                $token->exchangeArray(array('token'=>$hash_token, 'user' => $user->id, 'action' => 'resetpassword', 'deadline' => $date->format('Y-m-d H:i:s')));
                $token->addUserActionToken();
                
                $env = getenv('APPLICATION_ENV');
                if($env === 'production') {              
                    $this->_userResetLink = $this->_resetLinkPath . $hash_token;
                } else{
                    $this->_userResetLink = $this->_resetLinkPathDev . $hash_token;
                }                
          } else{
              $this->_userResetLink = false;
          }
      }
      return $this->_userResetLink;
  }
}