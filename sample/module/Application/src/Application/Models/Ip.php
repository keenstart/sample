<?php

namespace Application\Models;

class Ip{
  
  public function checkIpAddress($ip){
    if($ip == '50.186.110.20'){
      return false;
    }
    
    //if($_SERVER['APPLICATION_ENV'] == 'development'){
      $ip = '50.186.110.20';
    //}
    $two_letter_country_code= $this->iptocountry($ip);
    
    if ($two_letter_country_code=="US"){
      return true;
    }else{
      return false;
    }
  }
  
  protected function iptocountry($ip) {
    $numbers = preg_split( "/\./", $ip);
    include("IP/".$numbers[0].".php");
    if(isset($ranges)){
      $code=($numbers[0] * 16777216) + ($numbers[1] * 65536) + ($numbers[2] * 256) + ($numbers[3]);
      foreach($ranges as $key => $value){
        if($key<=$code){
          if($ranges[$key][0]>=$code){
            $two_letter_country_code=$ranges[$key][1];
            break;
          }
        }
      }
      if (isset($two_letter_country_code) && $two_letter_country_code==""){
        return false;
      } else{
        return $two_letter_country_code;
      }
    } else{
      return false;
    }
  }
}