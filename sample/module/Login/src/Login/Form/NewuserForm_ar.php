<?php

namespace Login\Form;

use Zend\Form\Form;
use Login\Model\Create;

class NewuserForm extends Form{
    public function __construct($name = null){
        parent::__construct('newuser');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'newUserForm');
        
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
            'name' => 'password',
            'type' => 'Password',
            'options' => array(
                'label' => 'Password'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Password'
            ),
        ));
        $this->add(array(
            'name' => 'repeat_password',
            'type' => 'Password',
            'options' => array(
                'label' => 'Re-enter Password'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Re-enter Password'
            ),
        ));
        $this->add(array(
            'name' => 'confirm_legal',
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Check to confirm that betting is legal in your local jurisdiction.'
            ),
            'attributes' => array(
                'class' => 'form-control',
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Create Account',
                'id' => 'createButton',
                'class' => 'btn btn-lg btn-red btn-block'
            )
        ));
    }
}