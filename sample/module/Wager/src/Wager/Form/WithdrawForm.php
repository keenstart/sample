<?php

namespace Wager\Form;

use Zend\Form\Form;

class WithdrawForm extends Form {
    public function __construct($name = null){
        parent::__construct('withdrawal');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'WithdrawForm');
        
        $this->add(array(
            'name' => 'withdrawal',
            'options' => Array(
              'label' => 'Withdrawal Amount (USD)',
            ),
            'attributes' => array(
                'id' => 'withdraw-id',
                'class' => 'form-control',
                'placeholder' => '$0.00'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'withtype',
            'options' => array(
                'value_options' => array(
                    '0' => '   Paypal   <i class="fa fa-paypal" aria-hidden="true"></i>',
                    '1' => '   Checks   <i class="fa fa-credit-card" aria-hidden="true"></i>',
                ),

            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Withdraw',
                'id' => 'submitWithdraw',
                'disabled' => 'disabled',
                'class' => 'btn btn-lg btn-success',
                'src' => 'https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif'
            )
        ));
    }
}