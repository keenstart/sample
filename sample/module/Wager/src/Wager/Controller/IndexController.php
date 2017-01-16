<?php 

namespace Wager\Controller;

use Wager\Controller\AbstractWagerController;

use Zend\Db\Adapter\AdapterInterface;
use Application\Models\The\TheCredits;
use Application\Models\The\TheTransactions;
use Application\Models\The\TheGames;
use Application\Models\The\TheConsoles;
use Application\Models\The\TheDispute;
use Application\Models\User;
use Wager\Form\MatchForm;
//use Wager\Models\Wagers;
use Wager\Models\Match;
use Application\Models\The\TheWagers;
use Zend\View\Model\JsonModel;
use Application\Models\Pusher\Pusher;
use Zend\Session\Container;
use Wager\Models\Email\NewWagerReceived;
use Wager\Models\Email\AcceptedWagerEmail;
use Wager\Models\Email\WagerResultConfirm;
use Wager\Models\Email\DisputeEmail;

class IndexController extends AbstractWagerController
{
    private $sessionlock;
    
    public function __construct(AdapterInterface $dbAdapter) 
    {
        parent::__construct($dbAdapter);
        $this->sessionlock = false;
    }

    public function indexAction()
    {      
      $this->validateUser();

      $theConsoles = new TheConsoles($this->_dbAdapter);
      $y = $theConsoles->getAll();
      $this->_view->setVariable('consoles', $theConsoles->getAll());
      
      $theGames = new TheGames($this->_dbAdapter);
      $this->_view->setVariable('games', $theGames->getGameConsoleId(1));      
      
      $theWagers = new TheWagers($this->_dbAdapter);
      $this->_view->setVariable('myWagers', $theWagers->getOpenWagersWithConsoleGames());

      $this->_newwagerForm = $this->wagerForm();
      $this->_view->setVariable('newwager', $this->_newwagerForm);

      return $this->_view;
    }

    public function mywagerAction()
    {
      $this->validateUser();
  
      // get new balance //
//      $theCredits = new TheCredits($this->_dbAdapter);
//      $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
//      $this->_view->setVariable('myCredits', $myCredits);
//
//      $matchForm = new MatchForm();
//      $this->_view->setVariable('matchresult', $matchForm);
      
      $theWagers = new TheWagers($this->_dbAdapter);
      $this->_view->setVariable('myWagers', $theWagers->getMyWagersWithConsoleGames());

      $this->_newwagerForm = $this->wagerForm();
      $this->_view->setVariable('newwager', $this->_newwagerForm);

      return $this->_view;
    }

    public function wagerhistoryAction()
    {
      $this->validateUser();

      // get new balance //
      $theCredits = new TheCredits($this->_dbAdapter);
      $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
      $this->_view->setVariable('myCredits', $myCredits);

      $theWagers = new TheWagers($this->_dbAdapter);
      $matchForm = new MatchForm();
      $this->_view->setVariable('matchresult', $matchForm);

      $this->_view->setVariable('myWagers', $theWagers->getMyWagersHistory());

      $this->_newwagerForm = $this->wagerForm();
      $this->_view->setVariable('newwager', $this->_newwagerForm);

      return $this->_view;
    }

