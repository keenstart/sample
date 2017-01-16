<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;

class UserActionTokensTable{
    
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway){
        $this->tableGateway = $tableGateway;
    }
    
    public function deleteEntry($id){
      $this->tableGateway->delete(array('id' => (int) $id));
    }
    
    public function getEntry($id){
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
          return null;
        }
        return $row;
    }
    
    public function getEntryByToken($token){
        $rowset = $this->tableGateway->select(array('token' => $token));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function fetchAll(){
      $resultSet = $this->tableGateway->select();
      return $resultSet;
    }
    
    public function saveEntry(UserActionTokens $token){
      $data = array();
      foreach($token as $key => $value){
          $data[$key] = $value;
      }
    
      $id = (int) $token->id;
    
      if($id == 0){
        $this->tableGateway->insert($data);
      } else {
        if($this->getEntry($id)){
          $this->tableGateway->update($data, array('id' => $id));
        } else{
          throw new \Exception('Error inserting record');
        }
      }
    }
}