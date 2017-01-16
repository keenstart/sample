<?php 

namespace Application\Models;

class Bitcoin{

  public function getBtcPrice($currency = 'usd'){
    return self::getPrice($currency);
  }
  
  public static function getBtcStatic($currency = 'usd'){
    return self::getPrice($currency);
  }
  
  protected static function getPrice($currency){
    $getExchanges = file_get_contents("https://coinbase.com/api/v1/currencies/exchange_rates");
    $exchangeRates = json_decode($getExchanges);
    $curToBtc = strtolower($currency) . '_to_btc';
    return $exchangeRates->$curToBtc;
  }
}