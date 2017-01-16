<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Application\Models\UserCoinbaseAccess;

class UserCoinbaseAccessTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getIdByUserId($userId){
    $rowset = $this->tableGateway->select(array('userId' => $userId));
    
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function getAccessTokenById($id){
    $id = (int) $id;
    $rowset = $this->tableGateway->select(array('id' => $id));
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function saveAccessToken(UserCoinbaseAccess $accessToken){
    $data = array();
    foreach($accessToken as $key => $value){
      $data[$key] = $value;
    }

    $id = (int) $accessToken->id;

    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getAccessTokenById($id)){
        $this->tableGateway->update($data, array('id' => $id));
        return true;
      } else{
        throw new \Exception('Error inserting record');
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
}