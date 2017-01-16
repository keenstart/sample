<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;

class TheConsoles extends DbalConnector
{
    public $id;
    public $consoleName;
    public $whichConsole;
    
    protected $_tableAdapter;
    
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->consoleName = (!empty($data['consoleName'])) ? $data['consoleName'] : $this->consoleName; 
        $this->whichConsole = (!empty($data['whichConsole'])) ? $data['whichConsole'] : $this->whichConsole; 
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getConsoleName()
    {
        return $this->consoleName;
    }

    public function getAll()
    {
       return $this->getDbAdapter()->getAll();
    }
    
    public function getConsoleId($id)
    {
       return $this->getDbAdapter()->getConsole($id);
    }
    
    public function setDbAdapter($dbAdapter){
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter(){
        if(!$this->_tableAdapter){
            $this->setDbAdapter($this->setTableGateway($this, 'theconsoles'));
        }
        return $this->_tableAdapter;
    }
}
