<?php
namespace Wager\Form;

use Zend\Form\Form;

class ChangeUsernameForm extends Form{
  public function __construct($name = null){
    parent::__construct('changeUsername');

    $this->setAttribute('role', 'form');
    $this->setAttribute('accept-charset', 'UTF-8');
    $this->setAttribute('id', 'changeUsernameForm');
    
    $this->add(array(
        'name' => 'username',
        'attributes' => array(
            'class' => 'form-control',
            'id' => "changeUsernameInput"
        )
    ));
    $this->add(array(
        'name' => 'submit',
        'type' => 'Submit',
        'attributes' => array(
            'value' => 'Update Username',
            'class' => 'btn btn-success',
            'id' => 'changeUsernameButton'
        )
    ));
  }
}