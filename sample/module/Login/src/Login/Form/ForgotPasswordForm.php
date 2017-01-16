<?php

namespace Login\Form;

use Zend\Form\Form;
use Login\Model\ForgotPassword;

class ForgotPasswordForm extends Form{
    public function __construct($name = null){
        parent::__construct('forgotPassword');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'forgotPasswordForm');
        
        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'options' => array(
                'label' => 'Email'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Email'
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Send Password Reminder',
                'id' => 'submitButton',
                'class' => 'btn btn-lg btn-red btn-block'
            )
        ));
    }
}