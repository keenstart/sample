<?php
namespace Wager\Form;

use Zend\Form\Form;

class ConsoleUsernameForm extends Form{
  public function __construct($name = null, $consoleUsername){
    parent::__construct('consoleUsername');

    $this->setAttribute('role', 'form');
    $this->setAttribute('accept-charset', 'UTF-8');
    $this->setAttribute('id', 'consoleUsernameForm');
    
    $this->add(array(
        'name' => 'xboxGamertag',
        'attributes' => array(
    	    'class' => 'form-control',
            'placeholder' => 'Xbox Gamertag',
            'id' => 'inputxboxgamertag',
            'value' => $consoleUsername['xboxGamertag']
        )
    ));
    
    $this->add(array(
        'name' => 'pSNUsername',
        'attributes' => array(
    	    'class' => 'form-control',
            'placeholder' => 'PSN Username',
            'id' => 'inputpsnusername',
            'value' => $consoleUsername['pSNUsername']
        )
    ));
    
    $this->add(array(
        'name' => 'submit',
        'type' => 'Submit',
        'attributes' => array(
            'value' => 'Update Username',
            'class' => 'btn btn-success'
        )
    ));
  }
}