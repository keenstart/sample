<?php

namespace Login\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Digits;

class Create implements InputFilterAwareInterface{
    
    protected $inputFilter;
    
    public function setInputFilter(InputFilterInterface $inputFilter){
        throw new \Exception("Not used");
    }
    
    public function getInputFilter(){
        if(!$this->inputFilter){
            $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                'name' => 'password',
                'required' => true,
                'filters' => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 6,
                            'max' => 128
                        )
                    )
                )
            ));
            
            $inputFilter->add(array(
                'name' => 'repeat_password',
                'required' => true,
                'filters' => array(
                    array('name' => 'Zend\Filter\StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 6
                        )
                    ),
                    array(
                        'name' => 'identical',
                        'options' => array(
            	               'token' => 'password'
                        )
                    )
                )
            ));
            
            $inputFilter->add(array(
                'name' => 'confirm_legal',
                'validators' => array(
                  array(
                    'name' => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => array(
                      'messages' => array(
                          Digits::NOT_DIGITS => 'You must confirm that betting is legal in your jurisdiction',
                      ),
                    ),
                  ),
                ),
            ));
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}