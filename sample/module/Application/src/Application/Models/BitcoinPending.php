<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class BitcoinPending extends DbalConnector{
  
  public $id;
  public $administratorId;
  public $wallet;
  public $custom;
  public $amount;
  public $updated;
  
  protected $_tableAdapter;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->administratorId = (!empty($data['administratorId'])) ? $data['administratorId'] : $this->administratorId;
    $this->wallet = (!empty($data['wallet'])) ? $data['wallet'] : $this->wallet;
    $this->custom = (!empty($data['custom'])) ? $data['custom'] : $this->custom;
    $this->amount = (!empty($data['amount'])) ? $data['amount'] : $this->amount;
    $this->updated = (!empty($data['updated'])) ? $data['updated'] : $this->updated;
  }
  
  public function addUpdatePending(){
    $now = new \DateTime();
    $now->setTimezone(new \DateTimeZone('UTC'));
    $this->exchangeArray(Array('updated' => $now->format('Y-m-d H:i:s')));
    $this->getDbAdapter()->addUpdatePending($this);
  }
  
  public function getPendingToCheck(){
    return $this->getDbAdapter()->getAllUpdatedOverThreeMinutesOld();
  }
  
  public function getAllPendingByUserId($userId){
    $all = $this->getDbAdapter()->getAllPendingByUserId($userId);
    
    $value = 0;
    foreach($all as $one){
      $value += $one->amount;
    }
    
    return $value;
  }
  
  public function getByTransactionId($id){
    $pending = $this->getDbAdapter()->getPendingByTransactionId($id);
    if($pending){
      return $pending;
    } else{
      return false;
    }
  }
  
  public function updatePending($wallet, $details){
    $array = $wallet->filterDetails($details);
    $this->exchangeArray(Array('amount'=>$array['value']));
    $this->addUpdatePending();
    
    if($array['confirmed']){
      //Update the user's credits;
      $userCredits = new Credits($this->_serviceManager);
      $credits = $userCredits->getCreditsByUserId($this->administratorId);
      $credits->exchangeArray(Array('availableCredits'=>($credits->availableCredits + $this->amount)));
      if($credits->saveCredits()){
        $this->getDbAdapter()->deleteEntry($this->id);
      }
      
      //We need to add a record to the transactions table now that it is gone from the pending table;
      $wallet->recordTransaction($details);
    }
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'bitcoin_pending'));
    }
    return $this->_tableAdapter;
  }  
}