<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class Games extends DbalConnector{
  
  public $id;
  public $sportsMlId;
  public $leagueId;
  public $locationId;
  public $homeTeamId;
  public $visitorTeamId;
  public $datetime;
  public $gameStatus;
  public $winnerId;
  public $updated;
  
  protected $_tableAdapter = null;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->sportsMlId = (!empty($data['sportsMlId'])) ? $data['sportsMlId'] : $this->sportsMlId;
    $this->leagueId = (!empty($data['leagueId'])) ? $data['leagueId'] : $this->leagueId;
    $this->locationId = (!empty($data['locationId'])) ? $data['locationId'] : $this->locationId;
    $this->homeTeamId = (!empty($data['homeTeamId'])) ? $data['homeTeamId'] : $this->homeTeamId;
    $this->visitorTeamId = (!empty($data['visitorTeamId'])) ? $data['visitorTeamId'] : $this->visitorTeamId;
    $this->datetime = (!empty($data['datetime'])) ? $data['datetime'] : $this->datetime;
    $this->gameStatus = (!empty($data['gameStatus'])) ? intval($data['gameStatus']) : $this->gameStatus;
    $this->winnerId = (!empty($data['winnerId'])) ? $data['winnerId'] : $this->winnerId;
    $this->updated = (!empty($data['updated'])) ? $data['updated'] : $this->updated;
  }
  
  public function getAllGames(){
    $games = $this->getDbAdapter()->fetchAll();
    return $games;
  }
  
  public function getGameById($gameId){
    $game = $this->getDbAdapter()->getGameById($gameId);
    return $game;
  }
  
  public function getGameBySportsMlId($sportsMlId){
    $game = $this->getDbAdapter()->getGameBySportsMlId($sportsMlId);
    return $game;
  }
  
  //This function exists in the rare instance that we have two entries for the same game:
  public function getAllGamesBySportsMlId($sportsMlId){
    $games = $this->getDbAdapter()->getAllGamesBySportsMlId($sportsMlId);
    $returnArray = Array();
    foreach($games as $game){
      $returnArray[] = $game;
    }
    return $returnArray;
  }
  
  public function getAllGamesByTeam($teamId){
    $games = $this->getDbAdapter()->getAllGamesByTeam($teamId);
    return $games;
  }
  
  public function saveGame(){
    return $this->getDbAdapter()->saveGame($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'games'));
    }
    return $this->_tableAdapter;
  }
  
}