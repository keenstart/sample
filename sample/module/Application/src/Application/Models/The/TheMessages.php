<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;
use Zend\Session\Container;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class TheMessages extends DbalConnector
{
    public $id;
    public $touserId;
    public $fromuserId;
    public $subject;
    public $messages;
    public $isDeleted;
    public $isRead;
    public $ipAddress;
    public $created;
    
    protected $_tableAdapter;
    protected $_userSession;

    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->touserId = (!empty($data['touserId'])) ? $data['touserId'] : $this->touserId;
        $this->fromuserId = (!empty($data['fromuserId'])) ? $data['fromuserId'] : $this->fromuserId;
        $this->subject = (!empty($data['subject'])) ? $data['subject'] : $this->subject;
        $this->messages = (!empty($data['messages'])) ? $data['messages'] : $this->messages;
        $this->isDeleted = (!empty($data['isDeleted'])) ? $data['isDeleted'] : $this->isDeleted;
        $this->isRead = (!empty($data['isRead'])) ? $data['isRead'] : $this->isRead;
        $this->ipAddress = (!empty($data['ipAddress'])) ? $data['ipAddress'] : $this->ipAddress;
        $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
    }

    public function sendTheMessage($theMessagesParams)
    {
       $this->exchangeArray($theMessagesParams);

        $this->fromuserId = $this->getUserSession()->user->id;

        $this->isRead = 0; 
        $this->isDeleted = 0;

        $dateTime = new \DateTime("now", new \DateTimeZone('America/Chicago'));
        $this->created = $dateTime->format('Y-m-d H:i:s');

        // Capture Client Ip
        $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
        $this->ipAddress =  $remote->getIpAddress();
        
        $newMessage = $this->saveTheMessage();

        return $newMessage;
    }
 
    public function editTheMessage($theMessagesParams)
    {
       $this->exchangeArray($theMessagesParams);
       $newMessage = $this->saveTheMessage();

        return $newMessage;
    }
 



    public function deleteTheMessage($theMessagesParams)
    {
       $this->exchangeArray($theMessagesParams);

       $this->isDeleted = true;
       $newMessage = $this->saveTheMessage();

       return $newMessage;
    }

    public function saveTheMessage()
    {
        $id = $this->getDbAdapter()->saveTheMessage($this);
        return $this->getDbAdapter()->getTheMessageById($id);
    }

    public function getTheMessageById($id)
    {
        return $this->getDbAdapter()->getTheMessageById($id);
    }
    
    public function getTheMessageToUserId($touserId, $offset, $limit)
    {

        $where = new Where();
        $where->equalTo('touserId', $touserId);
        //$where->equalTo('isDeleted', 0);

        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('m'=>'themessages'))
                ->join(array('ur'=>'users'),'ur.id = m.fromuserId',array('username'))
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
        return $result;
    } 
    
    public function getTheMessageFromUserId($fromuserId, $offset, $limit)
    {

        $where = new Where();
        $where->equalTo('fromuserId', $fromuserId);
        //$where->equalTo('isDeleted', 0);

        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('m'=>'themessages'))
                ->join(array('ur'=>'users'),'ur.id = m.touserId',array('username'))
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
    
    public function getTheUnreadMessages($touserId)
    {

          $where = new Where();
          $where->equalTo('touserId', $touserId);
          $where->equalTo('isRead', 0);
          $where->equalTo('isDeleted', 0);

          $sql = new Sql($this->_dbAdapter);
          $select = $sql->select();
          $select->from(array('m'=>'themessages'))
                  ->join(array('ur'=>'users'),'ur.id = m.fromuserId',array('username'))
                  ->where($where)
                  ->order('created DESC');
    //               ->limit($limit) 
    //               ->offset($offset);


          $statement = $sql->prepareStatementForSqlObject($select);
          $result = $statement->execute();

          if ($result instanceof ResultInterface && $result->isQueryResult()) {
              $resultSet = new ResultSet;
              $resultSet->initialize($result);
              
              $t = $resultSet->count();
              //return $resultSet->toArray();
              return $resultSet->count();
          } 
          return $result;
    }    
    
    public function setDbAdapter($dbAdapter)
    {
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter()
    {
        if(!$this->_tableAdapter) {
            $this->setDbAdapter($this->setTableGateway($this, 'themessages'));
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
