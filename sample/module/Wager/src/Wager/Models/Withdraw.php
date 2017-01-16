<?php

namespace Wager\Models;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Withdraw implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    public function setInputFilter(InputFilterInterface $inputFilter){
        throw new \Exception("Not used");
    }

    public function getInputFilter(){
        if(!$this->inputFilter){
            $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                'name'     => 'withdrawal',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            ));
 
          $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}
