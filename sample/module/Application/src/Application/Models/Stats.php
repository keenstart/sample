<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class Stats extends DbalConnector{
  
  public $id;
  public $gameId;
  public $teamId;
  public $teamScore;
  public $updated;
  
  protected $_tableAdapter = null;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->gameId = (!empty($data['gameId'])) ? $data['gameId'] : $this->gameId;
    $this->teamId = (!empty($data['teamId'])) ? $data['teamId'] : $this->teamId;
    $this->teamScore = (!empty($data['teamScore'])) ? $data['teamScore'] : $this->teamScore;
    $now = new \DateTime();
    $now->setTimezone(new \DateTimeZone('UTC'));
    $this->updated = (!empty($data['updated'])) ? $data['updated'] : $now->format('Y-m-d H:i:s'); 
  }
  
  public function getGameStats($gameId){
    return $this->getDbAdapter()->getGameStats($gameId);
  }
  
  public function saveGameStats(){
    if($this->getDbAdapter()->needsInserting($this)){
      $this->getDbAdapter()->saveGameStats($this);
    }
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'stats'));
    }
    return $this->_tableAdapter;
  }
}