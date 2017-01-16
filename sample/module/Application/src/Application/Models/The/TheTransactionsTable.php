<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class TheTransactionsTable 
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null)
    {
        $this->tableGateway = $tableGateway;
    }	
    
    public function getTheTransactionById($id) 
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getTheTransactionByTranId($transactionid)
    {
        $id = (int) $transactionid;
        $rowset = $this->tableGateway->select(array('transactionid' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }

    public function getTheTransactionUserId($userId)
    {
        
        $resultSet = $this->tableGateway->select(function(Select $select) use ($userId){
                $select->where->equalTo('userId', $userId);
        });
        return $resultSet;
    }
    
    public function getTheTransactionByType($userId, $wagerId, $type)
    {
        
        $rowset = $this->tableGateway->select(function(Select $select)
                use ($userId, $wagerId, $type)
        {
                $select->where->equalTo('userId', $userId);
                $select->where->equalTo('wagerId', $wagerId);
                $select->where->equalTo('type', $type);
        });
        
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }

    public function saveTheTransaction(TheTransactions $theTransactions) 
    {
        $data = array();
        foreach($theTransactions as $key => $value){
            $data[$key] = $value;
        }
        
        $id = (int) $theTransactions->id;
        
        if($id == 0){
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if($this->getTheTransactionById($id)){
                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            } else{
                throw new \Exception('Error inserting record');
            }
        }
    }
}