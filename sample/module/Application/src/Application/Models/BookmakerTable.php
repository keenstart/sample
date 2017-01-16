<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Models\Bookmaker;

class BookmakerTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->table = 'book_maker';
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getBookmakerById($id){
    $id = (int) $id;
    $rowset = $this->tableGateway->select(function(Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function getBySportsMlId($sportsMlId){
    $rowset = $this->tableGateway->select(function(Select $select) use ($sportsMlId){
      $select->where->equalTo('sportsMlId', $sportsMlId);
    });
    
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function saveBookmaker(Bookmaker $bookmaker){
    $data = array();
    foreach($bookmaker as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $bookmaker->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getBookmakerById($id)){
        $this->tableGateway->update($data, array('id' => $id));
        return $id;
      } else{
        throw new \Exception('Error inserting record');
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
}