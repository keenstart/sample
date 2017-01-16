<?php

namespace Application\Validators;

use Zend\Validator\AbstractValidator;

class SpecialCharacter extends AbstractValidator{
    
    const NOTSPECIAL = 'NOTSPECIAL';
    
    protected $messageTemplates = array(
        self::NOTSPECIAL => 'You must include at least one special character'
    );
    
    public function __construct(array $options = array()){
        parent::__construct($options);
    }
    
    public function isValid($value){
        $this->setValue($value);
        
        $special = preg_match('#[\W]{1,}#', $value);
        
        if(!$special){
            $this->error(self::NOTSPECIAL);
            return false;
        }
        
        return true;
    }
}