<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;

class TheChannels extends DbalConnector
{
    public $id;
    public $channel;
 
    
    protected $_tableAdapter;
    
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->channel = (!empty($data['channel'])) ? $data['channel'] : $this->channel;     
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getChannel()
    {
        return $this->channel;
    }

    public function getAll()
    {
       return $this->getDbAdapter()->getAll();
    }
    
    public function getChannelById($id)
    {
       return $this->getDbAdapter()->getChannel($id);
    }
    
    public function setDbAdapter($dbAdapter){
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter(){
        if(!$this->_tableAdapter){
            $this->setDbAdapter($this->setTableGateway($this, 'thechannels'));
        }
        return $this->_tableAdapter;
    }
}
