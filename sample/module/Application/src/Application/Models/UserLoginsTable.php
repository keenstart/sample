<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;

class UserLoginsTable{
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway = null){
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll(){
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }
    
    public function getRecentLogins($userId){
        return $this->tableGateway->select(array('userId' => $userId));
    }
    
    public function registerLogin(UserLogins $login){
        $data = array();
        foreach($login as $key => $value){
          $data[$key] = $value;
        }
        
        $id = (int) $login->id;
        
        if($id == 0){
          $this->tableGateway->insert($data);
        } else {
          if($this->getUser($id)){
            $this->tableGateway->update($data, array('id' => $id));
          } else{
            throw new \Exception('Error inserting record');
          }
        }
    }
}