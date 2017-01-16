<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Models\BitcoinTransactions;

class BitcoinTransactionsTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->table = 'bitcoin_transactions';
    $this->tableGateway = $tableGateway;
  }
  
  public function getTransactionById($id){
    $id = (int) $id;
    $rowset = $this->tableGateway->select(function(Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    
      $row = $rowset->current();
      if(!$row){
        return null;
      }
      return $row;
  }
  
  public function recordTransaction(BitcoinTransactions $transaction){
    $data = array();
    foreach($transaction as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $transaction->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getTransactionById($id)){
        $this->tableGateway->update($data, array('id' => $id));
        return $id;
      } else{
        throw new \Exception('Error inserting record');
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
}