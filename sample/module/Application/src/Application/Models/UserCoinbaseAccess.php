<?php

namespace Application\Models;

use Application\Models\DbalConnector;

class UserCoinbaseAccess extends DbalConnector{

  public $id;
  public $userId;
  public $accessToken;
  public $refreshToken;
  public $expires;

  protected $_tableAdapter;


  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
    $this->accessToken = (!empty($data['accessToken'])) ? $data['accessToken'] : $this->accessToken;
    $this->refreshToken = (!empty($data['refreshToken'])) ? $data['refreshToken'] : $this->refreshToken;
    $this->expires = (!empty($data['expires'])) ? $data['expires'] : $this->expires;
  }
  
  public function getTokens($userId){
    $coinbaseAccess = $this->getDbAdapter()->getIdByUserId($userId);
    if($coinbaseAccess){
      $tokenArray = Array(
      	'access_token' => $coinbaseAccess->accessToken,
        'refresh_token' => $coinbaseAccess->refreshToken,
        'expire_time' => $coinbaseAccess->expires
      );
      return $tokenArray;
    } else{
      return false;
    }
  }
  
  public function getUserCoinbaseAccess($userId){
    return $this->getDbAdapter()->getIdByUserId($userId);
  }
  
  public function saveAccessToken(){
    $this->id = $this->getDbAdapter()->saveAccessToken($this);
    return $this;
  }
  
  public function saveOrUpdateAccessToken(){
    //Check to see if there is an entry for the user in the Coinbase Access Table
    $coinbaseAccess = $this->getDbAdapter()->getIdByUserId($this->userId);
    
    if($coinbaseAccess){
      $this->id = $coinbaseAccess->id;
    }
    
    $this->saveAccessToken();
  }

  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }

  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'user_coinbase_access'));
    }
    return $this->_tableAdapter;
  }
}