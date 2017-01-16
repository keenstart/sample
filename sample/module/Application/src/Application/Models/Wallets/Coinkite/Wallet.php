<?php

namespace Application\Models\Wallets\Coinkite;

use Application\Models\Wallets\AbstractWallet;
use Application\Models\Wallets\Coinkite\Auth;
use Application\Models\Credits;
use Application\Models\BitcoinTransactions;
use Pubnub\Pubnub;
use Application\Models\BitcoinPending;
use Application\Models\User;

class Wallet extends AbstractWallet{
  
  private $_account = 'Holding';
  private $_holding = 'Holding';
  private $_fees = 'Fees';
  
  public function addBitcoin(Array $data = Array()){
    //To add bitcoin for a Coinkite account, we just show the receive address that we will listen for receipt.
    $auth = new Auth();
    $amount = $this->getExchangeRate($data['currency']) * $data['amount'];
    $amount = number_format($amount, 8);
    $receiveData = json_decode($this->runInteraction($auth->getCurlParams('/v1/new/receive'), Array('account' => $this->_account)));
    $receiveAddress = $receiveData->result->address;
    $referenceObject = $receiveData->result->CK_refnum;
    
    $listen = json_decode($this->runInteraction($auth->getCurlParams('/v1/pubnub/enable')));
    
    //Add a record for the pending receipt of bitcoin;
    $pending = new BitcoinPending($this->getServiceManager());
    $pending->exchangeArray(Array('wallet'=>'coinkite', 'custom'=>$referenceObject, 'administratorId'=>$this->getUser()->id));
    $pending->addUpdatePending();
    
    return $this->loadView('showbitcoinreceiveaddress', Array('address' => $receiveAddress , 'amount' => $amount, 'pubnub' => $listen));
  }
  
  public function getAccountBalance(){
    $credits = new Credits($this->getServiceManager());
    $userCredits = $credits->getCreditsByUserId($this->getUser()->id);
    
    //Credits that we show we have received, but haven't fully cleared yet, we still want to show these.
    $pending = new BitcoinPending($this->getServiceManager());
    $pendingValue = $pending->getAllPendingByUserId($this->getUser()->id);
        
    return $userCredits->availableCredits + $pendingValue;
  }
  
  public function getAvailableBalance(){
    $credits = new Credits($this->getServiceManager());
    $userCredits = $credits->getCreditsByUserId($this->getUser()->id);
    
    return $userCredits->availableCredits;
  }
  
  public function getDetails($refNum){
    $auth = new Auth();
    $details = json_decode($this->runInteraction($auth->getCurlParams('/v1/detail/' . $refNum)));
    
    return $details;
  }
  
  public function getExchangeCurrencies(){
    $auth = new Auth();
    $currencies = json_decode($this->runInteraction($auth->getCurlParams('/public/rates')));
    $returnArray = Array();
    foreach($currencies->currencies as $currency){
      $returnArray[] = (object) Array('iso' => $currency->code, 'name' => $currency->name);
    }
    return $returnArray;
  }
  
  public function getExchangeRate($currency = 'usd'){
    if(!$currency) $currency = 'usd';
    if(strtolower($currency) === 'btc') return 1;
    $auth = new Auth();
    $exchangeRates = json_decode($this->runInteraction($auth->getCurlParams('/public/rates')));
    $currency = strtoupper($currency);
    return (1/$exchangeRates->rates->BTC->$currency->rate);
  }
  
  /*
   * @param ServiceManager $serviceManager is passed for working with databases
   * @param integer $userId is passed to load data for an individual user, if necessary
   */
  public function getWalletBalance($serviceManager){
    //Since Coinkite is just a holding tank, we don't have a wallet with credits.  Return 0;
    return 0;
  }
  
  public function filterDetails($details){
    $confirmed = false;
    
    $value = $details->detail->amount_so_far->decimal;
    
    foreach($details->detail->events as $event){
      if($event && $event->credit_txo && $event->credit_txo->block && $event->credit_txo->block->fully_confirmed){
        $confirmed = true;
      }
    }
    
    return Array('value' => $value, 'confirmed' => $confirmed);
  }
  
