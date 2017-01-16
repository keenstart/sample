<?php

namespace Deploy\Models;

use Application\Models\DbalConnector;

class Deploy extends DbalConnector{
  
  public $id;
  public $deploy_data;
  
  protected $_tableAdapter = null;
  
  public function exchangeArray($data){
    $this->id = !empty($data['id']) ? $data['id'] : $this->id;
    $this->deploy_data = !empty($data['deploy_data']) ? $data['deploy_data'] : $this->deploy_data;
  }
  
  public function save(){
    $this->getDbAdapter()->saveDeployData($this);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'deploy'));
    }
    return $this->_tableAdapter;
  }
  
}