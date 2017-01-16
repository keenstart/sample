<?php

namespace Login\Form;

use Zend\Form\Form;

class ResendWelcomeForm extends Form{
    public function __construct($name = null){
        parent::__construct('resendWelcome');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'resendWelcomeForm');
        
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
                'value' => 'Resend Validation Email',
                'id' => 'createButton',
                'class' => 'btn btn-lg btn-red btn-block'
            )
        ));
    }
}