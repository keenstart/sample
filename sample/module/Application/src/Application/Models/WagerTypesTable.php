<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class WagerTypesTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getWagerTypeById($id){
    $rowset = $this->tableGateway->select(function (Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    return $rowset->current();
  }
  
  public function getWagerTypeByName($name){
    $rowset = $this->tableGateway->select(function (Select $select) use ($name){
      $select->where->equalTo('type', $name);
    });
    return $rowset->current();
  }
}