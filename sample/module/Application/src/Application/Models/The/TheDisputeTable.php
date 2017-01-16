<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class TheDisputeTable 
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null)
    {
        $this->tableGateway = $tableGateway;
    }	
    
    public function getTheDisputeById($id) 
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getTheDisputeByDisputeId($theDispute)
    {
        $id = (int) $theDispute;
        $rowset = $this->tableGateway->select(array('disputeId' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }

    public function getTheDisputeWagerId($wagerId)
    {
        $wagerId = (int) $wagerId;
        $rowset = $this->tableGateway->select(array('wagerId' => $wagerId));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }

    public function getTheDisputeUserId($userId)
    {
        
        $resultSet = $this->tableGateway->select(function(Select $select) use ($userId){
                $select->where->equalTo('userId', $userId);
        });
        return $resultSet;
    }
    
    public function saveTheDispute(TheDispute $theDispute) 
    {
        $data = array();
        foreach($theDispute as $key => $value) {
            $data[$key] = $value;
        }
        
        $id = (int) $theDispute->id;
        
        if($id == 0){
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if($this->getTheDisputeById($id)){
                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            } else{
                throw new \Exception('Error inserting record');
            }
        }
    }
}