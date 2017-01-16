<?php

namespace Login\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ResetPassword implements InputFilterAwareInterface{
    
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
                    ),
                    array(
                        'name' => '\Application\Validators\SpecialCharacter'
                    ),
                    array(
                        'name' => '\Application\Validators\Uppercase'
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
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}