<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class LastUpdatedTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function getLastPullForTable($table){
    $rowset = $this->tableGateway->select(function (Select $select) use ($table){
      $select->where->equalTo('update_table', $table);
    });
    
    return $rowset->current();
  } 
  
  public function updateLastPull(\Application\Models\LastUpdated $last){
    $data = array('update_table' => $last->update_table, 'last_id' => $last->last_id);
    
    $getRowForTable = $this->getLastPullForTable($last->update_table);
    
    if($getRowForTable){
      $this->tableGateway->update($data, array('id' => $getRowForTable->id));
    } else{
      $this->tableGateway->insert($data);
    }
  }
}