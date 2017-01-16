<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class WagerFees extends DbalConnector{
  
  public $id;
  public $wagerId;
  public $userId;
  public $transferId;
  
  protected $_tableAdapter;
    
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->wagerId = (!empty($data['wagerId'])) ? $data['wagerId'] : $this->wagerId;
    $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
    $this->transferId = (!empty($data['transferId'])) ? $data['transferId'] : $this->transferId;
  }
  
  public function getFeeTransferDataByWagerAndUserId($wagerId, $userId){
    $transfer = $this->getDbAdapter()->getTranferInfoByWagerAndUser($wagerId, $userId);
    return $transfer;
  }
  
  public function recordFeesTransfer(){
    $this->getDbAdapter()->recordFeesTransfer($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'wager_fees'));
    }
    return $this->_tableAdapter;
  }
}