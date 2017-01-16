<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;
use Zend\Session\Container;

class TheTransactions extends DbalConnector
{
    public $id;
    public $userId;
    public $transactionId;
    public $type;
    public $amount;
    public $paymentId;
    public $paymentmethod;
    public $feeamt;
    public $balance;
    public $description;
    public $wagerId;
    public $ipAddress;
    public $created;
    public $updated;    
    
    protected $_tableAdapter;
    protected $_userSession;

    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
        $this->transactionId = (!empty($data['transactionId'])) ? $data['transactionId'] : $this->transactionId;
        $this->type = (!empty($data['type'])) ? $data['type'] : $this->type;
        $this->amount = (!empty($data['amount'])) ? $data['amount'] : $this->amount;
        $this->paymentId = (!empty($data['paymentId'])) ? $data['paymentId'] : $this->paymentId;
        $this->paymentmethod = (!empty($data['paymentmethod'])) ? $data['paymentmethod'] : $this->paymentmethod;
        $this->feeamt = (!empty($data['feeamt'])) ? $data['feeamt'] : $this->feeamt; 
        $this->balance = (!empty($data['balance'])) ? $data['balance'] : $this->balance;
        $this->description = (!empty($data['description'])) ? $data['description'] : $this->description;  
        $this->wagerId = (!empty($data['wagerId'])) ? $data['wagerId'] : $this->wagerId;
        $this->ipAddress = (!empty($data['ipAddress'])) ? $data['ipAddress'] : $this->ipAddress;
        $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
        $this->updated = (!empty($data['updated'])) ? $data['updated'] : $this->updated;     
    }

    public function makeTheTransaction($thetransactionParams, $prefix = null)
    {
       $this->exchangeArray($thetransactionParams);
        if(!isset($thetransactionParams['userId'])) {
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

        
                
        $newTransaction = $this->saveTheTransaction();

        return $newTransaction;
    }
    
    public function saveFee($thetransactionParams)
    {
       $this->exchangeArray($thetransactionParams);

        $newTransaction = $this->saveTheTransaction();

        return $newTransaction;
    }    

    public function saveTheTransaction()
    {
        $id = $this->getDbAdapter()->saveTheTransaction($this);
        return $this->getDbAdapter()->getTheTransactionById($id);
    }

    public function getTransactionUserId()
    {
        return $this->getDbAdapter()->getTheTransactionUserId($this->getUserSession()->user->id);
    }
    
    public function getTransactionById($id)
    {
        return $this->getDbAdapter()->getTheTransactionById($id);
    }
    
    public function getTheTransactionByType($userId, $wagerId, $type)
    {
        return $this->getDbAdapter()->getTheTransactionByType($userId, $wagerId, $type);
    }
           
    public function setDbAdapter($dbAdapter)
    {
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter()
    {
        if(!$this->_tableAdapter) {
            $this->setDbAdapter($this->setTableGateway($this, 'thetransactions'));
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
