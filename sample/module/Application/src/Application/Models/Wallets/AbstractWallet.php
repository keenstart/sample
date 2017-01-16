<?php

namespace Application\Models\Wallets;

use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;

abstract class AbstractWallet{
  
  protected static $_userObject;
  protected static $_serviceManagerObject;
  
  protected $_user;
  protected $_serviceManager;

  public static function factory($user, $serviceManager){
    self::$_serviceManagerObject = $serviceManager;//--check to see it it can ve remove
    self::$_userObject = $user;
    $userWallet = $user->walletModel;
    if($userWallet){
      $wallet = ucfirst(strtolower($userWallet));
      $className = '\Application\Models\Wallets\\' . $wallet . '\Wallet';
      $objectToReturn = new $className();
      return $objectToReturn;
    } else{
      return false;
    }
  }
  
  public static function wagerwallFactory(){
    return new \Application\Models\Wallets\Coinkite\Wallet();
  }
  
  public abstract function addBitcoin(Array $data = Array());
  
  public abstract function getAccountBalance();
  
  public abstract function getAvailableBalance();
  
  public abstract function getExchangeCurrencies();
  
  public abstract function getExchangeRate($currency = 'usd');
  
  public abstract function getWalletBalance($serviceManager);
  
  public abstract function sendBitcoin($amount, $address);
  
  public abstract function transferFunds($amount, $from, $to);
  
  protected function loadView($viewName, $data){
    //Set the path to the view template.
    $resolver = new TemplateMapResolver();
    $resolver->setMap(array(
        'stepTemplate' => ROOT_PATH . '/module/BackOffice/view/back-office/wallet/partials/' . $viewName . '.phtml'
    ));
  
    //Create a view object and resolve the path based on the template map resolver above.
    $view = new PhpRenderer();
    $view->setResolver($resolver);
  
    //Create a view to use with the established template and add any variables that view will use.
    $viewModel = new ViewModel();
    $viewModel->setTemplate('stepTemplate')->setVariables(array(
        'data' => $data
    ));
    
    $html = $view->render($viewModel);
    return Array('success' => true, 'html' => $html);
  }
  
  protected function getUser(){
    if($this->_user) return $this->_user;
    $this->_user = self::$_userObject;
    return $this->_user;
  }
  
  protected function getServiceManager(){
    if($this->_serviceManager) return $this->_serviceManager;
    $this->_serviceManager = self::$_serviceManagerObject;
    return $this->_serviceManager;
  }
}