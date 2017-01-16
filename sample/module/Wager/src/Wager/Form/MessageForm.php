<?php

namespace Wager\Form;

use Zend\Form\Form;

class MessageForm extends Form{
    public function __construct($name = null){
        parent::__construct('match');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'MessageForm');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));    

        $this->add(array(
            'name' => 'touserId',
            'options' => Array(
              'label' => 'User'
            ),
            'attributes' => array(
                'class' => 'userInputFields',
                'id' => 'touserId'
            )
        ));

        $this->add(array(
            'name' => 'subject',
            'options' => Array(
              'label' => 'Subject'
            ),
            'attributes' => array(
                'class' => 'userInputFields',
                'id' => 'subject'
            )
        ));
        
        $this->add(array(
            'name' => 'messages',
            'attributes' => array(
                'type'  =>'Zend\Form\Element\Textarea', 
                'rows'=>'7',
                'cols'=>'50', 
                'placeholder' => 'Type your message here...',
                'class' => 'userInputFields',
            ),
            'options' => array(
               'label' => 'Message', 
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Send Match Result',
                'id' => 'submitMatch',
                'class' => 'btn btn-lg btn-red btn-block'
            )
        ));
    }
}