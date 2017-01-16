<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;

class CreditsTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getCreditsById($id){
    $resultSet = $this->tableGateway->select(array('id' => $id));
    return $resultSet->current();
  }
  
  public function getCreditsByUserId($id){
    $resultSet = $this->tableGateway->select(array('userId' => $id));
    return $resultSet->current();
  }
  
  public function saveCredits(Credits $credits){
    $data = array();
    foreach($credits as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $credits->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getCreditsById($id)){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        throw new \Exception('Error inserting record');
      }
    }
  }
}