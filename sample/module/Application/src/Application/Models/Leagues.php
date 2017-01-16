<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class Leagues extends DbalConnector{

  public $id;
  public $leagueName;
  public $sportsMlId;
  public $sportId;

  protected $_tableAdapter;

  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->leagueName = (!empty($data['leagueName'])) ? $data['leagueName'] : $this->leagueName;
    $this->sportsMlId = (!empty($data['sportsMlId'])) ? $data['sportsMlId'] : $this->sportsMlId;
    $this->sportId = (!empty($data['sportId'])) ? $data['sportId'] : $this->sportId;
  }
  
  public function getAllLeagues(){
    $leagues = $this->getDbAdapter()->fetchAll();
    return $leagues;
  }
  
  public function getLeagueById($id){
    return $this->getDbAdapter()->getLeagueById($id);
  }
  
  public function getLeagueByName($name){
    return $this->getDbAdapter()->getLeagueByName($name);
  }

  public function getLeagueBySportsMlId($value){
    return $this->getDbAdapter()->getLeagueBySportsMlId($value);
  }
  
  public function getLeaguesBySportId($id){
    return $this->getDbAdapter()->getLeaguesBySportId($id);
  }
  
  public function saveLeague(){
    $this->getDbAdapter()->saveLeague($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }

  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'leagues'));
    }
    return $this->_tableAdapter;
  }
}