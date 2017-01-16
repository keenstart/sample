<?php

namespace Application\Models\XMLTeam;

use Application\Models\AWS\S3;
class XMLTeam{
  
  private $_username = "wagerwall";
  private $_password = "h0m3d0g";
  
  protected function getFile($fileInfo, $passwordRequired=true){
    if(is_array($fileInfo)){
      if(isset($fileInfo['awsKey'])){
        $s3Object = new S3();
        $outputFile = $s3Object->getFile($fileInfo['awsKey']);
        $output = $outputFile['Body'];
      } else{
        $url = $fileInfo['file'];
        $output = $this->getFileByCurl($url, $passwordRequired);
      }
    } else{
      $output = $this->getFileByCurl($fileInfo);
    }
    
    return $output;
  }
  
  protected function getFileByCurl($url, $passwordRequired = true){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if($passwordRequired){
      curl_setopt($ch, CURLOPT_USERPWD, "$this->_username:$this->_password");
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    } else{
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    $output = curl_exec($ch);
    curl_close($ch);
    
    return $output;
  }
}