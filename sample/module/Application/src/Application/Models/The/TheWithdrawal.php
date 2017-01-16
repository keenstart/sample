<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;
use Zend\Session\Container;

class TheWithdrawal extends DbalConnector
{
    public $id;
    public $userId;
    public $transactionId;
    public $type;
    public $amount;
    public $paytype;
    public $isPaid;
    public $payDate;
    public $ipAddress;
    public $created;
     
    
    protected $_tableAdapter;
    protected $_userSession;

    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
        $this->transactionId = (!empty($data['transactionId'])) ? $data['transactionId'] : $this->transactionId;
        $this->type = (!empty($data['type'])) ? $data['type'] : $this->type;
        $this->amount = (!empty($data['amount'])) ? $data['amount'] : $this->amount;
        $this->paytype = (!empty($data['paytype'])) ? $data['paytype'] : $this->paytype;
        $this->isPaid = (!empty($data['isPaid'])) ? $data['isPaid'] : $this->isPaid;
        $this->payDate = (!empty($data['payDate'])) ? $data['payDate'] : $this->payDate; 
        $this->ipAddress = (!empty($data['ipAddress'])) ? $data['ipAddress'] : $this->ipAddress;
        $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
    }

    public function makeTheWithdrawal($thewithdrawalParams, $prefix = null)
    {
       $this->exchangeArray($thewithdrawalParams);
        if(!isset($thewithdrawalParams['userId'])) {
            $this->userId = $this->getUserSession()->user->id;
        }
        $dateTime = new \DateTime("now", new \DateTimeZone('America/Chicago'));
        $this->created = $dateTime->format('Y-m-d H:i:s');
        if(!$prefix) $prefix = 'p';
        $this->transactionId = uniqid($prefix,true);
        $this->transactionId = str_replace(".", "g", $this->transactionId);
        // Capture Client Ip
        $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
        $this->ipAddress =  $remote->getIpAddress();
        
        $this->amount = $thewithdrawalParams['withdrawal'];
        $this->type = $thewithdrawalParams['withtype'];
                
        if($this->type  == '0') {
            $this->paytype = "Pay with Paypal";
        } else {
            $this->paytype = "Pay with Check";
        }
   
        $newWithdrawal = $this->saveTheWithdrawal();

        return $newWithdrawal;
    }

    public function saveTheWithdrawal()
    {
        $id = $this->getDbAdapter()->saveTheWithdrawal($this);
        return $this->getDbAdapter()->getTheWithdrawalById($id);
    }

    public function getWithdrawalUserId()
    {
        return $this->getDbAdapter()->getTheWithdrawalUserId($this->getUserSession()->user->id);
    }
    
    public function getTheWithdrawalById($id)
    {
        return $this->getDbAdapter()->getTheWithdrawalById($id);
    }
           
    
    public function setDbAdapter($dbAdapter)
    {
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter()
    {
        if(!$this->_tableAdapter) {
            $this->setDbAdapter($this->setTableGateway($this, 'thewithdrawal'));
        }
        return $this->_tableAdapter;
    }
    
    protected function getUserSession()
    {
        if(!$this->_userSession) {
          $this->_userSession = new Container('user');
        }
        return $this->_userSession;
    }
}
