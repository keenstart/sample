<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Models\Lines;

class LinesTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->table = 'lines';
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getLineById($id){
    $id = (int) $id;
    $rowset = $this->tableGateway->select(function(Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    
    $row = $rowset->current();
    
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function getLinesByGameId($gameId){
    $resultSet = $this->tableGateway->select(function(Select $select) use ($gameId){
      $select->where->equalTo('gameId', $gameId);
    });
    
    return $resultSet;
  }
  
  public function getLineByGameTeamWagerTypeId($gameId, $teamId, $wagerTypeId){
    $rowset = $this->tableGateway->select(function(Select $select) use ($gameId, $teamId, $wagerTypeId){
      $select->where->equalTo('gameId', $gameId);
      $select->where->AND->equalTo('teamId', $teamId);
      $select->where->AND->equalTo('wagerTypeId', $wagerTypeId);
    });
    
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function saveLine(Lines $line){
    $data = array();
    foreach($line as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $line->id;
    
    if($id == 0){
      if($data['value']){
        $this->tableGateway->insert($data);
      }
    } else {
      if(is_object($this->getLineById($id))){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        $this->tableGateway->insert($data);
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
}