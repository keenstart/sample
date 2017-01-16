<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class Bookmaker extends DbalConnector{
  
  public $id;
  public $sportsMlId;
  public $name;
  
  protected $_tableAdapter;
    
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->sportsMlId = (!empty($data['sportsMlId'])) ? $data['sportsMlId'] : $this->sportsMlId;
    $this->name = (!empty($data['name'])) ? $data['name'] : $this->name;
  }
  
  public function getBookmaker($sportsMlId, $bookmakerName){
    $bookmaker = $this->getBySporstMlId($sportsMlId);
    if(!$bookmaker){
      $bookmaker = new Bookmaker($this->_serviceManager);
      $bookmaker->exchangeArray(Array('sportsMlId'=>$sportsMlId, 'name'=>$bookmakerName));
      $bookmaker = $bookmaker->saveBookmaker();
    }
    return $bookmaker->id;
  }
  
  public function getBookmakerById($wagerMakerId){
    return $this->getDbAdapter()->getBookmakerById($wagerMakerId);
  }
  
  public function getBySporstMlId($sportsMlId){
    return $this->getDbAdapter()->getBySportsMlId($sportsMlId);
  }
  
  public function getWagerMakerName($wagerMakerId){
    $bookmaker = $this->getBookmakerById($wagerMakerId);
    return $bookmaker->name;
  }
  
  public function saveBookmaker(){
    $this->id = $this->getDbAdapter()->saveBookmaker($this);
    return $this->getDbAdapter()->getBookmakerById($this->id);
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'book_maker'));
    }
    return $this->_tableAdapter;
  }
}