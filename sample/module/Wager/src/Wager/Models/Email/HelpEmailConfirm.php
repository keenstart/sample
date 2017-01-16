<?php

namespace Wager\Models\Email;

use Application\Models\SesEmail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;


class HelpEmailConfirm {
    
  protected $_ReplyEmail = 'help@thewagerwall.com';
  protected $_FromEmail = 'help@thewagerwall.com';
  protected $_ToEmail = 'help@thewagerwall.com';
  protected $_bouncedEmail = 'help@thewagerwall.com';

  
  public function sendEmail($email, $question) {
      $awsEmail = new SesEmail();
      $awsEmail->setSender($this->_FromEmail);
      $awsEmail->setRecipients(array($email));//array($email),
      $awsEmail->setSubject($this->getEmailSubject());

      $awsEmail->setEmailText($this->getEmailText($email, $question));
      $awsEmail->setEmailHtml($this->getEmailHtml($email, $question));

      $awsEmail->setReplyToAddress(array($this->_ReplyEmail));
      $awsEmail->setBounceEmailPoint($this->_bouncedEmail);
      return $awsEmail->sendEmail();
  }
  
  protected function getEmailSubject(){
      return 'The Wager Wall User Question';
  }
  
  protected function getEmailText($email, $question) {
      return $email . "\r\n\r\n" . "We have receive your quest and will get back to you soon" . ".\r\n\r\nBest regards,\r\nThe Wager Wall";
  }
  
  protected function getEmailHtml($email, $question) {

      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
          'helpMailTemplate' => ROOT_PATH . '/module/Wager/view/emails/helpconfirm.phtml'
      ));
      
      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);
      
      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('helpMailTemplate')->setVariables(array(
          'helpQuestion' => $question,          
          'userEmail' => $email
      ));
      
      return $view->render($viewModel);
  }
  
  /*protected function getAuthenticateLink($email,$dbAdapter){
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
  }*/
}