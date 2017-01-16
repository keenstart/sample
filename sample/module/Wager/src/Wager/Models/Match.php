<?php

namespace Wager\Models;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Match implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    public function setInputFilter(InputFilterInterface $inputFilter){
        throw new \Exception("Not used");
    }

    public function getInputFilter(){
        if(!$this->inputFilter){
          $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                'name'     => 'matchresult',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            ));
            
            $inputFilter->add(array(
                'name'     => 'comments',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ),
            ));


          $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
  
    /*public function getUserSession()
    {
        if(!$this->_userSession){
          $this->_userSession = new Container('user');
        }
        return $this->_userSession;
    }*/
}
