<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class TheWithdrawalTable 
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null)
    {
        $this->tableGateway = $tableGateway;
    }	
    
    public function getTheWithdrawalById($id) 
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getTheWithdrawalByTranId($theWithdrawal)
    {
        $id = (int) $theWithdrawal;
        $rowset = $this->tableGateway->select(array('transactionid' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }

    public function getTheWithdrawalUserId($userId)
    {
        
        $resultSet = $this->tableGateway->select(function(Select $select) use ($userId){
                $select->where->equalTo('userId', $userId);
        });
        return $resultSet;
    }

    public function saveTheWithdrawal(TheWithdrawal $theWithdrawal) 
    {
        $data = array();
        foreach($theWithdrawal as $key => $value){
            $data[$key] = $value;
        }
        
        $id = (int) $theWithdrawal->id;
        
        if($id == 0){
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if($this->getTheWithdrawalById($id)){
                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            } else{
                throw new \Exception('Error inserting record');
            }
        }
    }
}