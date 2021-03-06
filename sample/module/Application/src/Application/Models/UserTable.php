<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;

class UserTable{
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway = null){
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll(){
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }
    
    public function getUser($id){
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getUserByEmail($email){
        $rowset = $this->tableGateway->select(array('email' => $email));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getUserByUsername($username){
      $rowset = $this->tableGateway->select(array('username' => $username));
      $row = $rowset->current();
      if(!$row){
        return null;
      }
      return $row;
    }
    
    public function saveUser(User $user){
        $data = array();
        foreach($user as $key => $value){
            $data[$key] = $value;
        }
        
        $id = (int) $user->id;
        
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
    
    public function deleteUser($id){
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}