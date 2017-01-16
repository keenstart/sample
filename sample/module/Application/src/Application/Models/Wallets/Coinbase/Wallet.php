<?php
namespace Application\Models\Wallets\Coinbase;

use Application\Models\Wallets\AbstractWallet;
use Login\Form\LoginForm;
use Application\Models\Coinbase\Coinbase;
use Application\Models\Coinbase\OAuth;
use Application\Models\UserCoinbaseAccess;
use Application\Models\Credits;

class Wallet extends AbstractWallet{
  
  private $_oauth;
  private $_coinbase;
  
  public function addBitcoin(Array $data = Array()){
    //To add bitcoin for a Coinbase account, we want to show a view where the user confirms the selection in a form.
    $conversion = $this->getExchangeRate($data['currency']);
    $outputBtc = $conversion * $data['amount'];
    $outputCurrency = $data['amount'];
    
    $form = new LoginForm();
    return $this->loadView('confirmbitcoinbuy', Array('confirmForm' => $form, 'valueBtc' => $outputBtc, 'valueCurrency' => $outputCurrency, 'currencyType' => $data['currency']));
  }
  
  public function getAccountBalance(){
    return $this->getCoinbase()->getBalance();
  }
  
  //This function is only for use with bitcoin we hold.  
  public function getAvailableBalance(){
    $credits = new Credits($this->getServiceManager());
    $creditObject = $credits->getCreditsByUserId($this->getUser()->id);
    
    return $creditObject->availableBalance;
  }
  
  public function getExchangeCurrencies(){
    return $this->getCoinbase()->getCurrencies();
  }
  
  public function getExchangeRate($currency = 'usd'){
    $getExchanges = file_get_contents("https://coinbase.com/api/v1/currencies/exchange_rates");
    $exchangeRates = json_decode($getExchanges);
    $curToBtc = strtolower($currency) . '_to_btc';
    return $exchangeRates->$curToBtc;
  }
  
  /*
   * @param ServiceManager $serviceManager is passed for working with databases
   * @param integer $userId is passed to load data for an individual user, if necessary
   */
  public function getWalletBalance(){
    $userId = $this->getUser()->id;
    $coinbaseAccess = new UserCoinbaseAccess($this->getServiceManager());
    $tokens = $coinbaseAccess->getTokens($userId);
    if($tokens){
      $oauth = Coinbase::withOAuth(new OAuth(), $userId);
      $credits = $oauth->getBalance();
    } else{
      $credits = 0;
    }
    return $credits;
  }
  
  public function sendBitcoin($amount, $address){
    
  }
  
  public function transferFunds($amount, $from, $to){
    
  }
  
  private function getCoinbase(){
    if($this->_coinbase) return $this->_coinbase;
    $coinbaseToken = new UserCoinbaseAccess($this->getServiceManager());
    if($coinbaseToken->getTokens($this->getUser()->id)){
      $this->_coinbase = Coinbase::withOAuth($this->getOauth(), $this->getUser()->id);
      return $this->_coinbase;
    } else{
      return false;
    }
  }
  
  private function getOauth(){
    if($this->_oauth) return $this->_oauth;
    $this->_oauth = new OAuth();
    return $this->_oauth;
  }
}