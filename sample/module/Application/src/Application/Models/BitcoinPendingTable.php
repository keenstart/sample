<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Models\BitcoinPending;

class BitcoinPendingTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->table = 'bitcoin_pending';
    $this->tableGateway = $tableGateway;
  }
  
  public function addUpdatePending(BitcoinPending $pending){
    $data = array();
    foreach($pending as $key => $value){
      $data[$key] = $value;
    }
    
    $id = (int) $pending->id;
    
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getPendingById($id)){
        $this->tableGateway->update($data, array('id' => $id));
        return $id;
      } else{
        throw new \Exception('Error inserting record');
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
  
  public function deleteEntry($id){
    $this->tableGateway->delete(array('id' => (int) $id));
  }
  
  public function getAllUpdatedOverThreeMinutesOld(){
    $thirtyMinutesAgo = new \DateTime();
    $thirtyMinutesAgo->setTimezone(new \DateTimeZone('UTC'));
    $thirtyMinutesAgo->sub(new \DateInterval('PT3M'));
        
    $rowsets = $this->tableGateway->select(function(Select $select) use ($thirtyMinutesAgo){
      $select->where->lessThan('updated', $thirtyMinutesAgo->format('Y-m-d H:i:s'))
             ->OR->where->equalTo('amount', NULL);
    });
    
    return $rowsets;
  }
  
  public function getAllPendingByUserId($userId){
    $rowsets = $this->tableGateway->select(function(Select $select) use ($userId){
      $select->where->equalTo('administratorId', $userId);
    });
    
    return $rowsets;
  }
  
  public function getPendingById($id){
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
  
  public function getPendingByTransactionId($id){
    $rowset = $this->tableGateway->select(function(Select $select) use ($id){
      $select->where->equalTo('custom', $id);
    });
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
}