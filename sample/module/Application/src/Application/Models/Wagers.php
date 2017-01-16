<?php

namespace Application\Models;

use Application\Models\DbalConnector;
use Application\Models\Credits;
use Application\Models\WagerTypes;
use Application\Models\Leagues;
use Application\Models\Games;
use Application\Models\Teams;
use Application\Models\User;
use Application\Models\Stats;
use Application\Models\WagerFees;
use Application\Models\BitcoinTransactions;

class Wagers extends DbalConnector{

  public $id;
  public $placerUserId;
  public $takerUserId;
  public $leagueId;
  public $gameId;
  public $favoringId;
  public $wagerTypeId;
  public $wagerValue;
  public $wagerCustom;
  public $risk;
  public $win;
  public $status;
  public $wagerWinnerId;
  public $created;
  public $updated;
  
  protected $_tableAdapter;

  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->placerUserId = (!empty($data['placerUserId'])) ? $data['placerUserId'] : $this->placerUserId;
    $this->takerUserId = (!empty($data['takerUserId'])) ? $data['takerUserId'] : $this->takerUserId;
    $this->leagueId = (!empty($data['leagueId'])) ? $data['leagueId'] : $this->leagueId;
    $this->gameId = (!empty($data['gameId'])) ? $data['gameId'] : $this->gameId;
    $this->favoringId = (!empty($data['favoringId'])) ? $data['favoringId'] : $this->favoringId;
    $this->wagerTypeId = (!empty($data['wagerTypeId'])) ? $data['wagerTypeId'] : $this->wagerTypeId;
    $this->wagerValue = (!empty($data['wagerValue'])) ? $data['wagerValue'] : $this->wagerValue;
    $this->wagerCustom = (!empty($data['wagerCustom'])) ? $data['wagerCustom'] : $this->wagerCustom;
    $this->risk = (!empty($data['risk'])) ? $data['risk'] : $this->risk;
    $this->win = (!empty($data['win'])) ? $data['win'] : $this->win;
    $this->status = (!empty($data['status'])) ? $data['status'] : $this->status;
    $this->wagerWinnerId = (!empty($data['wagerWinnerId'])) ? $data['wagerWinnerId'] : $this->wagerWinnerId;
    $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
    $this->updated = (!empty($data['updated'])) ? $data['updated'] : $this->updated;
  }
  
  public function cancelWagersByGameId($gameId){
    $wagers = $this->getDbAdapter()->getAllWagersForGame($gameId);
    
    foreach($wagers as $wager){
      $wager = $this->winToNeither($wager);
      $wager->exchangeArray(Array('status' => $this->getStatusOptionValue('gameCanceled')));
      $wager->saveWager();
    }
  }
  
  public function fundWager($id, $user){
    $wager = $this->getWager($id);
    
    //Make Sure the user has enough credits for the wager, and adjust available credits:
    $credits = new Credits($this->_serviceManager);
    $userCredits = $credits->getAvailableCreditsByUserId($user->id);
    
    $wallet = \Application\Models\Wallets\AbstractWallet::factory($user, $this->_serviceManager);
    if(!$wallet) $wallet = \Application\Models\Wallets\AbstractWallet::wagerwallFactory();
    $conversion = $wallet->getExchangeRate(strtolower($user->currency));
    
    if($wager->placerUserId === $user->id){
      $riskingInBtc = $wager->risk;
    } else{
      $riskingInBtc = $wager->win;
    }
    
    //Get the game object to make sure we aren't funding a wager for a game that is past/started.
    $gameObj = new Games();
    $game = $gameObj->getGameById($wager->gameId);
    $gameTime = $game->datetime;
    
    $now = new \DateTime();
    $now->setTimezone(new \DateTimeZone('UTC'));
    
    $currentTime = $now->format('Y-m-d H:i:s');
    
    if($userCredits && ($riskingInBtc + $conversion) <= $userCredits && $currentTime < $gameTime){
      //We need to transfer the BTC before we call it a finalized wager.
      $creditObject = $credits->getCreditsByUserId($user->id);
      $creditObject->adjustUserCreditsWager($riskingInBtc);
      
      //We need to keep our bit.
      $creditObject->adjustUserCreditsTransfer($conversion);
      
      /*
       *  The Following is not something we are doing right now:
       *
      //We need to actually move the money to the fees account
      $ourWallet = \Application\Models\Wallets\AbstractWallet::wagerwallFactory();
      $transferId = $ourWallet->transferToFees($conversion);

      $wagerFee = new WagerFees();
      $wagerFee->exchangeArray(Array(
          'wagerId' => $wager->id,
          'userId' => $user->id,
          'transferId' => $transferId
        ));
      $wagerFee->recordFeesTransfer();
      */
    
      $wager->exchangeArray(Array(
          'status' => $wager->getStatusOptionValue('funded'),
          'updated' => $currentTime
      ));
      $wager = $wager->saveWager();
      return true;
    } else{
      return false;
    }
  }
  
  public function getWager($id){
    $wager = $this->getDbAdapter()->getWagerById($id);
    return $wager;
  }
  
  public function getStatusOptionValue($option){
    $options = Array(
      'gameCanceled' => -3,
      'unmatched' => -2,
      'unfunded' => -1,
      'funded' => 1,
      'completed' => 2
    );
    return $options[$option];
  }
  
  public function getActiveWagersCountForUser($userId){
    return $this->getDbAdapter()->getActiveWagerCountForUser($userId);
  }
  
  public function getCompletedWagerCountForUser($userId){
    return $this->getDbAdapter()->getCompletedWagerCountForUser($userId);
  }
  
  public function getAllActiveWagersForWagerWall($userId = NULL, $request = NULL){
    $wagers = $this->getDbAdapter()->getAllAvailableWagers($userId, $request);
    
    return $this->processWagerResponse($wagers);
  }
  
  public function getAllActiveWagersBySortValues($userId = NULL, $request = NULL){
    $wagers = $this->getDbAdapter()->getAllAvailableWagersBySortValues($userId, $request);
    
    return $this->processWagerResponse($wagers);
  }
  
  public function getExistingWagersForGame($gameId, $userId=NULL){
    $wagers = $this->getDbAdapter()->getAllAvailableWagersForGame($gameId, $userId);
    
    return $this->processWagerResponse($wagers);
  }
  
  public function getAllCurrentWagersByUser($userId){
    $wagers = $this->getDbAdapter()->getAllCurrentWagersByUser($userId);
    
    $totalWagers = 0;
    foreach($wagers as $wager){
      if($wager->placerUserId == $userId){
        $totalWagers += $wager->risk;
      } else if($wager->takerUserId == $userId){
        $totalWagers += $wager->win;
      }
    }
    
    return $totalWagers;
  }
  
  public function getInvolvedWagers($userId){
    $wagers = $this->getDbAdapter()->getAllCurrentWagersByUser($userId);
    
    return $this->processWagerResponse($wagers);
  }
  
  public function getPastWagers($userId){
    $wagers = $this->getDbAdapter()->getAllPastWagersByUser($userId);
    
    return $this->processWagerResponse($wagers);
  }
  
  public function getAllUnfundedWagersByUser($userId){
    $wagers = $this->getDbAdapter()->getAllUnfundedWagersByUser($userId);
    return $this->processWagerResponse($wagers);
  }
  
  public function getUnfundedWagersByUser($userId){
    $wagers = $this->getDbAdapter()->getUnfundedWagersByUser($userId);
    if($wagers){
      $wagerStatus = $this->getStatusOptionValue("unfunded");
      foreach($wagers as $wager){
        if((int) $wager->status === $wagerStatus){
          return true;
        }
      }
    }
    return false;
  }
  
  public function getUnfundedWagerCount($userId){
    $wagers = $this->getDbAdapter()->getUnfundedWagerCount($userId);
    return $wagers;
  }
  
  protected function loadLinesData($linesResponse, $wagerTypes){    
    $returnArray = Array();
    foreach($linesResponse as $line){
      $returnArray[$line->teamId][$line->wagerTypeId] = Array(
          'datetime' => $line->datetime,
          'name' => $wagerTypes[intval($line->wagerTypeId)]['display'],
          'value' => $line->value,
          'valueOpening' => $line->valueOpening,
          'prediction' => $line->prediction,
          'predictionOpening' => $line->predictionOpening
      );
    }
    return $returnArray;
  }
  
  protected function processWagers($wagers){
    $gameObj = new Games($this->_serviceManager);
    foreach($wagers as $wager){
      //Load the game.  The expectation at this point is that the game is completed.
      $game = $gameObj->getGameById($wager->gameId);
      /*
      $wager->resolveWager(Array(
          'eventId' => $game->id,
          'winningTeamId' => $winningTeam,
          'winningTeamScore' => $winningScore,
          'losingTeamScore' => $losingScore));*/
    }
  }
  
  public function processAllWagers(){
    $wagers = $this->getDbAdapter()->getAllWagersByFinishedGames();
    $this->processWagers($wagers);
  }
  
  public function processAllIncompleteWagers(){
    $wagers = $this->getDbAdapter()->getAllIncompleteWagersOfFinishedGames();
    $this->processWagers($wagers);
  }

  protected function processWagerResponse($wagers){
    $games = Array();
    $leagues = Array();
    $wagerUsers = Array();
    
    $wagerReiteration = Array();
    foreach($wagers as $wager){
      if(!in_array($wager->gameId, $games)) $games[] = $wager->gameId;
      if(!in_array($wager->leagueId, $leagues)) $leagues[] = $wager->leagueId;
      if(!in_array($wager->placerUserId, $wagerUsers)) $wagerUsers[] = $wager->placerUserId;
      if(!in_array($wager->takerUserId, $wagerUsers)) $wagerUsers[] = $wager->takerUserId;
      $wagerReiteration[] = $wager;
    }
    
    //Load all users
    $wagerUserObj = new User($this->_serviceManager);
    $wagerUsersArray = Array();
    foreach($wagerUsers as $user){
      $wagerUsersArray[$user] = $wagerUserObj->getUser($user);
    }
    
    //Load all leagues
    $leagueObj = new Leagues($this->_serviceManager);
    $leaguesArray = Array();
    foreach($leagues as $league){
      $leaguesArray[$league] = $leagueObj->getLeagueById($league);
    }
    
    //Get games objects
    $gamesObj = new Games($this->_serviceManager);
    $gamesArray = Array();
    foreach($games as $game){
      $gamesArray[$game] = $gamesObj->getGameById($game);
    }
    
    //Load all teams.
    $teamsObj = new Teams($this->_serviceManager);
    $teamsArray = Array();
    foreach($gamesArray as $game){
      if(!array_key_exists($game->homeTeamId, $teamsArray)){
        $teamsArray[$game->homeTeamId] = $teamsObj->getTeamById($game->homeTeamId);
      }
      if(!array_key_exists($game->visitorTeamId, $teamsArray)){
        $teamsArray[$game->visitorTeamId] = $teamsObj->getTeamById($game->visitorTeamId);
      }
    }
    
    //Load all necessary wager types.
    $wagerTypeObj = new WagerTypes($this->_serviceManager);
    $wagerTypesArray = $wagerTypeObj->getWagerTypes();
        
    //Load all lines.
    $linesObj = new Lines($this->_serviceManager);
    $linesArray = Array();
    foreach($gamesArray as $game){
      if(!array_key_exists($game->id, $linesArray)){
        $linesArray[$game->id] = $this->loadLinesData($linesObj->getLinesByGameId($game->id), $wagerTypesArray);
      }
    }
    
    $stats = new Stats($this->_serviceManager);
    $statsArray = Array();
    foreach($gamesArray as $game){
      if(!array_key_exists($game->id, $statsArray)){
        $statsInfo = $stats->getGameStats($game->id);
        foreach($statsInfo as $stat){
          $statsArray[$game->id][$stat->teamId] = $stat->teamScore;
        }
      }
    }
    
    $returnWagersArray = Array();
    
    foreach($wagerReiteration as $wager){
      $game = $gamesArray[$wager->gameId];
      $returnGamesArray[$wager->gameId] = Array(
          'league' => $leaguesArray[$wager->leagueId]->leagueName,
          'homeTeam' => $teamsArray[$game->homeTeamId]->teamName,
          'homeTeamId' => $game->homeTeamId,
          'visitorTeam' => $teamsArray[$game->visitorTeamId]->teamName,
          'visitorTeamId' => $game->visitorTeamId,
          'date' => $game->datetime
      );
    
      $favoringTeamName = NULL;
      $opposingTeamName = NULL;
      if(isset($teamsArray[$wager->favoringId])){
        $favoringTeamName = $teamsArray[$wager->favoringId]->teamName;
        
        //Get the teams for the game:
        $homeTeam = $returnGamesArray[$wager->gameId]['homeTeamId'];
        $visitingTeam = $returnGamesArray[$wager->gameId]['visitorTeamId'];
        
        if($wager->favoringId == $homeTeam){
          $opposingTeamName = $teamsArray[$returnGamesArray[$wager->gameId]['visitorTeamId']]->teamName;
        } else{
          $opposingTeamName = $teamsArray[$returnGamesArray[$wager->gameId]['homeTeamId']]->teamName;
        }
      }
            
      $returnWagersArray[] = Array(
          'gameData' => $returnGamesArray[$wager->gameId],
          'wagerData' => Array(
              'wagerId' => $wager->id,
              'wagerDisplay' => $wagerTypeObj->getWagerTypeDisplayOptions(
                  $wagerTypesArray[$wager->wagerTypeId]['name'],
                  $wager,
                  $favoringTeamName,
                  $opposingTeamName),
              'risk' => $wager->risk,
              'win' => $wager->win,
              'final' => isset($statsArray[$wager->gameId]) ? $statsArray[$wager->gameId][$returnGamesArray[$wager->gameId]['homeTeamId']] . '-' . $statsArray[$wager->gameId][$returnGamesArray[$wager->gameId]['visitorTeamId']] : '',
              'wagerMaker' => $wagerUsersArray[$wager->placerUserId],
              'wagerTaker' => $wagerUsersArray[$wager->takerUserId],
              'wagerWinner' => $wager->wagerWinnerId
          ),
          'lineData' => $linesArray[$wager->gameId]
      );
    }
    return $returnWagersArray;
  }
  
  public function removeUntakenWagers(){
    $now = new \DateTime();
    $now->setTimeZone(new \DateTimeZone("UTC"));
    
    $untakenWagers = $this->getDbAdapter()->getUntakenWagers($now);
    foreach($untakenWagers as $wager){
      //We need to refund the money from the wager:
      $credits = new Credits($this->_serviceManager);
      $userCredits = $credits->getCreditsByUserId($wager->placerUserId);
      $userCredits->adjustUserCreditsWager(($wager->risk * -1));
      
      //And we need to give back the dollar that we transfered to the fees.
      
      //First we find out what the total that we transferred was:
      $wagerFee = new WagerFees();
      $wagerFeeObj = $wagerFee->getFeeTransferDataByWagerAndUserId($wager->id, $wager->placerUserId);
      
      if($wagerFeeObj){
        $transaction = new BitcoinTransactions($this->_serviceManager);
        $transferObj = $transaction->getTransactionById($wagerFeeObj->transferId);
        
        //If we made it this far, we need to transfer the money back to their available amount.
        $ourWallet = \Application\Models\Wallets\AbstractWallet::wagerwallFactory();
        $transferId = $ourWallet->returnFeeToUser(abs($transferObj->value));
        
        $wagerFee->exchangeArray(Array(
            'wagerId' => $wager->id,
            'userId' => $wager->placerUserId,
            'transferId' => $transferId
          ));
        $wagerFee->recordFeesTransfer();
      }
      
      $wager->exchangeArray(Array('status' => $this->getStatusOptionValue("unmatched")));
      $wager->saveWager();
    }
  }
  
  public function resolveByGame(\Application\Models\Games $game){
    //First we should check if there are any wagers to resolve before we spend all the energy resolving them.
    $wagers = new Wagers($this->_serviceManager);
    $wager = $this->getDbAdapter()->getAllWagersForGame($game->id);
    if($wager->current()){
      $gameStat = new Stats($this->_serviceManager);
      $stats = $gameStat->getGameStats($game->id);
      if($stats){
        $winningScore = null;
        $losingScore = null;
        foreach($stats as $stat){
          if($stat->teamId == $game->winnerId){
            $winningScore = $stat->teamScore;
          } else{
            $losingScore = $stat->teamScore;
          }
        }
        $this->resolveWager(Array('eventId' => $game->id, 'winningTeamId' => $game->winnerId, 'winningTeamScore' => $winningScore, 'losingTeamScore' => $losingScore));
      }
    }
  }
  
  public function resolveWager($gameDetails = Array()){
    //If we don't have the necessary info, don't try it.
    if(!isset($gameDetails['eventId']) || !isset($gameDetails['winningTeamId'])
        || !isset($gameDetails['winningTeamScore']) || !isset($gameDetails['losingTeamScore'])){
      return false;
    }
    //Find wagers for a specific event
    $wagers = $this->getDbAdapter()->getAllUnresolvedWagersByEvent($gameDetails['eventId']);
    if(!$wagers) return false;
    //We need to iterate through the wagers and pass the calculation off to the models in the leagues to calculate winners/losers
    $leagues = new Leagues($this->_serviceManager);
    $wagerTypes = new WagerTypes($this->_serviceManager);
    foreach($wagers as $wager){
      $league = $leagues->getLeagueById($wager->leagueId);
      $wagerType = $wagerTypes->getWagerTypeById($wager->wagerTypeId);
      //Pass this off to the individual leagues to process wagers 
      $wagerWinner = call_user_func_array('\Application\Models\XMLTeam\Leagues\\' . str_replace(' ', '', $league->leagueName) . '\Wagers::' . $wagerType->type,
           Array(
              $wager,
              $gameDetails['winningTeamId'],
              $gameDetails['winningTeamScore'],
              $gameDetails['losingTeamScore']
           ));
      
      //Move credits around based on the winner of the wager:
      if($wagerWinner == 'placer'){
        $wager = $this->winToPlacer($wager);
      } else if($wagerWinner == 'taker'){
        $wager = $this->winToTaker($wager);
      } else if($wagerWinner == 'tie'){
        $wager = $this->winToNeither($wager);
      }
      $wager->exchangeArray(Array('status' => $this->getStatusOptionValue('completed')));
      $wager->saveWager();
    }
  }
  
  public function saveWager(){
    $this->id = $this->getDbAdapter()->saveWager($this);
    return $this;
  }
  
  public function updateWagerStatus($wagerId, $status){
    $wager = $this->getWager($wagerId);
    $wager->status = $status;
    $this->getDbAdapter()->saveWager($wager);
    return $wager;
  }
  
  protected function winToNeither($wager){
    $credits = new Credits($this->_serviceManager);
    $placerCredits = $credits->getCreditsByUserId($wager->placerUserId);
    $placerCredits->exchangeArray(Array(
        'availableCredits' => ($placerCredits->availableCredits + $wager->risk),
        'creditsCurrentlyWagered' => ($placerCredits->creditsCurrentlyWagered - $wager->risk)
    ));
    $placerCredits->saveCredits();
    
    $takerCredits = $credits->getCreditsByUserId($wager->takerUserId);
    $takerCredits->exchangeArray(Array(
        'availableCredits' => ($takerCredits->availableCredits + $wager->win),
        'creditsCurrentlyWagered' => ($takerCredits->creditsCurrentlyWagered - $wager->win)
    ));
    $takerCredits->saveCredits();
    
    //Lastly we need to set the "winner" field to 0;
    $wager->exchangeArray(Array('wagerWinnerId' => "0"));
    return $wager;
  }
  
  protected function winToPlacer($wager){
    //Create a credit object for getting values;
    $credits = new Credits($this->_serviceManager);
    //We need to take the risk column, subtract it from the winner's creditsCurrentlyWagered, then move it to the availableCredits
    $winnerCredits = $credits->getCreditsByUserId($wager->placerUserId);
    $winnerCredits->exchangeArray(Array(
        'availableCredits' => $winnerCredits->availableCredits + $wager->risk + $wager->win,
        'creditsCurrentlyWagered' => ($winnerCredits->creditsCurrentlyWagered - $wager->risk)
    ));
    $winnerCredits->saveCredits();
    //Give the win to the right user
    $user = new User($this->_serviceManager);
    $user = $user->getUser($wager->placerUserId);
    $user->exchangeArray(Array('wins'=>$user->wins++));
    $user->saveUser();
    
    //Then we need to take the win column, subtract that value from the loser's creditsCurrentlyWagered, and move it to the availableCredits of the winner
    $loserCredits = $credits->getCreditsByUserId($wager->takerUserId);
    $loserCredits->exchangeArray(Array(
        'creditsCurrentlyWagered' => ($loserCredits->creditsCurrentlyWagered - $wager->win)
    ));
    $loserCredits->saveCredits();
    //Give the loss to the right user
    $user = $user->getUser($wager->takerUserId);
    $user->exchangeArray(Array('losses'=>$user->losses++));
    $user->saveUser();
    
    //Lastly we need to set the "winner" field to the placerUserId;
    $wager->exchangeArray(Array('wagerWinnerId' => $wager->placerUserId));
    return $wager;
  }
  
  protected function winToTaker($wager){
    echo 'Win to Taker';
    //Create a credit object for getting values;
    $credits = new Credits($this->_serviceManager);
    //We need to take the risk column, subtract it from the winner's creditsCurrentlyWagered, then move it to the availableCredits
    $winnerCredits = $credits->getCreditsByUserId($wager->takerUserId);
    $winnerCredits->exchangeArray(Array(
        'availableCredits' => ($winnerCredits->availableCredits + $wager->risk + $wager->win),
        'creditsCurrentlyWagered' => ($winnerCredits->creditsCurrentlyWagered - $wager->win)
    ));
    $winnerCredits->saveCredits();
    //Give the win to the right user
    $user = new User($this->_serviceManager);
    $user = $user->getUser($wager->takerUserId);
    $user->exchangeArray(Array('wins'=>$user->wins++));
    $user->saveUser();
    //Then we need to take the win column, subtract that value from the loser's creditsCurrentlyWagered, and move it to the availableCredits of the winner
    $loserCredits = $credits->getCreditsByUserId($wager->placerUserId);
    $loserCredits->exchangeArray(Array(
        'creditsCurrentlyWagered' => ($loserCredits->creditsCurrentlyWagered - $wager->risk)
    ));
    $loserCredits->saveCredits();
    //Give the loss to the right user
    $user = $user->getUser($wager->placerUserId);
    $user->exchangeArray(Array('losses'=>$user->losses++));
    $user->saveUser();
    
    //Lastly we need to set the "winner" field to the takerUserId;
    $wager->exchangeArray(Array('wagerWinnerId' => $wager->takerUserId));
    return $wager;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }

  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'wagers'));
    }
    return $this->_tableAdapter;
  }
}