<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class TheMessagesTable 
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null)
    {
        $this->tableGateway = $tableGateway;
    }	
    
    public function getTheMessageById($id) 
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getTheMessageToUserId($touserId, $offset, $limit = 2)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($touserId, $offset, $limit) {
                $select->join('users', 'users.id = themessages.touserId', array('username'));
                $select->where->equalTo('touserId', $touserId);
                $select->limit($limit); 
                $select->offset($offset);
                $select->order('created ASC');
        });
        return $resultSet;
    }
    
    public function getTheMessageFromUserId($fromuserId, $offset, $limit = 2)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($fromuserId, $offset, $limit) {
                $select->join('users', 'users.id = themessages.fromuserId', array('username'));
                $select->where->equalTo('fromuserId', $fromuserId);
                $select->limit($limit); 
                $select->offset($offset);
                $select->order('created ASC');
        });
        return $resultSet;
    }   

    public function saveTheMessage(TheMessages $theMessage) 
    {
        $data = array();
        foreach($theMessage as $key => $value) {
            $data[$key] = $value;
        }
        
        $id = (int) $theMessage->id;
        
        if($id == 0){
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if($this->getTheMessageById($id)){
                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            } else{
                throw new \Exception('Error inserting record');
            }
        }
    }
}