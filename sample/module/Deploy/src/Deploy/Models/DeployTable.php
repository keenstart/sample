<?php

namespace Deploy\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class DeployTable{
  
  protected $tableGateway;
  
  public function __construct(TableGateway $tableGateway = null){
    $this->tableGateway = $tableGateway;
  }
  
  public function getDeployById($id){
    $rowset = $this->tableGateway->select(function (Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    return $rowset->current();
  }
  
  public function saveDeployData(Deploy $deploy){
    $data = array();
    foreach($deploy as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int)$deploy->id;
    
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getDeployById($id)){
        $this->tableGateway->update($data, array('id' => $id));
      } else{
        $this->tableGateway->insert($data);
      }
      return $this->getDeployById($id);
    }
  }
  
}