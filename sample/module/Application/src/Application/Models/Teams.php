<?php

namespace Application\Models;

use Application\Models\DbalConnector;
use Application\Models\Games;

class Teams extends DbalConnector{

  public $id;
  public $teamName;
  public $leagueId;
  public $sportsMlId;
  public $sportsMlAbbrev;
  public $locationId;
  
  protected $_tableAdapter;

  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->teamName = (!empty($data['teamName'])) ? $data['teamName'] : $this->teamName;
    $this->leagueId = (!empty($data['leagueId'])) ? $data['leagueId'] : $this->leagueId;
    $this->sportsMlId = (!empty($data['sportsMlId'])) ? $data['sportsMlId'] : $this->sportsMlId;
    $this->sportsMlAbbrev = (!empty($data['sportsMlAbbrev'])) ? $data['sportsMlAbbrev'] : $this->sportsMlAbbrev;
    $this->locationId = (!empty($data['locationId'])) ? $data['locationId'] : $this->locationId;
  }
  
  public function getTeamById($teamId){
    $team = $this->getDbAdapter()->getTeamById($teamId);
    return $team;
  }
  
  public function getAllTeams(){
    $teams = $this->getDbAdapter()->fetchAll();
    return $teams;
  }
  
  public function getAllTeamsAlphabeticallyByLeague($leagueId){
    $teams = $this->getDbAdapter()->getTeamsAlphabeticallyByLeague($leagueId);
    return $teams;
  }
  
  public function getTeamsByGame(Games $game){
    $teamIdArray = Array($game->homeTeamId, $game->visitorTeamId);
    return $this->getAllTeamsByTeamIdArray($teamIdArray);
  }
  
  public function getAllTeamsByTeamIdArray($teamIdArray){
    $teams = $this->getDbAdapter()->getAllTeamsByTeamIdArray($teamIdArray);
    return $teams;
  }
  
  public function getTeamBySportsMlId($id){
    $team = $this->getDbAdapter()->getTeamBySportsMlId($id);
    return $team;
  }
  
  public function getTeamBySportsMlAbbrevAndLeague($abbrev, $leagueId){
    $team = $this->getDbAdapter()->getTeamBySportsMlAbbrevAndLeague($abbrev, $leagueId);
    return $team;
  }
  
  public function saveTeam(){
    $this->getDbAdapter()->saveTeam($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }

  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'teams'));
    }
    return $this->_tableAdapter;
  }
}