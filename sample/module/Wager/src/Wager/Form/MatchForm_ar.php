<?php

namespace Wager\Form;

use Zend\Form\Form;

class MatchForm extends Form{
    public function __construct($name = null){
        parent::__construct('match');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'MatchForm');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));    

        $this->add(array(
          'type' => '\Zend\Form\Element\Select',
            'name' => 'matchresult',
            'options' => Array(
                'label' => 'Result',
                'empty_option' => 'Choose Match Result:',
                'value_options' => array(
                             '1' => 'I Won',
                             '2' => 'I Lost',
                         ),
            ),
            'attributes' => array(
            'class' => 'form-control',
          )
        ));

        $this->add(array(
            'name' => 'comments',
            'attributes' => array(
                'type'  =>'Zend\Form\Element\Textarea', 
                'rows'=>'7',
                'cols'=>'50', 
                //'class'=>'textlen3',
            ),
            'options' => array(
                    'label' => 'Comment', 
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