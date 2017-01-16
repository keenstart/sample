<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class StatsTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }

  public function getGameStats($gameId){
    $rowsets = $this->tableGateway->select(function (Select $select) use ($gameId){
      $select->where->equalTo('gameId', $gameId);
    });
    return $rowsets;
  }
  
  public function getGameStatById($id){
    $rowset = $this->tableGateway->select(function (Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    if(!$rowset->current()) return null;
    return $rowset->current();
  }
  
  public function needsInserting(\Application\Models\Stats $stats){
    $rowset = $this->tableGateway->select(function (Select $select) use ($stats){
      $select->where->equalTo('gameId', $stats->gameId);
      $select->where->equalTo('teamId', $stats->teamId);
      $select->where->equalTo('teamScore', $stats->teamScore);
    });
    if(!$rowset->current()) return true;
    return false;
  }
  
  public function saveGameStats(\Application\Models\Stats $stats){
    $data = array();
    foreach($stats as $key => $value){
      $data[$key] = $value;
    }
    
    $id = (int) $stats->id;
    
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getGameStatById($id)){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        $this->tableGateway->insert($data);
      }
    }
  }
  
  public function getLastPullTime(){
    $rowset = $this->tableGateway->select(function (Select $select){
      $select->where->equalTo('id', 1);
    });
    
    return $rowset->current();
  } 
}