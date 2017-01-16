<?php

namespace Login\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Session\Container;
use Application\Models\UserLogins;

class Login implements InputFilterAwareInterface{
    
  protected $inputFilter;
  protected $_userSession;
  
  public function registerAndLogin($user = null,$dbAdapter){
    if($user){
      $this->getUserSession()->user = $user;
    }
    
    $userLogin = new UserLogins($dbAdapter);
    $userLogin->login($this->getUserSession()->user, $_SERVER);
  }
  
  public function setInputFilter(InputFilterInterface $inputFilter){
    throw new \Exception("Not used");
  }

  public function getInputFilter(){
    if(!$this->inputFilter){
      $inputFilter = new InputFilter();
      
      $inputFilter->add(array(
          'name' => 'email',
          'required' => true
      ));
      
      $inputFilter->add(array(
          'name' => 'password',
          'required' => true,
          'filters' => array(
              array('name' => 'StringTrim'),
          ),
          'validators' => array(
              array(
                  'name' => 'StringLength',
                  'options' => array(
                      'encoding' => 'UTF-8',
                      'min' => 6,
                      'max' => 128
                  )
              )
          )
      ));
      $this->inputFilter = $inputFilter;
    }
    return $this->inputFilter;
  }
  
  public function getUserSession(){
    if(!$this->_userSession){
      $this->_userSession = new Container('user');
    }
    return $this->_userSession;
  }
}