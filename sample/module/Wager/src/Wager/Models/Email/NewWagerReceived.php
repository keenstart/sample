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


class NewWagerReceived {
    
    protected $_ReplyEmail = 'no_reply@thegamerwall.com';
    protected $_FromEmail = 'no_reply@thegamerwall.com';
    protected $_bouncedEmail = 'no_reply@thegamerwall.com';
    protected $_ToEmail;
    
    protected $_Game;
    protected $_Console;
    protected $_Username;
    protected $_riskAmount;
    protected $_dbAdapter;
    protected $matchId;
    protected $url;


    public function __construct(AdapterInterface $dbAdapter) {
      $this->_dbAdapter = $dbAdapter;
    }

    public function sendEmail(TheWagers $wager, $url) {
      $this->setProperties($wager, $url);
      
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
        $text = "You have received a new Wager" .
                "\r\n\r\nWager Id:  " . $this->matchId . 
                "\r\nFrom user: " . $this->_Username . 
                "\r\nGame: " . $this->_Game . 
                "\r\nConsole: " . $this->_Console .                 
                "\r\nWager Amount:" . $this->_riskAmount . 
                "\r\nTo view full Wager details click on the link or sign into your WagerWall account: " . $this->url .
                "\r\n\r\nPlease note you are receiving this because you have received a new Wager on your WagerWall account.";
        return $text;
    }

    protected function getEmailHtml() {

      //Set the path to the view template.
      $resolver = new TemplateMapResolver();
      $resolver->setMap(array(
          'newWagerReceived' => ROOT_PATH . '/module/Wager/view/emails/newWagerReceived.phtml'
      ));

      //Create a view object and resolve the path base on the template map resolver above.
      $view = new PhpRenderer();
      $view->setResolver($resolver);

      //Create a view to use with the established template and add any variables that view will use.
      $viewModel = new ViewModel();
      $viewModel->setTemplate('newWagerReceived')->setVariables(array(
          'username' => $this->_Username,
          'consoleName' => $this->_Console,
          'gameName' => $this->_Game,
          'riskAmount' => $this->_riskAmount,
          'matchId' => $this->matchId,
          'url' => $this->url
      ));

      return $view->render($viewModel);
    }

    protected function setProperties(TheWagers $wager, $url) {

    $this->_riskAmount = $wager->riskAmount;
    $this->matchId = $wager->wagerId;
    $this->url = $url;
    
    $theGames = new TheGames($this->_dbAdapter);
    $games = $theGames->getGameId($wager->gameId);
    $this->_Game = $games->gameName;

    $theConsoles = new TheConsoles($this->_dbAdapter);
    $consoles = $theConsoles->getConsoleId($wager->consoleId);
    $this->_Console = $consoles->consoleName;

    $theUser = new User($this->_dbAdapter);
    // Get Asker username//   
    $user = $theUser->getUser($wager->userAskId);
    $this->_Username = $user->username;
    
    // Get Acceptor Email//
    $user = $theUser->getUser($wager->userAcceptId);
    $this->_ToEmail = $user->email;
    }
}