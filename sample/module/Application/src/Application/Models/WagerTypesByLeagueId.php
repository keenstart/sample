<?php

namespace Application\Models;

use Application\Models\DbalConnector;
use Application\Models\Games;
use Application\Models\Teams;

class WagerTypesByLeagueId extends DbalConnector{
  
  public $id;
  public $leagueId;
  public $wagerTypeId;
  
  protected $_tableAdapter;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->leagueId = (!empty($data['leagueId'])) ? $data['leagueId'] : $this->leagueId;
    $this->wagerTypeId = (!empty($data['wagerTypeId'])) ? $data['wagerTypeId'] : $this->wagerTypeId;
  }
  
  public function getWagerTypesByLeagueId($leagueId){
    $wagerTypes = $this->getDbAdapter()->getWagerTypesByLeagueId($leagueId);
    return $wagerTypes;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'wagertypesbyleagueid'));
    }
    return $this->_tableAdapter;
  }
}