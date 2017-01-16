<?php
namespace Wager\Form;

use Zend\Form\Form;

class ChangeEmailForm extends Form{
  public function __construct($name = null){
    parent::__construct('changeEmail');

    $this->setAttribute('role', 'form');
    $this->setAttribute('accept-charset', 'UTF-8');
    $this->setAttribute('id', 'changeEmailForm');
    
    $this->add(array(
        'name' => 'email',
        'type' => 'Zend\Form\Element\Email',
        'options' => array(
            'label' => 'Email'
        ),
        'attributes' => array(
            'class' => 'form-control'
        )
    ));
    $this->add(array(
        'name' => 'submit',
        'type' => 'Submit',
        'attributes' => array(
            'value' => 'Update Email',
            'class' => 'btn btn-success'
        )
    ));
  }
}