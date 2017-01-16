<?php

namespace Wager\Models;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Wagers implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    public function setInputFilter(InputFilterInterface $inputFilter){
        throw new \Exception("Not used");
    }

    public function getInputFilter(){
        if(!$this->inputFilter){
            $inputFilter = new InputFilter();

//            $inputFilter->add(array(
//                'name'     => 'userAccept',
//                'required' => true,
//                'filters'  => array(
//                    array('name' => 'StripTags'),
//                    array('name' => 'StringTrim'),
//                ),
//                'validators' => array(
//                    array(
//                        'name'    => 'StringLength',
//                        'options' => array(
//                            'encoding' => 'UTF-8',
//                            'min'      => 1,
//                            'max'      => 255,
//                        ),
//                    ),
//                ),
//            ));
//
//
//            $inputFilter->add( array(
//                'name' => 'riskAmount',
//                'required' => true,
//                'validators' => array(
//                    array(
//                        'name' => 'Float',
//                        'options' => array(
//                            'min' => 0,
//                            //'locale' => '<my_locale>'
//                        ),
//                    ),
//                ),
//            ) );


//            $inputFilter->add(array(
//                'name'     => 'consoleId',
//                'required' => true,
//                'filters'  => array(
//                    array('name' => 'Int'),
//                ),
//            ));
//
//
//            $inputFilter->add(array(
//                'name'     => 'gameId',
//                'required' => true,
//                'filters'  => array(
//                    array('name' => 'Int'),
//                ),
//            ));
          
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
