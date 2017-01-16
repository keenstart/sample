<?php

namespace Application\Models\Wallets\Coinkite;

class Auth{
  
  private $_url = 'xxxxxxxxxxxxx';
  private $_key = 'xxxxxxxxxxxxx';
  private $_secret = 'xxxxxxxxxxxxx';
  
  public function getSignedRequest($endpoint, $force_timestamp = false){
    if($force_timestamp){
      $timestamp = $force_timestamp;
    } else{
      $now = new \DateTime();
      $now->setTimezone(new \DateTimeZone('UTC'));
      $timestamp = $now->format(\DateTime::ISO8601);
    }
    
    $data = $endpoint . '|' . $timestamp;
    $hash = hash_hmac('sha256', $data, $this->_secret);
    
    return Array($hash, $timestamp);
  }
  
  public function getCurlParams($endpoint, $force_timestamp = false){
    $requestArray = $this->getSignedRequest($endpoint, $force_timestamp);
    return Array('url' => $this->_url . $endpoint, 'headers' => Array("X-CK-Key: $this->_key", "X-CK-Sign: $requestArray[0]", "X-CK-Timestamp: $requestArray[1]"));
  }
}