<?php

namespace Application\Models;

use Application\Models\DbalConnector;
use Application\Models\User;

class UserActionTokens extends DbalConnector{
    
    public $id;
    public $user;
    public $action;
    public $token;
    public $deadline;
    
    protected $_tableName = 'user_action_tokens';
    protected $_tableAdapter;
    
    public function exchangeArray($data){
      $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
      $this->user = (!empty($data['user'])) ? $data['user'] : $this->user;
      $this->action = (!empty($data['action'])) ? $data['action'] : $this->action;
      $this->token = (!empty($data['token'])) ? $data['token'] : $this->token;
      $this->deadline = (!empty($data['deadline'])) ? $data['deadline'] : $this->deadline;
    }
    
    public function addUserActionToken(){
        $this->getDbAdapter()->saveEntry($this);
    }
    
    public function checkToken($token, $action){
        $entry = $this->getDbAdapter()->getEntryByToken($token);
        $returnArray = array();
        if($entry){
            if($entry->action === $action){
                $currentTime = new \DateTime("now", new \DateTimeZone('UTC'));
                $databaseTime = new \DateTime($entry->deadline);
                if($currentTime <= $databaseTime){
                  $user = new User($this->_dbAdapter);
                  $returnArray['success'] = true;
                  $returnArray['user'] = $user->getUser($entry->user);
                  $returnArray['tokenId'] = $entry->id;
                } else{
                  $returnArray['success'] = false;
                  $returnArray['error'] = 'This token has expired. Please request another token to reset your password.';
                }
            } else{
                $returnArray['success'] = false;
                $returnArray['error'] = 'This token is not valid. Please request another token to reset your password.';
            }
        } else{
            $returnArray['success'] = false;
            $returnArray['error'] = 'This token is not valid. Please request another token to reset your password.';
        }
        return $returnArray;
    }
    
    public function deleteToken($tokenId){
        $this->getDbAdapter()->deleteEntry($tokenId);
    }
    
    public function setDbAdapter($dbAdapter){
      $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter(){
      if(!$this->_tableAdapter){
        $this->setDbAdapter($this->setTableGateway($this, $this->_tableName));
      }
      return $this->_tableAdapter;
    }
}