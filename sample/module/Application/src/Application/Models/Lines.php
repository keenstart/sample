<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class Lines extends DbalConnector{
  
  public $id;
  public $gameId;
  public $teamId;
  public $wagerTypeId;
  public $datetime;
  public $value;
  public $valueOpening;
  public $prediction;
  public $predictionOpening;
  public $wagerMakerId;
  public $updated;
  
  protected $_tableAdapter;
    
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->gameId = (!empty($data['gameId'])) ? $data['gameId'] : $this->gameId;
    $this->teamId = (!empty($data['teamId'])) ? $data['teamId'] : $this->teamId;
    $this->wagerTypeId = (!empty($data['wagerTypeId'])) ? $data['wagerTypeId'] : $this->wagerTypeId;
    $this->datetime = (!empty($data['datetime'])) ? $data['datetime'] : $this->datetime;
    $this->value = (!empty($data['value'])) ? $data['value'] : $this->value;
    $this->valueOpening = (!empty($data['valueOpening'])) ? $data['valueOpening'] : $this->valueOpening;
    $this->prediction = (!empty($data['prediction'])) ? $data['prediction'] : $this->prediction;
    $this->predictionOpening = (!empty($data['predictionOpening'])) ? $data['predictionOpening'] : $this->predictionOpening;
    $this->wagerMakerId = (!empty($data['wagerMakerId'])) ? $data['wagerMakerId'] : $this->wagerMakerId;
    $now = new \DateTime();
    $now = $now->setTimeZone(new \DateTimeZone('UTC'));
    $this->updated = (!empty($data['updated'])) ? $data['updated'] : $now->format('Y-m-d H:i:s');
  }
  
  public function getLineByGameTeamWagerTypeId($gameId, $teamId, $wagerTypeId){
    return $this->getDbAdapter()->getLineByGameTeamWagerTypeId($gameId, $teamId, $wagerTypeId);
  }
  
  public function getLinesByGameId($gameId){
    return $this->getDbAdapter()->getLinesByGameId($gameId);
  }
  
  public function saveLine(){
    $this->id = $this->getDbAdapter()->saveLine($this);
    return $this;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'lines'));
    }
    return $this->_tableAdapter;
  }
}