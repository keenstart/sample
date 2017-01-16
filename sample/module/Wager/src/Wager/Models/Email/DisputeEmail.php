<?php

namespace Wager\Models\Email;

use Zend\Db\Adapter\AdapterInterface;
use Application\Models\SesEmail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Application\Models\The\TheDispute;
use Application\Models\The\TheWagers;
use Application\Models\User;


class DisputeEmail {
    
    protected $_ReplyEmail = 'no_reply@thegamerwall.com';
    protected $_FromEmail = 'no_reply@thegamerwall.com';
    protected $_bouncedEmail = 'no_reply@thegamerwall.com';
    protected $_ToEmail;
    
    protected $_Username;
    protected $_DisputeId;
    protected $_DisputeDetails;
    protected $matchId;
    protected $_riskAmount;
    protected $_dbAdapter;
    protected $url;


    public function __construct(AdapterInterface $dbAdapter) {
      $this->_dbAdapter = $dbAdapter;
    }

    public function sendEmail(TheDispute $dispute, $url) {
      $this->setProperties($dispute, $url);
      
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
      return 'Received a Dispute';
    }

    protected function getEmailText() {
        $text = "Disputed Wager" .
                "\r\nOne of your Wagers has been disputed." .
                "\r\n\r\nDispute ID: " . $this->_DisputeId . 
                "\r\nWager ID: " . $this->matchId . 
                "\r\nOpponent: " . $this->_Username . 
                "\r\nDispute Details: " . $this->_DisputeDetails .                 
                "\r\nWager Amount:" . $this->_riskAmount . 
                "\r\nTo view full Wager details click on the link or sign into your WagerWall account: " . $this->url .
                "\r\n\r\nPlease note you are receiving this because a Dispute has been made on one of your Wagers in your WagerWall account.";
        return $text;      
    }

    protected function getEmailHtml() {

      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
          'DisputeEmail' => ROOT_PATH . '/module/Wager/view/emails/disputeEmail.phtml'
      ));

      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);

      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('DisputeEmail')->setVariables(array(
          'username' => $this->_Username,
          'disputeId' => $this->_DisputeId,
          'disputeDetails' => $this->_DisputeDetails,
          'riskAmount' => $this->_riskAmount,
          'matchId' => $this->matchId,  
          'url' => $this->url
      ));

      return $view->render($viewModel);
    }

    protected function setProperties(TheDispute $dispute, $url) {
        $this->url = $url;
 
        $this->_DisputeId = $dispute->disputeId;
        $this->_DisputeDetails = $dispute->disputeDetails;
        
        $theWagers = new TheWagers($this->_dbAdapter);        
        $wager = $theWagers->getWagerById($dispute->wagerId);
        
        $this->_riskAmount = $wager->riskAmount;
        $this->matchId = $wager->wagerId;
                
                
        $theUser = new User($this->_dbAdapter);
        // Get Asker username//   
        $user = $theUser->getUser($dispute->userId);
        $this->_Username = $user->username;

        // Get Acceptor Email//
        
        if($dispute->userId != $wager->userAskId) { 
            $userId = $wager->userAskId;
        } else {
            $userId = $wager->userAcceptId;
        }        
        $user = $theUser->getUser($userId);
        $this->_ToEmail = $user->email;
    }
}