    public function createwagerAction()
    {
        $this->validateUser();

        $request = $this->getRequest();
        if ($request->isPost()) {
              $theWagers = new TheWagers($this->_dbAdapter);
              $data = $request->getPost();//--fix this
              $validator = new \Zend\I18n\Validator\IsFloat();
              
              $valid = $validator->isValid($data['riskAmount']) ;
              if(!$valid) {
                $return = Array(
                  'success' => false,
                  'messages'   => 'The wager amount is invlaid.',
                );
                return new JsonModel($return);
              }              
              
              if ($valid) { //$this->_newwagerForm->isValid()

                  if($data['typeId'] != 1) { // Open Wager
                      //Get opponent Id from username 
                      $opponent = new User($this->_dbAdapter);
                      $userAccept = $opponent->getUserByUsername($data['userAccept']);
                      if(!$userAccept) {
                          $return = Array(
                            'success' => false,
                            'messages'   => 'This User not Found. Try again.',
                          );
                          return new JsonModel($return);                    
                      }

                      if($userAccept->id  == $this->getUserSession()->user->id) {
                          $return = Array(
                            'success' => false,
                            'messages'   => 'Cannot wager yourself.',
                          );
                          return new JsonModel($return);
                      }
                      $data['userAcceptId'] = $userAccept->id;
                      $wagerType = 'mywager';
                  } else {
                      $data['userAcceptId'] = null;
                      $wagerType = 'openwager';
                  }
                  //--Adjust credits --//
                  $theCredits = new TheCredits($this->_dbAdapter);
                  $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
                  if($myCredits) {
                      $myCredits = get_object_vars($myCredits);
                  }
                  $makewagar = $theCredits->holdUserCredits($myCredits,$data['riskAmount']);

                  if(!$makewagar) {
                      $return = Array(
                        'success' => false,
                        'messages'   => 'The wager amount is greater than what is available in your Wager Wall Wallet. If this problem persists, contact support@thewagerwall.com.',
                      );
                      return new JsonModel($return);
                  }

                  $wager = $theWagers->makeTheWager($data);
                  
                  //Increment User shown ID
                  $data = get_object_vars($wager);
                  $wager->wagerId = 100000 + $wager->id;
                  $data['wagerId'] = $wager->wagerId ; 
                  $theWagers->setStatusTheWager($data);


                  // -Pusher Event -//
                  $theGames = new TheGames($this->_dbAdapter);
                  $games = $theGames->getGameId($wager->gameId);

                  $theConsoles = new TheConsoles($this->_dbAdapter);
                  $consoles = $theConsoles->getConsoleId($wager->consoleId);

                  $theUser = new User($this->_dbAdapter);
                  $user = $theUser->getUser($wager->userAskId);

                  // Create transaction
                  $trandata = Array(
                      'description' => $consoles->consoleName. ' - ' . $games->gameName,
                      'wagerId' => $wager->id,
                      'type' => 'H',
                      'amount'=> 0 - $data['riskAmount'],
                      'balance'=> $makewagar
                  );
                  $this->saveTransaction($trandata, 'c');


                  //---Pusher ---//
                  $pusher = new Pusher();
                  $socket = $this->params()->fromPost('socket_id');
                  $return = array(
                      'id' => $wager->id,
                      'wagerId' => $wager->wagerId,
                      'consoleName' => $consoles->consoleName,
                      'whichConsole' => $consoles->whichConsole,
                      'gameName' => $games->gameName,
                      'username' => $user->username,
                      'riskAmount' => $wager->riskAmount,
                      'status' => $wager->status,
                      'userask' => $wager->userAskId,
                      'userAccept' => $wager->userAcceptId,
                      'typeid' => $wager->typeId,
                      'consoleUsername' => $wager->consoleUsername,
                      'askRules' => $wager->askRules,
                      'pageOn' => $wagerType,
                      'consoleUsernameAccept' => $wager->consoleUsernameAccept,
                      'created' => $wager->created,  
                      'gameStartTime' => $wager->gameStartTime,  
                      'gameResultTime' => $wager->gameResultTime,  
                  );
                  $pusher->getPusherService()->trigger('buttonevents', 'createbutton', $return, $socket);
                  
                  if($data['typeId'] != 1) {
                    $theUser = new User($this->_dbAdapter);
                    $user = $theUser->getUser($wager->userAcceptId);
                    
                    //Uri //
                    $uri = $this->getRequest()->getUri();
                    $scheme = $uri->getScheme();
                    $myhost = $uri->getHost();
                    $url = $scheme.'://'.$myhost .'/wager/index/mywager/';
                    // Sent New wager Email
                    $newWagerReceived = new NewWagerReceived($this->_dbAdapter);
                    $wagerReceived = $newWagerReceived->sendEmail($wager, $url);
                  }
                  
                  $return = array(
                    'success' => true,
                      'id' => $wager->id,
                      'wagerId' => $wager->wagerId,
                      'consoleName' => $consoles->consoleName,
                      'whichConsole' => $consoles->whichConsole,
                      'gameName' => $games->gameName,
                      'username' => $user->username,
                      'riskAmount' => $wager->riskAmount,
                      'status' => $wager->status,
                      'userask' => $wager->userAskId,
                      'userAccept' => $wager->userAcceptId,
                      'typeid' => $wager->typeId,
                      'consoleUsername' => $wager->consoleUsername,
                      'askRules' => $wager->askRules,                      
                      'credits' => $makewagar,//mycredits
                      'pageOn' => $wagerType,
                      'consoleUsernameAccept' => $wager->consoleUsernameAccept,
                      'created' => $wager->created,  
                      'gameStartTime' => $wager->gameStartTime,  
                      'gameResultTime' => $wager->gameResultTime,                      
                  );
                  
                  return new JsonModel($return);
              } 
          }  
          $return = Array(
            'success' => false,
            'messages'   => 'The wager was not successfully made. Please completed all fields.',
          );
          return new JsonModel($return);
    }
    
