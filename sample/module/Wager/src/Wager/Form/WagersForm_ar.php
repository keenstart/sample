<?php

namespace Wager\Form;

use Zend\Form\Form;

class WagersForm extends Form{
    public function __construct($name = null, $consoles = Array(),$games = Array()){
        parent::__construct('wager');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'WagersForm');
    
        $consolesOptions = Array();
        foreach($consoles as $console){
            if($console->id != 1) {
                $consolesOptions[$console->id] = $console->consoleName;
            }
        }

        $gamesOptions = Array();
        foreach($games as $game){
            $gamesOptions[$game->id] = $game->gameName;
        }
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'typeId',
            'options' => array(
                'value_options' => array(
                    '0' => 'User Wager',
                    '1' => 'Open Wager',
                ),
                'attributes' => array(
                    'value'=>'0',
                    'class'=>'ari'
                ),
            ),
        ));
            
        $this->add(array(
            'name' => 'userAccept',
            'options' => Array(
              'label' => 'Opponent'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'opponent'
            )
        ));

        $this->add(array(
            'name' => 'riskAmount',
            'options' => Array(
              'label' => 'Wager Amount (USD)'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => '0.00'
            )
        ));

        $this->add(array(
          'type' => '\Zend\Form\Element\Select',
            'name' => 'consoleId',
            'options' => Array(
              'label' => 'Console',
              'value_options' => $consolesOptions
            ),
            'attributes' => array(
            'class' => 'form-control',
            'value' => 2,
            'id' => 'wager-console-id',
            'onchange' => 'getGameWagerForm("#wager-console-id")'
          )
        ));

        $this->add(array(
          'type' => '\Zend\Form\Element\Select',
            'name' => 'gameId',
            'options' => Array(
              'label' => 'Game',
              'empty_option' => 'All Games',
              'value_options' => $gamesOptions
          ),
          'attributes' => array(
            'class' => 'form-control',
            'value' => 1,
            'id' => 'wager-games-id'
          )
        ));
        $this->add(array(
            'name' => 'consoleUsername',
            'options' => Array(
              'label' => 'Console Username'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'consoleUsername'
            )
        ));
        
        $this->add(array(
            'name' => 'askRules',
            'options' => Array(
              'label' => 'Rules'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'rules',
                'type'  =>'Zend\Form\Element\Textarea', 
                'rows'=>'5',
                'cols'=>'50',
            )
        ));
//        $this->add(array(
//            'name' => 'comments',
//            'options' => Array(
//              'label' => 'Comments'
//            ),
//            'attributes' => array(
//                'class' => 'form-control',
//                'id' => 'comments',
//                'type'  =>'Zend\Form\Element\Textarea', 
//                'rows'=>'7',
//                'cols'=>'50',                
//            )
//        ));


        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Create Wager',
                'id' => 'submitWager',
                'class' => 'btn btn-lg btn-red'
            )
        ));
    }
}