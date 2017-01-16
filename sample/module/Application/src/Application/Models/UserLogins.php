<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class UserLogins extends DbalConnector{
    
    public $id;
    public $userId;
    public $ipAddress;
    public $date;
    
    protected $_tableAdapter;
    
    public function exchangeArray($data){
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
        $this->ipAddress = (!empty($data['ipAddress'])) ? $data['ipAddress'] : $this->ipAddress;
        $this->date = (!empty($data['date'])) ? $data['date'] : $this->date;
    }
    
    public function getRecentLogins($userObject){
        return $this->getDbAdapter()->getRecentLogins($userObject->id);
    }
   
    public function login($userObject, $serverObj){
        $this->ipAddress = $this->getIpFromServerVar($serverObj);
        $this->userId = $userObject->id;
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone("UTC"));
        $this->date = $date->format('Y-m-d H:i:s');
        $this->getDbAdapter()->registerLogin($this);
    }
    
    protected function getIpFromServerVar($server){
      $ipaddress = '';
      if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
      else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
      else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
      else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
      else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
      else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
      else
        $ipaddress = 'UNKNOWN';
    
      return $ipaddress;
    }
    
    public function setDbAdapter($dbAdapter){
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter(){
        if(!$this->_tableAdapter){
            $this->setDbAdapter($this->setTableGateway($this, 'user_logins'));
        }
        return $this->_tableAdapter;
    }
}