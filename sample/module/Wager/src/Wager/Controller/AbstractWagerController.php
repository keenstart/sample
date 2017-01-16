<?php 

namespace Wager\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Application\Models\The\TheCredits;
use Application\Models\The\TheChannels;
use Application\Models\Wagers;
use Application\Models\The\TheConsoles;
use Application\Models\The\TheGames;
use Application\Models\The\TheDispute;
use Application\Models\The\TheWagers;
use Application\Models\The\TheMessages;
use Wager\Form\WagersForm;
use Wager\Form\DepositForm;
use Wager\Form\WithdrawForm;
use Wager\Form\MessageForm;
use Wager\Form\MatchForm;

class AbstractWagerController extends AbstractActionController{
    protected $_userSession;
    protected $_request;
    protected $_unfundedWagerCount;
    protected $_dbAdapter;
    
    protected $_newwagerForm;
    protected $_depositForm;
    protected $_withdrawForm;
    protected $_messageForm;
    protected $_matchForm;
    protected $_theChannels;
    protected $_verifymsg;
    
    public function __construct(AdapterInterface $dbAdapter){
        $this->_dbAdapter = $dbAdapter;
    
        $this->_view = new ViewModel();
        $this->_request = new Request();
        if($this->getUserSession()->user){
      
            /*if($this->getUserSession()->user->walletModel){
                $wallet = \Application\Models\Wallets\AbstractWallet::factory($this->getUserSession()->user, $this->getServiceLocator());
     
                if(!$wallet) $wallet = \Application\Models\Wallets\AbstractWallet::wagerwallFactory();
                $this->_view->setVariable('wallet', $wallet);
                //--        $this->_view->setVariable('userAvailableBtc', $wallet->getAccountBalance() / $wallet->getExchangeRate($this->getUserSession()->user->currency));
            } else{
                $wallet = \Application\Models\Wallets\AbstractWallet::wagerwallFactory();
                $this->_view->setVariable('wallet', $wallet);
            }*/
            //--Adjust credits --//
            $theCredits = new TheCredits($this->_dbAdapter);
            $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
            $this->_view->setVariable('myCredits', $myCredits);
            
            
            $this->_matchForm = new MatchForm();
            $this->_view->setVariable('matchresult', $this->_matchForm);

            $this->_newwagerForm = $this->wagerForm();
            $this->_view->setVariable('newwager', $this->_newwagerForm);
            
            $this->_depositForm = new DepositForm();
            $this->_view->setVariable('deposit', $this->_depositForm);
            
            $this->_withdrawForm = new WithdrawForm();
            $this->_view->setVariable('withdrawal', $this->_withdrawForm);            
            
            $this->_messageForm = new MessageForm();
            $this->_view->setVariable('message', $this->_messageForm);
            
            $theChannels = new TheChannels($this->_dbAdapter);
            //$r = $theChannels->getAll();
            $this->_view->setVariable('theChannels', $theChannels->getAll());
            
            $theDispute = new TheDispute($this->_dbAdapter);
            $this->_view->setVariable('theDispute', $theDispute);
            
            $this->_view->setVariable('xboxGamertag', $this->getUserSession()->user->xboxGamertag);
            $this->_view->setVariable('pSNUsername', $this->getUserSession()->user->pSNUsername);

            $theWagers = new TheWagers($this->_dbAdapter);
            $pendingWager = $theWagers->getMyWagersCount();
            $this->_view->setVariable('myWagersCount', $pendingWager);

            $theWagersHistory = new TheWagers($this->_dbAdapter);
            $wagersHistoryCount = $theWagersHistory->getHistoryCount();
            $this->_view->setVariable('wagersHistoryCount', $wagersHistoryCount);

            $theWagersHistoryWins = new TheWagers($this->_dbAdapter);
            $wagersHistoryCountWins = $theWagersHistoryWins->getHistoryWinsCount();
            $this->_view->setVariable('wagersHistoryCountWins', $wagersHistoryCountWins);

            $theWagersHistoryLosses = new TheWagers($this->_dbAdapter);
            $wagersHistoryCountLosses = $theWagersHistoryLosses->getHistoryLossesCount();
            $this->_view->setVariable('wagersHistoryCountLosses', $wagersHistoryCountLosses);            

            $theMessages = new TheMessages($this->_dbAdapter);
            $unreadMessages = $theMessages->getTheUnreadMessages($this->getUserSession()->user->id); 
            
            $this->_view->setVariable('unreadMessages', $unreadMessages);
            $this->_view->setVariable('username', $this->getUserSession()->user->username);
            $this->_view->setVariable('userId', $this->getUserSession()->user->id);
            $this->_view->setVariable('userCurrency', $this->getUserSession()->user->currency);
            $this->_view->setVariable('isAdmin', $this->getUserSession()->user->administrator);
            $this->_view->setVariable('unfundedWagerCount', $this->getUnfundedWagerCount());
        }
    }
    
    protected function wagerForm() 
    {
        $theConsoles = new TheConsoles($this->_dbAdapter);
        $theGames = new TheGames($this->_dbAdapter);
        return new WagersForm(null,$theConsoles->getAll(),$theGames->getGameConsoleId(1));
    }
    
    public function logoutAction()
    {
       $this->getUserSession();
       $this->_userSession->getManager()->getStorage()->clear('validated');
       $this->_userSession->getManager()->getStorage()->clear('user');
       $this->validateUser();
    }
  
    protected function validateUser()
    {
        $userSession = $this->getUserSession();
    
        //Check the IP Address:
        $ip = new \Application\Models\Ip();
        $userSession->usIp = $ip->checkIpAddress($_SERVER['REMOTE_ADDR']);
    
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
  
    protected function getUserSession(){
       if(!$this->_userSession){
            $this->_userSession = new Container('user');
       }
       return $this->_userSession;
    }

    protected function getUnfundedWagerCount($force = false)
    {
       if(!$this->_unfundedWagerCount || $force = true){
           $unfundedSessionVal = new Container('unfundedWagerCount');
           if(!$unfundedSessionVal->count || $force = true){
               $wagers = new Wagers($this->_dbAdapter);
               $unfundedSessionVal->count = $wagers->getUnfundedWagerCount($this->getUserSession()->user->id);
           }
           $this->_unfundedWagerCount = $unfundedSessionVal->count;
        }
        return $this->_unfundedWagerCount;
    }
}
