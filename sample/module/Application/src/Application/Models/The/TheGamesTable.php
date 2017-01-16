<?php

namespace Application\Models\The;


use Zend\Db\TableGateway\TableGateway;

class TheGamesTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway = null){
        $this->tableGateway = $tableGateway;
    }	
    
    public function getGame($id) 
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if(!$row){
            return null;
        }
        return $row;
    }
    
    public function getGameConsoleId($consoleId)
    {
        $consoleId = (int) $consoleId;
        $rowset = $this->tableGateway->select(array('consoleId' => $consoleId));
        return $rowset;
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