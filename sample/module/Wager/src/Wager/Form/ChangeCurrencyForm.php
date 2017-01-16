<?php
namespace Wager\Form;

use Zend\Form\Form;

class ChangeCurrencyForm extends Form{
  public function __construct($user, $serviceManager){
    parent::__construct('changeCurrency');

    $this->setAttribute('role', 'form');
    $this->setAttribute('accept-charset', 'UTF-8');
    $this->setAttribute('id', 'changeCurrencyForm');
        
    $wallet = \Application\Models\Wallets\AbstractWallet::factory($user, $serviceManager);
    $currencyArray = $wallet->getExchangeCurrencies();
    
    $currencyOptions = Array();
    foreach($currencyArray as $currency){
      $currencyOptions[$currency->iso] = $currency->name;
    }
    
    $this->add(array(
        'name' => 'currency',
        'type' => 'Zend\Form\Element\Select',
        'attributes' => array(
            'class' => 'form-control',
            'value' => $user->currency
        ),
        'options' => Array(
            'label' => 'Select your currency of choice:',
            'value_options' => $currencyOptions
        )
    ));
    $this->add(array(
        'name' => 'submit',
        'type' => 'Submit',
        'attributes' => array(
            'value' => 'Update Default Currency',
            'class' => 'btn btn-gray'
        )
    ));
  }
}