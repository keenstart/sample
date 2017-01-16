<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;

class TheGames extends DbalConnector
{
    public $id;
    public $consoleId;
    public $gameName;

    
    protected $_tableAdapter;
    
    public function exchangeArray($data){
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->consoleId = (!empty($data['consoleId'])) ? $data['consoleId'] : $this->consoleId;
        $this->gameName = (!empty($data['gameName'])) ? $data['gameName'] : $this->gameName;
    
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getConsoleId()
    {
        return $this->consoleName;
    }

    public function getGameName()
    {
        return $this->gameName;
    }

    public function getAll()
    {
       return $this->getDbAdapter()->getAll();
    }
    
    public function getGameId($id)
    {
       return $this->getDbAdapter()->getGame($id);
    }
    
    public function getGameConsoleId($consoleId)
    {
       return $this->getDbAdapter()->getGameConsoleId($consoleId);
    }
    
    public function setDbAdapter($dbAdapter){
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter(){
        if(!$this->_tableAdapter){
            $this->setDbAdapter($this->setTableGateway($this, 'thegames'));
        }
        return $this->_tableAdapter;
    }
}
