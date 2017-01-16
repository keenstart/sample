<?php

namespace Wager\Form;

use Zend\Form\Form;

class ChangePasswordForm extends Form{
  public function __construct($name = null){
    parent::__construct('changePassword');

    $this->setAttribute('role', 'form');
    $this->setAttribute('accept-charset', 'UTF-8');
    $this->setAttribute('id', 'changePasswordForm');
    
    $this->add(array(
        'name' => 'original_password',
        'type' => 'Password',
        'options' => array(
    	      'label' => 'Old Password'
        ),
        'attributes' => array(
    	      'class' => 'form-control',
            'placeholder' => 'Old Password'
        )
    ));    
    $this->add(array(
        'name' => 'password',
        'type' => 'Password',
        'options' => array(
            'label' => 'Password'
        ),
        'attributes' => array(
            'class' => 'form-control',
            'placeholder' => 'New Password'
        ),
    ));
    $this->add(array(
        'name' => 'repeat_password',
        'type' => 'Password',
        'options' => array(
            'label' => 'Re-enter New Password'
        ),
        'attributes' => array(
            'class' => 'form-control',
            'placeholder' => 'Re-enter New Password'
        ),
    ));    
    $this->add(array(
        'name' => 'submit',
        'type' => 'Submit',
        'attributes' => array(
            'value' => 'Update Password',
            'class' => 'btn btn-success'
        )
    ));
  }
}