<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;

class LeaguesTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getLeagueById($id){
    $resultSet = $this->tableGateway->select(array('id' => $id));
    return $resultSet->current();
  }
  
  public function getLeagueByName($name){
    $resultSet = $this->tableGateway->select(array('leagueName' => $name));
    return $resultSet->current();
  }
  
  public function getLeagueBySportsMlId($value){
    $resultSet = $this->tableGateway->select(array('sportsMlId' => $value));
    return $resultSet->current();
  }
  
  public function getLeaguesBySportId($id){
    $resultSet = $this->tableGateway->selectAll(array('sportId' => $id));
    return $resultSet;
  }
  
  public function saveLeague(Leagues $league){
    $data = array();
    foreach($league as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $league->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getLeagueById($id)){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        throw new \Exception('Error inserting record');
      }
    }
  }
}