<?php

namespace Application\Models;

use Application\Models\DbalConnector;
use Application\Models\UserCoinbaseAccess;
use Application\Models\Coinbase\OAuth;
use Application\Models\Coinbase\Coinbase;

class Credits extends DbalConnector{

  public $id;
  public $userId;
  
  //AVAILABLE CREDITS IS ONLY TO BE USED FOR BITCOIN THAT WE HAVE RECIEVED FROM A TRANSFER!!!!!!!!
  //MISUSING THIS WILL RESULT IN MONEY LOST!!!!!!!!!!!!!!
  //I CAN'T STRESS ENOUGH HOW ESSENTIAL THIS IS TO UNDERSTAND!!!!!!!!!!!!!!!
  
  public $availableCredits;
  public $creditsCurrentlyWagered;

  protected $_tableAdapter;

  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
    $this->availableCredits = (isset($data['availableCredits']) && !is_null($data['availableCredits'])) ? $data['availableCredits'] : $this->availableCredits;
    $this->creditsCurrentlyWagered = (isset($data['creditsCurrentlyWagered']) && !is_null($data['creditsCurrentlyWagered'])) ? $data['creditsCurrentlyWagered'] : $this->creditsCurrentlyWagered;
  }
  
  public function adjustUserCreditsTransfer($transferAmount){
    if(!$this->userId) return false;
  
    $available = null;
  
    $this->availableCredits = $this->availableCredits - $transferAmount;
    
    $this->saveCredits();
  }
  
  public function adjustUserCreditsWager($wageringValue){
    if(!$this->userId) return false;
    
    $available = null;
        
    if($this->availableCredits >= $wageringValue || $available >= $wageringValue){
      $this->availableCredits = $this->availableCredits - $wageringValue;
      $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered + $wageringValue;
      $this->saveCredits();
    }
  }
  
  public function getCreditsByUserId($id){ 
    return $this->getDbAdapter()->getCreditsByUserId($id);
  }
  
  public function getAvailableCreditsByUserId($id){
    $credits = $this->getDbAdapter()->getCreditsByUserId($id);
    if(!$credits || !$credits->availableCredits || $credits->availableCredits == 0){
      //We need to get the user's credits based on their wallet, if declared.
      $user = new User($this->_serviceManager);
      $userObj = $user->getUser($id);
      
      $wallet = \Application\Models\Wallets\AbstractWallet::factory($userObj, $this->_serviceManager);
      if($wallet){
        $credits = $wallet->getWalletBalance($this->_serviceManager);
      } else{
        return 0;
      }
    } else{
      return $credits->availableCredits;
    }
    return $credits;
  }
  
  public function saveCredits(){
    $this->getDbAdapter()->saveCredits($this);
    return true;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'credits'));
    }
    return $this->_tableAdapter;
  }
}