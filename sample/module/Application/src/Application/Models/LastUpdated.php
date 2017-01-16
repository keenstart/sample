<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class LastUpdated extends DbalConnector{
  
  public $id;
  public $update_table;
  public $last_id;
  
  protected $_tableAdapter = null;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->update_table = (!empty($data['update_table'])) ? $data['update_table'] : $this->update_table;
    $this->last_id = (!empty($data['last_id'])) ? $data['last_id'] : $this->last_id;
  }
  
  public function getLastPull($tableName){
    return $this->getDbAdapter()->getLastPullForTable($tableName);
  }
  
  public function updateLastPull($tableName, $id){
    $this->update_table = $tableName;
    $this->last_id = $id;
    $this->getDbAdapter()->updateLastPull($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'lastPull'));
    }
    return $this->_tableAdapter;
  }
}