    public function matchAction()
    {
        $this->validateUser();

        $request = $this->getRequest();
        if ($request->isPost()) {
              $matchForm = new MatchForm();
              $theWagers = new TheWagers($this->_dbAdapter);
              $model = new Match();
              $matchForm->setInputFilter($model->getInputFilter());
              $matchForm->setData($request->getPost());
              $match =array();
              $dispute = array();
              
              if ($matchForm->isValid()) {
                  $data = $matchForm->getData();
                  $thegame = $theWagers->getWagerById($data['id']);
                  $match = get_object_vars($thegame);

                  if($this->isUser($thegame->userAskId) && !$thegame->askResult) {
                      $match['askResult'] = $data['matchresult'];
                      $match['commentAsk'] = $this->getUserSession()->user->username .': '. $data['comments'];
                  } elseif($this->isUser($thegame->userAcceptId) && !$thegame->acceptResult) {
                      $match['acceptResult'] = $data['matchresult'];
                      $match['commentAccept'] = $this->getUserSession()->user->username .': '. $data['comments'];
                  } 
                  
                  $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));//'America/New_York'
                  $match['gameResultTime'] = $dateTime->format('Y-m-d H:i:s');    //Start time                  
                  $thegame = $theWagers->setMatchResult($match);
                  
                  // Refresh the game to get update results //
                  //$thegame = $theWagers->getWagerById($data['id']);

                  // save dispute data
                  if($thegame->askResult == 3 || $thegame->acceptResult == 3) {

                      $theDispute = new TheDispute($this->_dbAdapter);
                      
                      $dispute['wagerId'] = $thegame->id;
                      $dispute['status'] = 1;
                      $dispute['linkUrl'] = $data['url'];
                      $dispute['disputeDetails'] = $data['comments'];
                      
                      $thedispute = $theDispute->makeTheDispute($dispute);
                      $dispute = get_object_vars($thedispute);
                      $dispute['disputeId'] = 100000 + $thedispute->id;
                      $dispute = $theDispute->editTheDispute($dispute);
                      
                      // Send reminder Email to player who as not confirmed match as yet.
                      //Uri //
                      $uri = $this->getRequest()->getUri();
                      $scheme = $uri->getScheme();
                      $myhost = $uri->getHost();
                      $url = $scheme.'://'.$myhost .'/wager/index/mywager/';                      
                      
                      $disputeEmail = new DisputeEmail($this->_dbAdapter);
                      $disputeemail = $disputeEmail->sendEmail($dispute, $url);                      
                      
                  }
                  
                  $theCredits = new TheCredits($this->_dbAdapter);
                  if($thegame->askResult && $thegame->acceptResult) {
                      $status = $this->matchFundsTransfer($thegame, $theWagers);

                        if($this->getUserSession()->user->id != $thegame->userAskId) {
                            $credits = $theCredits->getCreditsByUserId($thegame->userAskId);
                        } else {
                            $credits = $theCredits->getCreditsByUserId($thegame->userAcceptId);
                        }
                      
                        //--Pusher Event --//  
                        $pusher = new Pusher();
                        $data = array(
                            'id' => $thegame->id,
                            'credits' => $credits->availableCredits,
                            'userId'  => $credits->userId,
                            'status' => $status
                        );
                        $pusher->getPusherService()->trigger('buttonevents', 'matchresult', $data); 
                  } else {
                        if($this->getUserSession()->user->id != $thegame->userAskId) {
                            $userid = $thegame->userAskId;
                        } else {
                            $userid = $thegame->userAcceptId;
                        }                      
                        //--Pusher Event --//  
                        $pusher = new Pusher();
                        $data = array(
                            'id' => $thegame->id,
                            'userId'  => $userid,
                            'gameResultTime' => $thegame->gameResultTime,
                            'status' => 9
                        );
                        $pusher->getPusherService()->trigger('buttonevents', 'matchresult', $data); 
                        // Send reminder Email to player who as not confirmed match as yet.
                        $wagerResultConfirm = new WagerResultConfirm($this->_dbAdapter);
                        $wagerResult = $wagerResultConfirm->sendEmail($thegame, $this->getUserSession()->user->id);                       
                      
                  }

                 
                  $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
                   $return = Array(
                    'success' => true,
                    'id' => $data['id'],
                    //'disputeid' => $dispute->disputeId,
                    //'linkurl' => $dispute->linkUrl,
                    //'disputedetails' => $dispute->disputeDetails,   
                    'credits' => $myCredits->availableCredits,//mycredits
                   );
                   return new JsonModel($return);

              }  
          }  
            $return = Array(
                    'success' => false,
                    'messages'   => 'The Match Report was not successfully made. Please completed all fields.',
                   );
            return new JsonModel($return);
    }


    
    public function matchexpiresAction()
    {
        $this->validateUser();

        $credits = -1;
        $id = $this->params()->fromPost('wagerId');
        $theWagers = new TheWagers($this->_dbAdapter);
 
        $thegame = $theWagers->getWagerById($id);

        if($thegame->askResult != 4 || $thegame->acceptResult != 4) {
            $theTransactions = new TheTransactions($this->_dbAdapter);
            $match = array();
            $match = get_object_vars($thegame);

            if($this->isUser($thegame->userAskId) && !$thegame->askResult && $thegame->askResult != 4) {
                $match['askResult'] = 4;
                $match['commentAsk'] = $this->getUserSession()->user->username .': '. "Wager Timer out";
                if($thegame->acceptResult == 4) $match['status'] = 9;

                $theWagers->setMatchResult($match);

                //--Adjust credits --//
                $theCredits = new TheCredits($this->_dbAdapter);    
                $myCredits = $theCredits->getCreditsByUserId($thegame->userAskId);
                $myCredits = get_object_vars($myCredits);

                $userAskTran = $theTransactions->getTheTransactionByType($thegame->userAskId, $thegame->id, 'H');
                $credits = $theCredits->declareTruceCredits($myCredits, $userAskTran);
                if ($credits) {
                    $trandata = Array(
                      'userId' => $myCredits['userId'],
                      'description' => 'Wager Expired.',
                      'wagerId' => $thegame->id,
                      'type' => 'U',
                      'amount'=> $thegame->riskAmount,
                      'balance'=> $credits
                    );
                    $theTransactions->makeTheTransaction($trandata, 'x');
                }                    


            } elseif($this->isUser($thegame->userAcceptId) && !$thegame->acceptResult  && $thegame->acceptResult != 4) {
                $match['acceptResult'] = 4;
                $match['commentAccept'] =  $this->getUserSession()->user->username .': '. "Wager Timer out";
                if($thegame->askResult == 4) $match['status'] = 9;
                $theWagers->setMatchResult($match);

                //--Adjust credits --//
                $theCredits = new TheCredits($this->_dbAdapter);    
                $myCredits = $theCredits->getCreditsByUserId($thegame->userAcceptId);
                $myCredits = get_object_vars($myCredits);

                $userAcceptTran = $theTransactions->getTheTransactionByType($thegame->userAcceptId, $thegame->id, 'H');
                $credits = $theCredits->declareTruceCredits($myCredits, $userAcceptTran);
                if ($credits) {
                    $trandata = Array(
                      'userId' => $myCredits['userId'],
                      'description' => 'Wager Expired.',
                      'wagerId' => $thegame->id,
                      'type' => 'U',
                      'amount'=> $thegame->riskAmount,
                      'balance'=> $credits
                    );
                    $theTransactions->makeTheTransaction($trandata, 'x');
                }
            } 

            if($credits >= 0) {
                $return = Array(
                  'success' => true,
                  'id' => $id,
                  'type' => 9,
                  'credits' => $credits
                  );                    
            } else {
                $return = array(
                  'success' => false,
                );                    
            }

            return new JsonModel($return);
        }

        $return = array(
                'success' => false,
        );
        return new JsonModel($return);
    }
    
    public function resultexpiresAction()
    {
        $this->validateUser();

        $credits = -1;
        $winloss = false;
        $id = $this->params()->fromPost('wagerId');
        $theWagers = new TheWagers($this->_dbAdapter);
 
        $thegame = $theWagers->getWagerById($id);

        if($thegame->askResult <= 2 || $thegame->acceptResult <= 2) {
            $theTransactions = new TheTransactions($this->_dbAdapter);
            $match = array();
            $match = get_object_vars($thegame);

            if($this->isUser($thegame->userAskId)) {
                if ($thegame->askResult) {
                    if($thegame->askResult == 1) {
                        $match['askResult'] = 5; //win
                        $winloss = true;
                    } else {
                        $match['askResult'] = 6; //loose
                    }
                } else {
                    if($thegame->acceptResult == 1 || $thegame->acceptResult == 5) {
                        $match['askResult'] = 6; // loose
                    } else {
                        $match['askResult'] = 5; // win
                        $winloss = true;
                    }                    
                }
                
                //$match['askResult'] = 4;
                $match['commentAsk'] = $this->getUserSession()->user->username .': '. "Wager Timer out";
                if($thegame->acceptResult == 5 || $thegame->acceptResult == 6) $match['status'] = 9;

                $theWagers->setMatchResult($match);

                //--Adjust credits --//
                $theCredits = new TheCredits($this->_dbAdapter);    
                $myCredits = $theCredits->getCreditsByUserId($thegame->userAskId);
                $myCredits = get_object_vars($myCredits);

                
                $userAskTran = $theTransactions->getTheTransactionByType($thegame->userAskId, $thegame->id, 'H');
                if ($winloss) {   
                    $declareWinner = $theCredits->declareWinnerCredits($myCredits, $userAskTran);
                    if($declareWinner) {
                        // Create User Ask(won) unhold transaction
                        $trandata = Array(
                          'userId' => $myCredits['userId'],
                          'description' => 'Expired Unhold.',
                          'wagerId' => $thegame->id,
                          'type' => 'U',
                          'amount'=> $thegame->riskAmount,
                          'balance'=> $declareWinner
                        );
                        $theTransactions->makeTheTransaction($trandata, 'u');

                        $trandata['description']  = 'Expired Winner.';
                        $trandata['amount'] = ($thegame->riskAmount - $userAskTran->feeamt) * 2;
                        $trandata['type'] =  'W';
                        //$this->saveTransaction($trandata, 'm');
                        $theTransactions->makeTheTransaction($trandata, 'x');
                    }
                    $credits = $declareWinner;
                }

                if (!$winloss) { 
                    $declareLosser = $theCredits->declareLosserCredits($myCredits, $userAskTran);            
                    if($declareLosser) {
                        // Create User Accept(loss) unhold transaction
                        $trandata = Array(
                          'userId' => $myCredits['userId'],
                          'description' => 'Expired Unhold',
                          'wagerId' => $thegame->id,
                          'type' => 'U',
                          'amount'=> $thegame->riskAmount,
                          'balance'=> $declareLosser
                        );
                        $theTransactions->makeTheTransaction($trandata, 'u');

                        $trandata['description']  = 'Expired Looser.';
                        $trandata['amount'] = 0 - $thegame->riskAmount;
                        $trandata['type'] =  'L';
                        $theTransactions->makeTheTransaction($trandata, 'x');
                    }
                    $credits = $declareLosser;
                }
            
            } elseif($this->isUser($thegame->userAcceptId)) {
                if ($thegame->acceptResult) {
                    if($thegame->acceptResult == 1) {
                        $match['acceptResult'] = 5; //win
                        $winloss = true;
                    } else {
                        $match['acceptResult'] = 6; //loose
                    }
                } else {
                    if($thegame->askResult == 1 || $thegame->askResult == 5) {
                        $match['acceptResult'] = 6; // loose
                    } else {
                        $match['acceptResult'] = 5; // win
                        $winloss = true;
                    }                    
                }                
                
                //$match['acceptResult'] = 4;
                $match['commentAccept'] =  $this->getUserSession()->user->username .': '. "Wager Timer out";
                if($thegame->askResult == 5 || $thegame->askResult == 6) $match['status'] = 9;
                $theWagers->setMatchResult($match);

                //--Adjust credits --//
                $theCredits = new TheCredits($this->_dbAdapter);    
                $myCredits = $theCredits->getCreditsByUserId($thegame->userAcceptId);
                $myCredits = get_object_vars($myCredits);

                $userAcceptTran = $theTransactions->getTheTransactionByType($thegame->userAcceptId, $thegame->id, 'H');
                
                if ($winloss) {   
                    $declareWinner = $theCredits->declareWinnerCredits($myCredits, $userAcceptTran);
                    if($declareWinner) {
                        // Create User Ask(won) unhold transaction
                        $trandata = Array(
                          'userId' => $myCredits['userId'],
                          'description' => 'Expired Unhold.',
                          'wagerId' => $thegame->id,
                          'type' => 'U',
                          'amount'=> $thegame->riskAmount,
                          'balance'=> $declareWinner
                        );
                        $theTransactions->makeTheTransaction($trandata, 'u');

                        $trandata['description']  = 'Expired Winner.';
                        $trandata['amount'] = ($thegame->riskAmount - $userAcceptTran->feeamt) * 2;
                        $trandata['type'] =  'W';
                        //$this->saveTransaction($trandata, 'm');
                        $theTransactions->makeTheTransaction($trandata, 'x');
                    }
                    $credits = $declareWinner;
                }

                if (!$winloss) { 
                    $declareLosser = $theCredits->declareLosserCredits($myCredits, $userAskTran);            
                    if($declareLosser) {
                        // Create User Accept(loss) unhold transaction
                        $trandata = Array(
                          'userId' => $myCredits['userId'],
                          'description' => 'Expired Unhold',
                          'wagerId' => $thegame->id,
                          'type' => 'U',
                          'amount'=> $thegame->riskAmount,
                          'balance'=> $declareLosser
                        );
                        $theTransactions->makeTheTransaction($trandata, 'u');

                        $trandata['description']  = 'Expired Looser.';
                        $trandata['amount'] = 0 - $thegame->riskAmount;
                        $trandata['type'] =  'L';
                        $theTransactions->makeTheTransaction($trandata, 'x');
                    }
                    $credits = $declareLosser;
                }
            } 

            if($credits >= 0) {
                $return = Array(
                  'success' => true,
                  'id' => $id,
                  'type' => 9,
                  'credits' => $credits
                  );                    
            } else {
                $return = array(
                  'success' => false,
                );                    
            }

            return new JsonModel($return);
        }

        $return = array(
                'success' => false,
        );
        return new JsonModel($return);
    }
    
    
    protected function matchFundsTransfer($thegame, $theWagers) {
      $theCredits = new TheCredits($this->_dbAdapter);

      $askCredits = $theCredits->getCreditsByUserId($thegame->userAskId);
      if($askCredits) {
         $askCredits = get_object_vars($askCredits);
      } else {return 0;}  

      $acceptCredits = $theCredits->getCreditsByUserId($thegame->userAcceptId);
      if($acceptCredits) {
         $acceptCredits = get_object_vars($acceptCredits);
      } else {return 0;}

      
              
      // Bothside declaring themselves the winner //
      if(($thegame->askResult == 1 && $thegame->acceptResult == 1) ||
         ($thegame->askResult == 3 || $thegame->acceptResult == 3))
      {
          $match['status'] = 7; //Dispute
          $theWagers->setMatchResult($match);
          return 7;
      }

      $theTransactions = new TheTransactions($this->_dbAdapter);
      
      $userAskTran = $theTransactions->getTheTransactionByType($thegame->userAskId, $thegame->id, 'H');
      $userAcceptTran = $theTransactions->getTheTransactionByType($thegame->userAcceptId, $thegame->id, 'H');

      
      $match = get_object_vars($thegame);


      // Bothside call a truce declaring themselves looser //
      if($thegame->askResult == 2 && $thegame->acceptResult == 2) {
            $declareTruce = $theCredits->declareTruceCredits($askCredits, $userAskTran);
            if ($declareTruce) {
                // Create User Ask unhold transaction
                $trandata = Array(
                  'userId' => $askCredits['userId'],
                  'description' => 'Declared a Truces.',
                  'wagerId' => $thegame->id,
                  'type' => 'U',
                  'amount'=> $thegame->riskAmount,
                  'balance'=> $declareTruce
                );
                $theTransactions->makeTheTransaction($trandata, 'u');
            }     
            
            $declareTruce = $theCredits->declareTruceCredits($acceptCredits, $userAcceptTran);
            if ($declareTruce) {            
                // Create User Accept unhold transaction
                $trandata = Array(
                  'userId' => $acceptCredits['userId'],
                  'description' => 'Declared a Truces.',
                  'wagerId' => $thegame->id,
                  'type' => 'U',
                  'amount'=> $thegame->riskAmount,
                  'balance'=> $declareTruce
                );
                $theTransactions->makeTheTransaction($trandata, 'u');            
            }
            
           $match['status'] = 6; // It a truce
           $theWagers->setMatchResult($match);
           return 6;
      }
      
      if($thegame->askResult < $thegame->acceptResult) {
            $declareWinner = $theCredits->declareWinnerCredits($askCredits, $userAskTran);
            $declareLosser = $theCredits->declareLosserCredits($acceptCredits, $userAcceptTran);
            
            if($declareWinner) {
                // Create User Ask(won) unhold transaction
                $trandata = Array(
                  'userId' => $askCredits['userId'],
                  'description' => 'Unhold.',
                  'wagerId' => $thegame->id,
                  'type' => 'U',
                  'amount'=> $thegame->riskAmount,
                  'balance'=> $declareWinner
                );
                $theTransactions->makeTheTransaction($trandata, 'u');
                
                $trandata['description']  = 'Declared Winner.';
                $trandata['amount'] = ($thegame->riskAmount - $userAskTran->feeamt) * 2;
                $trandata['type'] =  'W';
                //$this->saveTransaction($trandata, 'm');
                $theTransactions->makeTheTransaction($trandata, 'm');
            }
            
            if($declareLosser) {
                // Create User Accept(loss) unhold transaction
                $trandata = Array(
                  'userId' => $acceptCredits['userId'],
                  'description' => 'Unhold',
                  'wagerId' => $thegame->id,
                  'type' => 'U',
                  'amount'=> $thegame->riskAmount,
                  'balance'=> $declareLosser
                );
                $theTransactions->makeTheTransaction($trandata, 'u');
                
                $trandata['description']  = 'Declared Looser.';
                $trandata['amount'] = 0 - $thegame->riskAmount;
                $trandata['type'] =  'L';
                $theTransactions->makeTheTransaction($trandata, 'm');
            }
            
      } else {
          $declareLosser = $theCredits->declareLosserCredits($askCredits, $userAskTran);
          $declareWinner = $theCredits->declareWinnerCredits($acceptCredits, $userAcceptTran);
            if($declareLosser) {
                // Create User Ask(loss) unhold transaction
                $trandata = Array(
                  'userId' => $askCredits['userId'],
                  'description' => 'Unhold.',
                  'wagerId' => $thegame->id,
                  'type' => 'U',
                  'amount'=> $thegame->riskAmount,
                  'balance'=> $declareLosser
                );
                $theTransactions->makeTheTransaction($trandata, 'u');
                
                $trandata['description']  = 'Declared Looser.';
                $trandata['amount'] = 0 - $thegame->riskAmount;
                $trandata['type'] =  'L';
                $theTransactions->makeTheTransaction($trandata, 'm');
            }
            
            if($declareWinner) {
                // Create User Accept(won) unhold transaction
                $trandata = Array(
                  'userId' => $acceptCredits['userId'],
                  'description' => 'Unhold.',
                  'wagerId' => $thegame->id,
                  'type' => 'U',
                  'amount'=> $thegame->riskAmount,
                  'balance'=> $declareWinner
                );
                $theTransactions->makeTheTransaction($trandata, 'u');
                
                $trandata['description']  = 'Declared Winner.';
                $trandata['amount'] = ($thegame->riskAmount - $userAskTran->feeamt) * 2;
                $trandata['type'] =  'W';
                $theTransactions->makeTheTransaction($trandata, 'm');
            }
      }

      $match['status'] = 5; // As a winner and looser
      $theWagers->setMatchResult($match);
      return 5;
    }

    public function acceptwagerAction()
    {
          $this->validateUser();

          $id = $this->params()->fromPost('wagerId');
          $consoleUsernameAccept = $this->params()->fromPost('consoleUsernameAccept');
          
          $theWagers = new TheWagers($this->_dbAdapter);
          $wager = $theWagers->getWagerById($id);
          
          // Active wager open //
          $activewagerAsk = $theWagers->getMyActiveWagers($wager->userAskId);
          $activewagerAccept = $theWagers->getMyActiveWagers($wager->userAcceptId);
          if($activewagerAsk || $activewagerAccept) {
                if(!$activewagerAsk && !$activewagerAccept) {
                    if($this->getUserSession()->user->id == $wager->userAskId && $activewagerAsk) {
                          $error = 'You have an open active wager. Cannot accept a new wager until you complete the current wager.';
                    } elseif ($this->getUserSession()->user->id == $wager->userAcceptId && $activewagerAccept) {
                          $error = 'You have an open active wager. Cannot accept a new wager until you complete the current wager.';
                    } else {
                        $error = 'Your opponent have an open active wager. Cannot accept the wager until your opponent complete the current wager.';
                    }
                } else {
                    $error = 'You or your opponent have an open active wager.';
                }
                $return = Array(
                  'success' => false,
                  'error' => $error,
                );
                return new JsonModel($return);              
          }

          // already have a opponent //
          if($wager->typeId == 1 && !is_null($wager->userAcceptId)) {
              $return = Array(
                'success' => true,
              );
              return new JsonModel($return);
          }
          $data = get_object_vars($wager);

          //--Adjust credits --//
          $theCredits = new TheCredits($this->_dbAdapter);
          $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
          if($myCredits) {
              $myCredits = get_object_vars($myCredits);
          }
          
          $makewagar = $theCredits->holdUserCredits($myCredits,$data['riskAmount']);

          if($makewagar) {
              if($wager->typeId == 1) { //open wager
                  $data['userAcceptId'] = $this->getUserSession()->user->id;
                  $data['typeId'] = 2; //open wager found a opponent
              }
              


              $data['status'] = 2; //match on
              
              $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));//'America/New_York'
              $data['gameStartTime'] = $dateTime->format('Y-m-d H:i:s');    //Start time
              
              $data['consoleUsernameAccept'] = $consoleUsernameAccept;
              $theWagers->setStatusTheWager($data);

              $theGames = new TheGames($this->_dbAdapter);
              $games = $theGames->getGameId($wager->gameId);

              $theConsoles = new TheConsoles($this->_dbAdapter);
              $consoles = $theConsoles->getConsoleId($wager->consoleId);

              
              // Create transaction
              $feeamt = $this->returnFee($data['riskAmount']);
              $trandata = Array(
                  'description' => $consoles->consoleName. ' - ' . $games->gameName,
                  'wagerId' => $wager->id,
                  'type' => 'H',
                  'amount'=> 0 - $data['riskAmount'],
                  'feeamt' => $feeamt,
                  'balance' => $makewagar
              );
              $this->saveTransaction($trandata,'a');
              
              $theTransactions = new TheTransactions($this->_dbAdapter);
              $userAskTran = $theTransactions->getTheTransactionByType($theWagers->userAskId, $theWagers->id, 'H');
              $userAskTran->feeamt = $feeamt;
              
              $theTransactions->saveFee(get_object_vars($userAskTran));

              // -Pusher Event -//
              $pusher = new Pusher();
              $data = array(
                    'id' => $wager->id,
                    'consoleName' => $consoles->consoleName,
                    'gameName' => $games->gameName,
                    'riskAmount' => $wager->riskAmount,
                    'status' => 2,
                    'wagerOriginator' => $wager->userAskId,
                    'credits' => $makewagar,
                    'user' => $this->getUserSession()->user->id,
                    'wagerAcceptor' => $this->getUserSession()->user->username,
                    'gameStartTime' => $dateTime->format('Y-m-d H:i:s')
              );
              $pusher->getPusherService()->trigger('buttonevents', 'acceptwager', $data);
              
              //Uri //
              $uri = $this->getRequest()->getUri();
              $scheme = $uri->getScheme();
              $myhost = $uri->getHost();
              $url = $scheme.'://'.$myhost .'/wager/index/mywager/';
              // Sent Accept wager Email
              $acceptedWagerEmail = new AcceptedWagerEmail($this->_dbAdapter);
              $acceptedWager = $acceptedWagerEmail->sendEmail($wager, $url);              
              

              $return = Array(
                'success' => true,
                'id' => $id,
                'type' => 2);       

          } else {
              $return = Array(
                'success' => false,
                'type' => 2,
                'error' => 'The wager amount is greater than what is available in your Wager Wall Wallet. If this problem persists, contact support@thewagerwall.com.'
              );
          }
          return new JsonModel($return);
               
    }
    
    protected function returnFee($amount) {
        
        $fee = $amount * 0.10;
        if($fee > 10) {
            $fee = 10;
        }
        return $fee;
    }

    public function declinewagerAction()
    {
          $this->validateUser();

          $id = $this->params()->fromPost('wagerId');

          $theWagers = new TheWagers($this->_dbAdapter);
          $wager = $theWagers->getWagerById($id);
          $data = get_object_vars($wager);
          //--Adjust credits --//
          $theCredits = new TheCredits($this->_dbAdapter);
          $myCredits = $theCredits->getCreditsByUserId($data['userAskId']);
          if($myCredits) {
              $myCredits = get_object_vars($myCredits);
          }
          $credits = $theCredits->unholdUserCredits($myCredits,$data['riskAmount']);

          $return = Array(
            'success' => false,
            'id' => $id,
            'type' => 3,
          );
          
          if($credits){
              $data['status'] = 3; //decline
              $theWagers->setStatusTheWager($data);

              // Create transaction
              $theUser = new User($this->_dbAdapter);
              $user = $theUser->getUser($wager->userAskId);
              $trandata = Array(
                  'userId' => $user->id,
                  'description' => 'Decline the Wager.',
                  'wagerId' => $wager->id,
                  'type' => 'U',
                  'amount'=> $data['riskAmount'],
                  'balance' => $credits
              );
              $this->saveTransaction($trandata, 'd');

              $return = Array(
                'success' => true,
                'id' => $id,
                'type' => 3,
              );  

              //--Pusher Event --//
              $pusher = new Pusher();
              $data = array(
                  'id' => $wager->id,
                  'credits' => $credits,
                  'userAskId' => $data['userAskId']
              );
              $pusher->getPusherService()->trigger('buttonevents', 'delcinewager', $data);

          }
          return new JsonModel($return);    
    }

    public function cancelwagerAction()
    {
          $this->validateUser();

          $id = $this->params()->fromPost('wagerId');

          $theWagers = new TheWagers($this->_dbAdapter);
          $wager = $theWagers->getWagerById($id);
          $data = get_object_vars($wager);

          //--Adjust credits --//
          $theCredits = new TheCredits($this->_dbAdapter);
          $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
          if($myCredits) {
              $myCredits = get_object_vars($myCredits);
          }
          $credits = $theCredits->unholdUserCredits($myCredits,$data['riskAmount']);
          
          $return = Array(
            'success' => false,
            'id' => $id,
            'type' => 4,
          );
          
          if($credits){
              $data['status'] = 4; //cancel
              $theWagers->setStatusTheWager($data);

              // Create transaction
              $trandata = Array(
                  'description' => 'Cancel Wager',
                  'wagerId' => $wager->id,
                  'type' => 'U',
                  'amount'=> $data['riskAmount'],
                  'balance' => $credits
              );
              $this->saveTransaction($trandata, 'x');

              //--Pusher Event --//  
              $return = Array(
                'success' => true,
                'id' => $id,
                'type' => 4,
                'credits' => $credits
                );       

              $pusher = new Pusher();
              $data = array(
                  'id' => $wager->id,
              );
              $pusher->getPusherService()->trigger('buttonevents', 'cancelwager', $data); 

          } 
        return new JsonModel($return);       
    }
    
    public function expirewagerAction()
    {
          $this->validateUser();

          $id = $this->params()->fromPost('wagerId');

          $theWagers = new TheWagers($this->_dbAdapter);
          $wager = $theWagers->getWagerById($id);
          $data = get_object_vars($wager);

          //--Adjust credits --//
          $theCredits = new TheCredits($this->_dbAdapter);
          $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
          if($myCredits) {
              $myCredits = get_object_vars($myCredits);
          }
          $credits = $theCredits->unholdUserCredits($myCredits,$data['riskAmount']);
          
          $return = Array(
            'success' => false,
            'id' => $id,
            'type' => 9,
          );
          
          if($credits) {
              $data['status'] = 9; //Expired
              $theWagers->setStatusTheWager($data);

              // Create transaction
              $trandata = Array(
                  'description' => 'Wager Expires',
                  'wagerId' => $wager->id,
                  'type' => 'U',
                  'amount'=> $data['riskAmount'],
                  'balance' => $credits
              );
              $this->saveTransaction($trandata, 'e');

              //--Pusher Event --//  
              $return = Array(
                'success' => true,
                'id' => $id,
                'type' => 9,
                'credits' => $credits
                );       

              $pusher = new Pusher();
              $data = array(
                  'id' => $wager->id,
              );
              $pusher->getPusherService()->trigger('buttonevents', 'cancelwager', $data); 

          } 
        return new JsonModel($return);       
    }
    

    public function disputeAction()
    {
          $this->validateUser();
          $id = $this->params()->fromPost('id');
          $linkurl = $this->params()->fromPost('linkurl');
          $details = $this->params()->fromPost('details');
          $wagerid = $this->params()->fromPost('wagerid');

          $theDispute = new TheDispute($this->_dbAdapter);
          $dispute = $theDispute->getTheDisputeById($id);
          $data = get_object_vars($dispute);
          $data['linkUrl'] = $linkurl;
          $data['disputeDetails'] = $details;       
          $dispute = $theDispute->editTheDispute($data);


          
          if($dispute){
              $return = Array(
                'success' => true,
                'id' => $id,
                'wagerid' => $wagerid,
                'linkurl' => $dispute->linkUrl,
                'disputedetails' => $dispute->disputeDetails
                );

          } 
        return new JsonModel($return);       
    }
    
    
    protected function saveTransaction($trandata, $prefix = null) {
        $theTransactions = new TheTransactions($this->_dbAdapter);
        $theTransactions->makeTheTransaction($trandata, $prefix);
    }

    protected function htmlCreateWager($wager) {

          $theGames = new TheGames($this->_dbAdapter);
          $games = $theGames->getGameId($wager->gameId);

          $theConsoles = new TheConsoles($this->_dbAdapter);
          $consoles = $theConsoles->getConsoleId($wager->consoleId);

          $theUser = new User($this->_dbAdapter);
          $user = $theUser->getUser($wager->userAcceptId);


          $html = "<li class='list-group-item' id='listEvtMatch" . $wager->id ."'>"   
                  . "<div> <div><h3>" . $consoles->consoleName . " - " . $games->gameName .
                " - $" . $wager->riskAmount . "</h3></div>" .
                       "<div><small>You have made a challenge</small></div>" .
                       "<div><small> Opponent:  " .  $user->username . "</small></div>" .
                  "</div> <div class='individualWager' id='buttEvtMatchResult". $wager->id . "'> ".
                  "<div id = 'wagerid' style='display:none;'>" . $wager->id . "</div>" .
          "<div role='group' aria-label='...'>" . 
          " <a id = 'pendingmatch' type='button'  value2 = " . $wager->id . 
                " class='btn btn-default' disabled='disabled'>Pending</a>" .                  
          " <a id = 'cancelmatch' type='button' value1 =  'cancelwager' value2 = " . $wager->id . 
                " class='btn btn-danger cancelWager_open' href='#cancelWager'>Cancel</a>" .                               
          "</div></div></li>";

      return $html;
    }
  
    protected function isUser($userid) 
    {
      if($this->getUserSession()->user->id == $userid) {
          return true;              
      }
      return false;
    }


  /*public function openAction()
  {
    $this->validateUser();

    $theWagers = new TheWagers($this->_dbAdapter);
    $this->_view->setVariable('openWagers', $theWagers->getOpenWagers());
    return $this->_view; 
  }*/
}