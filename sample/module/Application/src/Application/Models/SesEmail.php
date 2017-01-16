<?php

namespace Application\Models;

use Aws\Ses\SesClient;
use Aws\Common\Aws;
use Application\Models\AWS\AWSFactory;
use Application\Models\AWS\SES;
use Exception;

class SesEmail{
    protected $_client;
    
    public $_sendArray = array();
    
    public function sendEmail(){
        if($this->checkSendReady()){
            try{
                return $this->getSesClient()->sendEmail($this->_sendArray);
            } catch(\Exception $e){
                
                return false;
            }
        } else{
            return false;
        }
    }
    
    protected function checkSendReady(){
        if(isset($this->_sendArray['Source']) 
        && isset($this->_sendArray['Destination']) 
        && isset($this->_sendArray['Message']['Subject']) 
        && isset($this->_sendArray['Message']['Body']['Text'])
        && isset($this->_sendArray['Message']['Body']['Html'])){
            return true;
        }
    }
    
    public function setBounceEmailPoint($email){
      $this->_sendArray['ReturnPath'] = $email;
    }
    
    public function setEmailHtml($html, $charset = 'UTF-8'){
        $this->_sendArray['Message']['Body']['Html'] = array(
        	'Data' => $html,
          'Charset' => $charset
        );
    }
    
    public function setEmailText($text, $charset = 'UTF-8'){
      $this->_sendArray['Message']['Body']['Text'] = array(
          'Data' => $text,
          'Charset' => $charset
      );
    }
    
    public function setRecipients($toAddress = array(), $ccAddress = array(), $bccAddress = array()){
      $this->_sendArray['Destination'] = array('ToAddresses' => $toAddress, 'CcAddresses' => $ccAddress, 'BccAddresses' => $bccAddress);
    }
    
    public function setReplyToAddress($email = array()){
        $this->_sendArray['ReplyToAddresses'] = $email;
    }
    
    public function setSender($email){
        $this->_sendArray['Source'] = $email;
    }
    
    public function setSubject($subject, $charset = 'UTF-8'){
      $this->_sendArray['Message']['Subject'] = array(
          'Data' => $subject,
          'Charset' => $charset
      );
    }
    
    protected function getSesClient(){
      if(!$this->_client){
        $ses = new SES();
        $this->_client = $ses->returnSesClient();
      }
      return $this->_client;
    }
}