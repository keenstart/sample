<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class TheWagersTable 
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null){
        $this->tableGateway = $tableGateway;
    }	
    
    public function getTheWagerById($id) 
	{
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }

     public function getOpenWagers($userId){
        $resultSet = $this->tableGateway->select(function(Select $select) use ($userId){
            $select->join('theconsoles', 'theconsoles.id = thewagers.consoleId', array('consoleName'));
            $select->join('thegames', 'thegames.id = thewagers.gameId', array('gameName'));
            $select->join('users', 'users.id = thewagers.userAcceptId', array('username'));
            $select->where->equalTo('userAskId', $userId);
            $select->where->OR->equalTo('userAcceptId', $userId);
        });
        return $resultSet;
    }

    public function getMyWagers($userId){
        
        $resultSet = $this->tableGateway->select(function(Select $select) use ($userId){
                $select->where->equalTo('userAskId', $userId);
        });
        return $resultSet;
    }

    public function saveTheWager(TheWagers $theWager) 
    {
        $data = array();
        foreach($theWager as $key => $value){
            $data[$key] = $value;
        }
        
        $id = (int) $theWager->id;
        
        if($id == 0){
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if($this->getTheWagerById($id)){
                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            } else{
                throw new \Exception('Error inserting record');
            }
        }
    }
}