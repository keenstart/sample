<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;

class TheChannelsTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null){
        $this->tableGateway = $tableGateway;
    }	
    
    public function getChannel($id) 
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
}