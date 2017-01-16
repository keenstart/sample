<?php

namespace Login\Form;

use Zend\Form\Form;

class ResetPasswordForm extends Form{
    public function __construct($name = null){
        parent::__construct('resetPassword');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'resetPasswordForm');
        
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
                'label' => 'Re-enter Password'
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
                'id' => 'createButton',
                'class' => 'btn btn-lg btn-red btn-block'
            )
        ));
    }
}