<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class Locations extends DbalConnector{
  
  public $id;
  public $locationName;
  public $coordinates;
  
  protected $_tableAdapter;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->locationName = (!empty($data['locationName'])) ? $data['locationName'] : $this->locationName;
    $this->coordinates = (!empty($data['coordinates'])) ? $data['coordinates'] : $this->coordinates;
  }
  
  public function getLocationsByLocationIdArray($locationsArray){
    $locations = $this->getDbAdapter()->getLocationsByLocationIdArray($locationsArray);
    return $locations;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'locations'));
    }
    return $this->_tableAdapter;
  }
  
}