  public function recordTransaction($details){
    $hash = 0;
    foreach($details->detail->events as $event){
      if($event->credit_txo->block->hash) $hash = $event->credit_txo->block->hash;
    }
    $transaction = new BitcoinTransactions($this->getServiceManager());
    $transaction->exchangeArray(Array(
          'account' => $this->_holding,
          'custom' => json_encode(Array('reference' => $details->detail->CK_refnum, 'detail-page' => $details->detail->detail_page, 'hash' => $hash)),
          'value' => $details->detail->amount_so_far->decimal,
          'wallet' => 'coinkite'
        ));
    $transaction->recordTransaction();
  }
  
  public function sendBitcoin($amount, $address, User $userObject = null){
    $auth = new Auth();
    $amount = number_format($amount, 8);
    $send = json_decode($this->runInteraction($auth->getCurlParams('/v1/new/send'), Array('amount' => $amount, 'account' => $this->_holding, 'dest' => $address)));
    
    $refNum = $send->result->CK_refnum;
    $authCode = $send->result->send_authcode;
    
    $authorize = json_decode($this->runInteraction($auth->getCurlParams('/v1/update/' . $refNum . '/auth_send'), Array('authcode' => $authCode)));
    
    //Record the transaction in the database
    $transactionRecord = new BitcoinTransactions($this->getServiceManager());
    $transactionRecord->exchangeArray(Array(
          'account' => $this->_holding,
          'custom' => json_encode(Array('reference' => $authorize->result->CK_refnum, 'detail-page' => $send->result->detail_page)),
          'value' => $amount * -1,
          'wallet' => 'coinkite'
        ));
    $transactionRecord->recordTransaction();
    
    $transactionOtherSide = new BitcoinTransactions($this->getServiceManager());
    $transactionOtherSide->exchangeArray(Array(
          'account' => $address,
          'custom' => json_encode(Array('reference' => $authorize->result->CK_refnum, 'detail-page' => $send->result->detail_page)),
          'value' => $amount,
          'wallet' => 'user'
        ));
    $transactionOtherSide->recordTransaction();
    
    //Lastly we need to remove the amount from the user's account.
    if($userObject){
      $credits = new Credits();
      $userCredits = $credits->getCreditsByUserId($userObject->id);
      $userCredits->adjustUserCreditsTransfer($amount);
    }
    
    return $authorize;
  }
  
  public function transferFunds($amount, $from, $to){
    $auth = new Auth();
    $amount = number_format($amount, 8);
    $transfer = json_decode($this->runInteraction($auth->getCurlParams('/v1/new/xfer'), Array('amount' => $amount, 'from_account' => $from, 'to_account' => $to)));
    
    //We need to save a record of the transaction:
    $transaction = new BitcoinTransactions($this->getServiceManager());
    $transaction->exchangeArray(Array(
          'account' => $from,
          'custom' => json_encode(Array('reference' => $transfer->result->CK_refnum, 'detail-page' => $transfer->result->detail_page, 'full-output' => $transfer)),
          'value' => $amount * -1,
          'wallet' => 'coinkite'
        ));
    $savedId = $transaction->recordTransaction();
    
    $repeatTransaction = new BitcoinTransactions($this->getServiceManager());
    $repeatTransaction->exchangeArray(Array(
        'account' => $to,
        'custom' => json_encode(Array('reference' => $transfer->result->CK_refnum, 'detail-page' => $transfer->result->detail_page, 'full-output' => $transfer)),
        'value' => $amount,
        'wallet' => 'coinkite'
    ));
    $repeatTransaction->recordTransaction();

    return $savedId;
  }
  
  public function transferToFees($amount = null){
    //We transfer $1.00 per transaction
    if(!$amount){
      $amount = number_format($this->getExchangeRate(), 8);
    } else{
      $amount = number_format($amount, 8);
    }
    $transfer = $this->transferFunds($amount, $this->_holding, $this->_fees);
    return $transfer;
  }
  
  public function returnFeeToUser($amount){
    $amount = number_format($amount, 8);
    $transfer = $this->transferFunds($amount, $this->_fees, $this->_holding);
    return $transfer;
  }
  
  protected function runInteraction($curlParams, $postParams=null){
    $ch = curl_init($curlParams['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    if($postParams){
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));
    }
    curl_setopt($ch,CURLOPT_HTTPHEADER, $curlParams['headers']);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    $result = curl_exec($ch);
    //var_dump(curl_getinfo($ch, CURLINFO_HEADER_OUT));
    curl_close($ch);
    return $result;
  }
  
}