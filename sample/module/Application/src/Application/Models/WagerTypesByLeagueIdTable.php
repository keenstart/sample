<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class WagerTypesByLeagueIdTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getWagerTypesByLeagueId($leagueId){
    $rowset = $this->tableGateway->select(function (Select $select) use ($leagueId){
      $select->where->equalTo('leagueId', $leagueId);
    });
    return $rowset;
  }  
}