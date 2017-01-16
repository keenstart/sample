<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class BitcoinTransactions extends DbalConnector{
  
  public $id;
  public $wallet;
  public $account;
  public $value;
  public $custom;
  
  protected $_tableAdapter;
    
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->wallet = (!empty($data['wallet'])) ? $data['wallet'] : $this->wallet;
    $this->account = (!empty($data['account'])) ? $data['account'] : $this->account;
    $this->value = (!empty($data['value'])) ? $data['value'] : $this->value;
    $this->custom = (!empty($data['custom'])) ? $data['custom'] : $this->custom;
  }
  
  public function getTransactionById($id){
    return $this->getDbAdapter()->getTransactionById($id);
  }
  
  public function recordTransaction(){
    return $this->getDbAdapter()->recordTransaction($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'bitcoin_transactions'));
    }
    return $this->_tableAdapter;
  }
}