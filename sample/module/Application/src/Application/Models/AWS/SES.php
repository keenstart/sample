<?php

namespace Application\Models\Aws;

use Application\Models\AWS\AWSFactory;

class SES{
  
  protected $_client;
  
  public function returnSesClient(){
    return $this->getSesClient();
  }
  
  protected function getSesClient(){
    if(!$this->_client){
      $aws = AwsFactory::getCommonAwsObject();
      $this->_client = $aws->get('ses');
    }
    return $this->_client;
  }
  
}