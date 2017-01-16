<?php

namespace Application\Validators;

use Zend\Validator\AbstractValidator;

class Uppercase extends AbstractValidator{
    const NOUPPERCASE = 'NOUPPERCASE';
    
    protected $messageTemplates = array(
        self::NOUPPERCASE => 'You must have at least one uppercase'
    );
    
    public function __construct(array $options = array()){
      parent::__construct($options);
    }
    
    public function isValid($value){
      $this->setValue($value);
      
      $uppercase = preg_match('/[A-Z]/', $value);
    
      if(!$uppercase){
        $this->error(self::NOUPPERCASE);
        return false;
      }

      return true;
    }
}