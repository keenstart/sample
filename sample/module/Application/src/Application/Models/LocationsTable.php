<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class LocationsTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getLocationsByLocationIdArray($locationsArray){
    $rowset = $this->tableGateway->select(function (Select $select) use ($locationsArray){
      $select->where->in('id', $locationsArray);
    });
    
    return $rowset;
  }
  
}