<?php

namespace Application\Models;

use Application\Models\DbalConnector;
use Application\Models\Credits;

class User extends DbalConnector {
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $currency;
    public $walletModel;
    public $xboxGamertag;
    public $pSNUsername;
    public $email_validated;
    public $administrator;
    
    protected $_tableAdapter;
    
    public function exchangeArray($data) {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->username = (!empty($data['username'])) ? $data['username'] : $this->username;
        $this->email = (!empty($data['email'])) ? $data['email'] : $this->email;
        $this->password = (!empty($data['password'])) ? $data['password'] : $this->password;
        $this->currency = (!empty($data['currency'])) ? $data['currency'] : $this->currency;
        $this->walletModel = (!empty($data['walletModel'])) ? $data['walletModel'] : $this->walletModel;
        $this->xboxGamertag = (!empty($data['xboxGamertag'])) ? $data['xboxGamertag'] : $this->xboxGamertag;
        $this->pSNUsername = (!empty($data['pSNUsername'])) ? $data['pSNUsername'] : $this->pSNUsername;
        $this->email_validated = (isset($data['email_validated'])) ? $data['email_validated'] : $this->email_validated;
        $this->administrator = (isset($data['administrator'])) ? $data['administrator'] : $this->administrator;        
    }
    
    public function checkExistingUser($email) {
        $existing = $this->getDbAdapter()->getUserByEmail($email);
        return $existing;
    }
    
    public function checkExistingUsername($username) {
      if(!$username){
        return true;
      }
      $existing = $this->getDbAdapter()->getUserByUsername($username);
      if(!$existing){
        return false;
      } else{
        return true;
      }
    }
    
    public function createNewUser($userParams) {
      //Create the user from the data passed in.
      $this->exchangeArray($userParams);
      $this->username = $this->createRandomUsername();
      $this->administrator = 0;
      $this->email_validated = 0;
      $this->currency = 'USD';
      $this->walletModel = 'coinkite';
      $newUser = $this->saveUser();
      
      //Add credits to their account
      $userCredits = new Credits($this->_serviceManager);
      $userCredits->exchangeArray(Array(
            'userId' => $newUser->id,
            'availableCredits' => 0,
            'creditsCurrentlyWagered' => 0
          ));
      
      $userCredits->saveCredits();
      
      return $newUser;
    }
    
    public function createRandomUsername() {
      $username = 'Anonymous';
      while($this->checkExistingUsername($username)){
        $username = rand(1000000,9999999);
      }
      return $username;
    }
    
    public function getUser($id) {
        $user = $this->getDbAdapter()->getUser($id);
        return $user;
    }

    public function getUserByUsername($username) {
      if(!$username){
        return null;
      }
      $row = $this->getDbAdapter()->getUserByUsername($username);
      return $row;
    }
    
    public function loginUser($email, $password) {
        $user = $this->getDbAdapter()->getUserByEmail($email);
        if($user){
            $valid = ($user->password === hash('sha256', $password)) ? $user : false;
        } else{
            $valid = false;
        }
        return $valid;
    }
    
    public function saveUserData($userParm) {
        $this->exchangeArray($userParm);
        return $this->saveUser();
    }
    
    public function saveUser() {
        $this->getDbAdapter()->saveUser($this);
        return $this->getDbAdapter()->getUserByEmail($this->email);
    }
    
    public function validateUserPassword($user, $password){
        $valid = ($user->password === hash('sha256', $password)) ? true : false;
        return $valid;
    }
    
    public function setDbAdapter($dbAdapter) {
        $this->_tableAdapter = $dbAdapter;
    }
       
//    public function setAdapterdb($dbAdapter) {
//       $this->_dbAdapter = $dbAdapter;
//    }
    
    public function getDbAdapter() {
        if(!$this->_tableAdapter){
            $this->setDbAdapter($this->setTableGateway($this, 'users'));
        }
        return $this->_tableAdapter;
    }
}