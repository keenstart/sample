<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class GamesTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getGameById($gameId){
    $rowset = $this->tableGateway->select(function (Select $select) use ($gameId){
      $select->where->equalTo('id', $gameId);
    });
    return $rowset->current();
  }
  
  public function getGameBySportsMlId($value){
    $rowset = $this->tableGateway->select(function (Select $select) use ($value){
      $select->where->equalTo('sportsMlId', $value);
    });
    return $rowset->current();
  }
  
  public function getAllGamesBySportsMlId($value){
    $rowsets = $this->tableGateway->select(function (Select $select) use ($value){
      $select->where->equalTo('sportsMlId', $value);
    });
    return $rowsets;
  }
  
  public function getAllGamesByTeam($teamId){
    $team = (int) $teamId;
    $rowset = $this->tableGateway->select(function (Select $select) use ($team){
      $select->where->AND->NEST->equalTo('homeTeamId', $team)
                    ->OR->equalTo('visitorTeamId', $team);
      $select->order('datetime ASC');
    });
  
    return $rowset;
  }
  
  public function saveGame(Games $game){
    $data = array();
    foreach($game as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int)$game->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getGameById($id)){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        $this->tableGateway->insert($data);
      }
      return $this->getGameById($id);
    }
  }
  
}