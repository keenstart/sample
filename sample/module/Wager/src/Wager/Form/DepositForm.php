<?php

namespace Wager\Form;

use Zend\Form\Form;

class DepositForm extends Form{
    public function __construct($name = null){
        parent::__construct('deposit');
        
        $this->setAttribute('role', 'form');
        $this->setAttribute('accept-charset', 'UTF-8');
        $this->setAttribute('id', 'DepositForm');
        
        $this->add(array(
            'name' => 'deposit',
            'options' => Array(
              'label' => 'Deposit Amount (USD)',
            ),
            'attributes' => array(
                'id' => 'deposit-id',
                'class' => 'form-control',
                'placeholder' => '$0.00'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'paytype',
            'options' => array(
                'value_options' => array(
                    '0' => '   Paypal   <i class="fa fa-paypal" aria-hidden="true"></i>',
                    '1' => '   Credit Card   <i class="fa fa-credit-card" aria-hidden="true"></i>',
                ),

            ),
        ));

        $this->add(array(
          'type' => '\Zend\Form\Element\Select',
            'name' => 'cardtype',
            'options' => Array(
                'label' => 'Credit Card Type',
                'value_options' => array(
                             'Visa' => 'Visa',
                             'MasterCard' => 'MasterCard',
                             'Discover' => 'Discover',
                             'Amex' => 'American Express',
                         ),
            ),
            'attributes' => array(
            'value' => 1,
            'class' => 'form-control',
          )
        ));

        $this->add(array(
            'name' => 'cardnumber',
            'options' => Array(
              'label' => 'Credit Card Number'
            ),
            'attributes' => array(
                'id' => 'cardnumber-id',
                'class' => 'form-control',
            )
        ));
        
        $this->add(array(
            'name' => 'cvv',
            'options' => Array(
              'label' => 'Security Code (CVV)'
            ),
            'attributes' => array(
                'id' => 'cvv-id',
                'class' => 'form-control',
            )
        ));
        
        $this->add(array(
          'type' => '\Zend\Form\Element\Select',
            'name' => 'month',
            'options' => Array(
                'label' => 'Month:',
                'value_options' => array(
                             '01' => '01',
                             '02' => '02',
                             '03' => '03',
                             '04' => '04',
                             '05' => '05',
                             '06' => '06',
                             '07' => '07',
                             '08' => '08',
                             '09' => '09',
                             '10' => '10',
                             '11' => '11',
                             '12' => '12'                              
                         ),
            ),
            'attributes' => array(
            'value' => 1,
            'class' => 'form-control',
          )
        ));
        $this->add(array(
          'type' => '\Zend\Form\Element\Select',
            'name' => 'year',
            'options' => Array(
                'label' => 'Year:',
                'value_options' => array(
                             '2016' => '2016',
                             '2017' => '2017',
                             '2018' => '2018',
                             '2019' => '2019',
                             '2020' => '2020',
                             '2021' => '2021',
                             '2022' => '2022',
                             '2023' => '2023',
                             '2024' => '2024',
                             '2025' => '2025',
                             '2026' => '2026',
                             '2027' => '2027',
                             '2028' => '2028',
                             '2029' => '2029',
                             '2030' => '2030',
                             '2031' => '2031',
                             '2032' => '2032',
                         ),
            ),
            'attributes' => array(
            'value' => 1,
            'class' => 'form-control',
          )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Send Deposit',
                'id' => 'submitDeposit',
                'class' => 'btn btn-lg btn-success',
                'src' => 'https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif'
            )
        ));
    }
}