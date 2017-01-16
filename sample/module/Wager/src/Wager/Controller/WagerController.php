<?php 

namespace Wager\Controller;

//use Wager\Models\TheWagers;
use Zend\Mvc\Controller\AbstractActionController;


class WagerController extends AbstractActionController{
  
  protected $_dbAdapter;


  public function __construct(AdapterInterface $dbAdapter) 
  {
      $this->_dbAdapter = $dbAdapter;
  }

  public function indexAction()
  {
      //$this->layout('layout/basic.phtml');
      $this->_view = new ViewModel();
      return $this->_view;

  }
  
}