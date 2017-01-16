<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;
use Zend\Session\Container;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class TheTalkWall extends DbalConnector
{
    public $id;
    public $userId;
    public $talk;
    public $channelId;
    public $ipAddress;
    public $created;
    
    protected $_tableAdapter;
    protected $_userSession;
    
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
        $this->talk = (!empty($data['talk'])) ? $data['talk'] : $this->talk;
        $this->channelId = (!empty($data['channelId'])) ? $data['channelId'] : $this->channelId;
        $this->ipAddress = (!empty($data['ipAddress'])) ? $data['ipAddress'] : $this->ipAddress;        
        $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
    }
    
    public function sendTalk($theTalkParams)
    {
       $this->exchangeArray($theTalkParams);

        $this->userId = $this->getUserSession()->user->id;

        $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));//'America/New_York'
        $this->created = $dateTime->format('Y-m-d H:i:s');

        // Capture Client Ip
        $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
        $this->ipAddress =  $remote->getIpAddress();
        
        $newMessage = $this->saveTalk();

        return $newMessage;
    }

    public function saveTalk()
    {
       $id = $this->getDbAdapter()->saveTalk($this);
       return $this->getTalkById($id);
    }
    
    public function getAll()
    {
       return $this->getDbAdapter()->getAll();
    }
    
    public function getTalkById($id)
    {
       return $this->getDbAdapter()->getTalk($id);
    }
  
    public function getTalkPage($channelId, $limit, $offset) 
    {
        $where = new Where();
        $where->equalTo('channelId', $channelId);

        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('t'=>'thetalkwall'))
                ->join(array('ur'=>'users'),'ur.id = t.userId',array('username'))
                ->where($where)
                ->order('created DESC')
                ->limit($limit) 
                ->offset($offset);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
       
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            
            $t = $resultSet->count();
            return $resultSet->toArray();
        }
    }   
                
    public function setDbAdapter($dbAdapter){
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter(){
        if(!$this->_tableAdapter){
            $this->setDbAdapter($this->setTableGateway($this, 'thetalkwall'));
        }
        return $this->_tableAdapter;
    }
    
    protected function getUserSession()
    {
        if(!$this->_userSession) {
          $this->_userSession = new Container('user');
        }
        return $this->_userSession;
    }    
}
