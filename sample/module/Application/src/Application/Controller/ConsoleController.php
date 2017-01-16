<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Models\XMLTeam\Feed;
use Zend\Console\Request as ConsoleRequest;
use Application\Models\Wagers;
use Application\Models\CronDbAccess;
use Application\Models\LastUpdated;
use Application\Models\BitcoinPending;
use Application\Models\User;

class ConsoleController extends AbstractActionController{
  
  public function checkbitcointransfersAction(){
    $request = $this->getRequest();
    if (!$request instanceof ConsoleRequest) {
      throw new \RuntimeException('You can only use this action from a console!');
    }
  
    $pending = new BitcoinPending($this->getServiceLocator());
    $pendingToCheck = $pending->getPendingToCheck();
    
    foreach($pendingToCheck as $pend){
      //We need to get the wallet object for the user in question.
      $user = new User($this->getServiceLocator());
      $userObject = $user->getUser($pend->administratorId);
    
      if($userObject->walletModel){
        $wallet = \Application\Models\Wallets\AbstractWallet::factory($userObject, $this->getServiceLocator());
    
        $details = $wallet->getDetails($pend->custom);
    
        $pend->updatePending($wallet, $details);
      }
    }
  }
  
  public function processwagersAction(){
    $request = $this->getRequest();
    
    // Make sure that we are running in a console and the user has not tricked our
    // application into running this action from a public web server.
    if (!$request instanceof ConsoleRequest) {
      throw new \RuntimeException('You can only use this action from a console!');
    }
    
    $wagers = new Wagers($this->getServiceLocator());
    
    if($request->getParam('all')){
      $wagers->processAllWagers();
    } else{
      $wagers->processAllIncompleteWagers();
    }
  }
  
  public function updatefeedAction(){
    $request = $this->getRequest();
    
    // Make sure that we are running in a console and the user has not tricked our
    // application into running this action from a public web server.
    if (!$request instanceof ConsoleRequest) {
      throw new \RuntimeException('You can only use this action from a console!');
    }
    
    $requestArray = Array();
    
    if($request->getParam('start')){
      $requestArray['start'] = $request->getParam('start');
    }
    
    if($request->getParam('slice')){
      $requestArray['slice'] = $request->getParam('slice');
    }
    
    if($request->getParam('loadfroms3')){
      $xml = new Feed($this->getServiceLocator());
      
      if($request->getParam('startDate')){
        $requestArray['startDate'] = $request->getParam('startDate');
      }
      
      if($request->getParam('endDate')){
        $requestArray['endDate'] = $request->getParam('endDate');
      }
      
      //Replace "/" with "_" to make it easier to find
      if($request->getParam('specificFile')){
        $requestArray['specificFile'] = $request->getParam('specificFile');
      }
      
      $getFeed = $xml->getFeedFromAs3($requestArray);
      
    } else{
      $xml = new Feed($this->getServiceLocator());
      $getFeed = $xml->getFeedDocuments($requestArray);
      
    }
  }
  
  public function updateuntakenwagersAction(){
    
    $request = $this->getRequest();
    
    // Make sure that we are running in a console and the user has not tricked our
    // application into running this action from a public web server.
    if (!$request instanceof ConsoleRequest) {
      throw new \RuntimeException('You can only use this action from a console!');
    }
    
    if(!$request->getParam('env')){
      throw new \RuntimeException('You must establish an environment to run this function!');
    }
    
    $wagers = new Wagers($this->getServiceLocator());
    $wagers->removeUntakenWagers();
  }
  
  public function retrievefromcronAction(){
    $request = $this->getRequest();
    
    // Make sure that we are running in a console and the user has not tricked our
    // application into running this action from a public web server.
    if (!$request instanceof ConsoleRequest) {
      throw new \RuntimeException('You can only use this action from a console!');
    }
     
    //Create an instance of a DB adapter for connecting with the cron server
    $serviceLocator = $this->getServiceLocator();
    $adapter = $serviceLocator->get('db_cron');
    
    //Select data from the stats and games table.
    $statsModel = new CronDbAccess($adapter, 'stats');
    $gamesModel = new CronDbAccess($adapter, 'games');
    $linesModel = new CronDbAccess($adapter, 'lines');
    
    //Now we get the record for the last time we checked the database so that everything we pull is after that time.
    $getLastUpdate = new LastUpdated($this->getServiceLocator());
    $lastPull = $getLastUpdate->getLastPull('stats');
    
    //First we pull all the stats so that the data to process will be available
    $statsUpdates = $statsModel->getAllStatsUpdatesSinceLastPull($lastPull->last_id);
    $getLastUpdate->updateLastPull('stats', $statsUpdates);
    
    $getLastGamesUpdate = new LastUpdated($this->getServiceLocator());
    $lastPull = $getLastGamesUpdate->getLastPull('games');
    //Now we make a request of all elements in the games table that have been updated since the last pull.
    $gamesUpdates = $gamesModel->getAllUpdatesSinceLastPull($lastPull->last_id);
    
    $getLastGamesUpdate->updateLastPull('games', $gamesUpdates);
    
    $getLastLinesUpdate = new LastUpdated($this->getServiceLocator());
    $lastPull = $getLastLinesUpdate->getLastPull('lines');
    //Now we pull all the lines that have been updated since the last pull.
    $linesUpdates = $linesModel->getAllLinesUpdatesSinceLastPull($lastPull->last_id);
    
    $getLastLinesUpdate->updateLastPull('lines', $linesUpdates);
  }

}