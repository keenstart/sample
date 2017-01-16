<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class CronDbAccessTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function getGameById($gameId){
    $rowset = $this->tableGateway->select(function (Select $select) use ($gameId){
      $select->where->equalTo('id', $gameId);
    });
    return $rowset->current();
  }
  
  public function getAllStatsUpdatesSinceLastPull($lastId){
    $updated = $this->tableGateway->select(function (Select $select) use ($lastId){
      $select->where->equalTo('id', $lastId);
    });
    $lastUpdated = $updated->current();
    
    $rowsets = $this->tableGateway->select(function (Select $select) use ($lastUpdated){
      $select->where->greaterThanOrEqualTo('updated', $lastUpdated->updated);
      $select->order('updated ASC');
    });
    return $rowsets;
  }
  
  public function getAllUpdatesSinceLastPull($lastId){
    $updated = $this->tableGateway->select(function (Select $select) use ($lastId){
      $select->where->equalTo('id', $lastId);
    });
    $lastUpdated = $updated->current();
    
    $rowsets = $this->tableGateway->select(function (Select $select) use ($lastUpdated){
      $select->where->greaterThanOrEqualTo('updated', $lastUpdated->updated);
      $select->order('updated ASC');
    });
    return $rowsets;
  }
  
  public function getAllLinesUpdatesSinceLastPull($lastId){
    $updated = $this->tableGateway->select(function (Select $select) use ($lastId){
      $select->where->equalTo('id', $lastId);
    });
    $lastUpdated = $updated->current();
    
    $rowsets = $this->tableGateway->select(function (Select $select) use ($lastUpdated){
      $select->where->greaterThanOrEqualTo('updated', $lastUpdated->updated);
      $select->order('updated ASC');
    });
    return $rowsets;
  }
}