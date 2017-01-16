<?php

namespace Application\Models\The;

use Application\Models\The\TheTransactions;
use Application\Models\DbalConnector;

class TheCredits extends DbalConnector{

    public $id;
    public $userId;
    public $availableCredits;
    public $creditsCurrentlyWagered;

    protected $_tableAdapter;

    public function exchangeArray($data){
      $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
      $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
      $this->availableCredits = (isset($data['availableCredits']) && !is_null($data['availableCredits'])) ? $data['availableCredits'] : $this->availableCredits;
      $this->creditsCurrentlyWagered = (isset($data['creditsCurrentlyWagered']) && !is_null($data['creditsCurrentlyWagered'])) ? $data['creditsCurrentlyWagered'] : $this->creditsCurrentlyWagered;
    }
    //Add or increase credit
    public function adjustUserCreditsDeposit($myCredits, $depositAmount, $userId) {
      if(is_null($this->availableCredits)) $this->availableCredits = 0;
      if(is_null($this->creditsCurrentlyWagered)) $this->creditsCurrentlyWagered = 0;
      if(is_null($this->userId)) $this->userId = $userId;
      
      if($myCredits) $this->exchangeArray($myCredits);

      $this->availableCredits = $this->availableCredits + $depositAmount;

      if($this->saveCredits()) {
          return $this->availableCredits;
      }
      return false;
    }
    // 
    public function holdUserCredits($myCredits, $holdAmount) {
      if(is_null($this->availableCredits)) $this->availableCredits = 0;
      if(is_null($this->creditsCurrentlyWagered)) $this->creditsCurrentlyWagered = 0;
      
      if($myCredits) $this->exchangeArray($myCredits);
      
      if($this->availableCredits < $holdAmount){ return false;}
      
      $this->availableCredits = $this->availableCredits - $holdAmount;
      $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered + $holdAmount;
      if($this->saveCredits()) {
            return $this->availableCredits;
      }
      return false;
    }
  
    public function unholdUserCredits($myCredits, $unholdAmount) {
      if(is_null($this->availableCredits)) $this->availableCredits = 0;
      if(is_null($this->creditsCurrentlyWagered)) $this->creditsCurrentlyWagered = 0;
      
      if($myCredits) $this->exchangeArray($myCredits);
      
      $this->availableCredits = $this->availableCredits + $unholdAmount;
      $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered - $unholdAmount;

      if($this->saveCredits()) {
            return $this->availableCredits;
      }
      return false;
    }

    public function declareWinnerCredits($myCredits, TheTransactions $theTransactions) {
      if(is_null($this->availableCredits)) $this->availableCredits = 0;
      if(is_null($this->creditsCurrentlyWagered)) $this->creditsCurrentlyWagered = 0;
      
      if($myCredits) $this->exchangeArray($myCredits);
      
      $this->availableCredits = $this->availableCredits + 
              ((abs($theTransactions->amount) - $theTransactions->feeamt) * 2);
      $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered - abs($theTransactions->amount);
      
      

      if($this->saveCredits()) {
          return $this->availableCredits;
      }
      return false;
    }

    public function declareLosserCredits($myCredits, TheTransactions $theTransactions) {
      if(is_null($this->availableCredits)) $this->availableCredits = 0;
      if(is_null($this->creditsCurrentlyWagered)) $this->creditsCurrentlyWagered = 0;
      
      if($myCredits) $this->exchangeArray($myCredits);
      
      //$this->availableCredits = $this->availableCredits - $unholdAmount;
      $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered - abs($theTransactions->amount);

      if($this->saveCredits()) {
          return $this->availableCredits;
      }
      return false;
    }

    public function declareTruceCredits($myCredits, TheTransactions $theTransactions) {
      if(is_null($this->availableCredits)) $this->availableCredits = 0;
      if(is_null($this->creditsCurrentlyWagered)) $this->creditsCurrentlyWagered = 0;
      
      if($myCredits) $this->exchangeArray($myCredits);
      
      $this->availableCredits = $this->availableCredits + 
              (abs($theTransactions->amount) - $theTransactions->feeamt);
      $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered - abs($theTransactions->amount);

      if($this->saveCredits()) {
          return $this->availableCredits;
      }
      return false;
    }  

    
    public function adjustUserCreditsWithdrawal($myCredits, $withdrawalAmount){

      if($myCredits) $this->exchangeArray($myCredits);

      $this->availableCredits = $this->availableCredits - $withdrawalAmount;

      if($this->saveCredits()) {
          return $this->availableCredits;
      }
      return false;
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
      return $this->getDbAdapter()->saveCredits($this);
    }

    public function setDbAdapter($dbAdapter){
      $this->_tableAdapter = $dbAdapter;
    }

    public function getDbAdapter() 
    {
      if(!$this->_tableAdapter){
        $this->setDbAdapter($this->setTableGateway($this, 'thecredits'));
      }
      return $this->_tableAdapter;
    }
    
    
//    public function adjustUserCreditsTransfer($transferAmount){
//      if(!$this->userId) return false;
//
//      $this->availableCredits = $this->availableCredits - $transferAmount;
//
//      $this->saveCredits();
//    }
    
    

//    public function adjustUserCreditsWager($wageringValue){
//      if(!$this->userId) return false;
//
//      $available = null;
//
//      if($this->availableCredits >= $wageringValue || $available >= $wageringValue){
//        $this->availableCredits = $this->availableCredits - $wageringValue;
//        $this->creditsCurrentlyWagered = $this->creditsCurrentlyWagered + $wageringValue;
//        $this->saveCredits();
//      }
//    }
    /*
    protected function getUserSession()
    {
        if(!$this->_userSession) {
          $this->_userSession = new Container('user');
        }
        return $this->_userSession;
    }*/
}