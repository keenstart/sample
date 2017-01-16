<?php

namespace Wager\Models\Email;

use Zend\Db\Adapter\AdapterInterface;
use Application\Models\SesEmail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Application\Models\The\TheMessages;
use Application\Models\User;


class NewMessageReceived {
    
    protected $_ReplyEmail = 'no_reply@thegamerwall.com';
    protected $_FromEmail = 'no_reply@thegamerwall.com';
    protected $_bouncedEmail = 'no_reply@thegamerwall.com';
    protected $_ToEmail;
    
    protected $_Subject;
    protected $_Messages;
    protected $_From;

    protected $_dbAdapter;
    protected $url;


    public function __construct(AdapterInterface $dbAdapter) {
      $this->_dbAdapter = $dbAdapter;
    }

    public function sendEmail(TheMessages $messages, $url) {
      $this->setProperties($messages, $url);
      
      $awsEmail = new SesEmail();
      $awsEmail->setSender($this->_FromEmail);
      $awsEmail->setRecipients(array($this->_ToEmail));//array($email),
      $awsEmail->setSubject($this->getEmailSubject());

      $awsEmail->setEmailText($this->getEmailText());
      $awsEmail->setEmailHtml($this->getEmailHtml());

      $awsEmail->setReplyToAddress(array($this->_ReplyEmail));
      $awsEmail->setBounceEmailPoint($this->_bouncedEmail);
      return $awsEmail->sendEmail();
    }

    protected function getEmailSubject() {
      return 'Received New Wager Mesage';
    }

    protected function getEmailText() {
           $text = "You have received a message" .
                "\r\n\r\nFrom user: " . $this->_From . 
                "\r\nSubject: " . $this->_Subject . 
                "\r\nMessage: " . $this->_Messages . 
                "\r\nTo view full Wager details click on the link or sign into your WagerWall account: " . $this->url .
                "\r\n\r\nPlease note you are receiving this because you have received a new message to your WagerWall account.";
        return $text;
    }

    protected function getEmailHtml() {

      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
          'NewMessageReceived' => ROOT_PATH . '/module/Wager/view/emails/newMessageReceived.phtml'
      ));

      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);

      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('NewMessageReceived')->setVariables(array(
          'subject' => $this->_Subject,
          'messages' => $this->_Messages,
          'from' => $this->_From,
          'url' => $this->url
      ));

      return $view->render($viewModel);
    }

    protected function setProperties(TheMessages $messages, $url) {
        $this->url = $url;

        $this->_Subject = $messages->subject;
        $this->_Messages = $messages->messages;

        $theUser = new User($this->_dbAdapter);
        // Get Asker username//   
        $user = $theUser->getUser($messages->fromuserId);
        $this->_From = $user->username;

        // Get Acceptor Email//
        $user = $theUser->getUser($messages->touserId);
        $this->_ToEmail = $user->email;
    }
}