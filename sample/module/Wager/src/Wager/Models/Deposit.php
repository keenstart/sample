<?php

namespace Wager\Models;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Deposit implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    public function setInputFilter(InputFilterInterface $inputFilter){
        throw new \Exception("Not used");
    }

    public function getInputFilter(){
        if(!$this->inputFilter){
            $inputFilter = new InputFilter();
            
//            $inputFilter->add(array(
//                'name'     => 'deposit',
//                'required' => true,
//                'filters'  => array(
//                    array('name' => 'Int'),
//                ),
//            ));
            
            $inputFilter->add( array(
                'name' => 'deposit',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Float',
                        'options' => array(
                            'min' => 0,
                            //'locale' => '<my_locale>'
                        ),
                    ),
                ),
            ) );
            
 
          $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}
