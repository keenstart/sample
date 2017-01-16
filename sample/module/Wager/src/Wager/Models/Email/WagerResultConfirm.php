<?php

namespace Wager\Models\Email;

use Zend\Db\Adapter\AdapterInterface;
use Application\Models\SesEmail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Application\Models\The\TheWagers;
use Application\Models\The\TheGames;
use Application\Models\The\TheConsoles;
use Application\Models\User;
use Zend\Session\Container;

class WagerResultConfirm {
    
    protected $_ReplyEmail = 'no_reply@thegamerwall.com';
    protected $_FromEmail = 'no_reply@thegamerwall.com';
    protected $_bouncedEmail = 'no_reply@thegamerwall.com';
    protected $_ToEmail;
    protected $_userSession;
    
    protected $_Game;
    protected $_Console;
    protected $_Username;
    protected $_riskAmount;
    protected $_dbAdapter;
    protected $matchId;
    protected $status;


    public function __construct(AdapterInterface $dbAdapter) {
      $this->_dbAdapter = $dbAdapter;
    }

    public function sendEmail(TheWagers $wager, $userid) {
      $this->setProperties($wager, $userid);
      
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
      return 'A New Wager was Received';
    }

    protected function getEmailText() {
        $text = "Confirm Wager Result" .
                "\r\nYour opponent has confirmed there match result." .
                "\r\n\r\nWager:  " . $this->matchId . ': ' . $this->_Console . ' - ' . $this->_Game . 
                "\r\nOpponent: " . $this->_Username . 
                "\r\nGame: " . $this->_Game . 
                "\r\nStatus: " . $this->status .                 
                "\r\nWager Amount:" . $this->_riskAmount . 
                "\r\nPlease confirm your Wager Result. " .
                "\r\n\r\nPlease note you are receiving this because your Wager Results has Not yet been reported on your WagerWall account.";
        return $text;
    }

    protected function getEmailHtml() {

      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
          'WagerResultConfirm' => ROOT_PATH . '/module/Wager/view/emails/wagerResultConfirm.phtml'
      ));

      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);

      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('WagerResultConfirm')->setVariables(array(
          'username' => $this->_Username,
          'consoleName' => $this->_Console,
          'gameName' => $this->_Game,
          'riskAmount' => $this->_riskAmount,
          'matchId' => $this->matchId,
          'status' =>  $this->status
      ));

      return $view->render($viewModel);
    }

    protected function setProperties(TheWagers $wager, $userid) {

        $this->_riskAmount = $wager->riskAmount;
        $this->matchId = $wager->wagerId;

        $theGames = new TheGames($this->_dbAdapter);
        $games = $theGames->getGameId($wager->gameId);
        $this->_Game = $games->gameName;

        $theConsoles = new TheConsoles($this->_dbAdapter);
        $consoles = $theConsoles->getConsoleId($wager->consoleId);
        $this->_Console = $consoles->consoleName;

        $theUser = new User($this->_dbAdapter);
        if($userid == $wager->userAskId) {
            if($wager->askResult == 1) { 
                $this->status = "Won";
            } 
            if($wager->askResult == 2){
                $this->status = "Lost";
            }
            $userId = $wager->userAskId;
            
            $user = $theUser->getUser($wager->userAcceptId);
            $this->_ToEmail = $user->email;            
        } else {
            if($wager->acceptResult == 1) { 
                $this->status = "Won";
            } 
            if($wager->acceptResult == 2){
                $this->status = "Lost";
            }            
            $userId = $wager->userAcceptId;      
            
            $user = $theUser->getUser($wager->userAskId);
            $this->_ToEmail = $user->email;             
        }

        // Get  username//   
        $user = $theUser->getUser($userId);
        $this->_Username = $user->username;
    }
}