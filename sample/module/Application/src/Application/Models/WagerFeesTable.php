<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Models\WagerFees;

class WagerFeesTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->table = 'wager_fees';
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getFeeById($id){
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
  
  public function getTranferInfoByWagerAndUser($wagerId, $userId){
    $rowset = $this->tableGateway->select(function(Select $select) use ($wagerId,$userId){
      $select->where->equalTo('wagerId', $wagerId);
      $select->where->equalTo('userId', $userId);
    });
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function recordFeesTransfer(WagerFees $fees){
    $data = array();
    foreach($fees as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $fees->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getFeeById($id)){
        $this->tableGateway->update($data, array('id' => $id));
        return true;
      } else{
        throw new \Exception('Error inserting record');
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
}