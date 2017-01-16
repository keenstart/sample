<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;

class TheTalkWallTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null){
        $this->tableGateway = $tableGateway;
    }	
    
    public function getTalk($id) 
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getAll() 
    {
        $rowset = $this->tableGateway->select();
        if(!$rowset){
            return null;
        }
        return $rowset;
    }
    

    
    public function saveTalk(TheTalkWall $talk){
        $data = array();
        foreach($talk as $key => $value){
          $data[$key] = $value;
        }

        $id = (int) $talk->id;

        if($id == 0){
          $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
          if($this->getTalk($id)){
            $this->tableGateway->update($data, array('id' => $id));
            return $id;
          } else {
            throw new \Exception('Error inserting record');

          }
        }
        return false;
    }    
}