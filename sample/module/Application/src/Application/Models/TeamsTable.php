<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class TeamsTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getTeamById($teamId){
    $rowset = $this->tableGateway->select(function (Select $select) use ($teamId){
      $select->where->equalTo('id', $teamId);
    });
    return $rowset->current();
  }
  
  public function getTeamBySportsMlId($id){
    $rowset = $this->tableGateway->select(function (Select $select) use ($id){
      $select->where->equalTo('sportsMlId', $id);
    });
    return $rowset->current();
  }
  
  public function getTeamBySportsMlAbbrevAndLeague($abbrev, $leagueId){
    $rowset = $this->tableGateway->select(function (Select $select) use ($abbrev, $leagueId){
      $select->where->equalTo('sportsMlAbbrev', $abbrev);
      $select->where->equalTo('leagueId', $leagueId);
    });
    return $rowset->current();
  }
  
  public function getTeamsAlphabeticallyByLeague($leagueId){
    $league = (int) $leagueId;
    $rowset = $this->tableGateway->select(function (Select $select) use ($league){
      $select->where->equalTo('leagueId', $league);
      $select->order('teamName ASC');
    });
    
    return $rowset;
  }
  
  public function getAllTeamsByTeamIdArray($teamArray){
    $rowset = $this->tableGateway->select(function (Select $select) use ($teamArray){
      $select->where->in('id', $teamArray);
    });
  
    return $rowset;
  }
  
  public function saveTeam(Teams $team){
    $data = array();
    foreach($team as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int)$team->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getTeamById($id)){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        throw new \Exception('Error inserting record');
      }
    }
  }
}