<?php

namespace Application\Models\AWS;

use Aws\Common\Aws;

class AWSFactory{
  
  private static $_key = 'xxxxxxxxxxxxx';
  private static $_secret = 'xxxxxxxxxxxxx';
  
  public static function getCommonAwsObject(){
    $aws = Aws::factory(Array(
        'region' => 'us-west-2',
        'key'    => self::$_key,
        'secret' => self::$_secret
    ));
    return $aws;
  }
}

//SMTP Username: AKIAIBGCGM5IPBXINUWA
//SMTP Password:AkwHdhjAl+85GFBeVlxtoSdouUGV9UwQ26FDd8Wq4ne4