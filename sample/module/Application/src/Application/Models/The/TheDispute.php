<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;
use Zend\Session\Container;

class TheDispute extends DbalConnector
{
    public $id;
    public $userId;
    public $disputeId;
    public $wagerId;
    public $status;
    public $linkUrl;
    public $disputeDetails;
    public $ipAddress;
    public $created;
     
    
    protected $_tableAdapter;
    protected $_userSession;

    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->userId = (!empty($data['userId'])) ? $data['userId'] : $this->userId;
        $this->disputeId = (!empty($data['disputeId'])) ? $data['disputeId'] : $this->disputeId;
        $this->wagerId = (!empty($data['wagerId'])) ? $data['wagerId'] : $this->wagerId;
        $this->status = (!empty($data['status'])) ? $data['status'] : $this->status;
        $this->linkUrl = (!empty($data['linkUrl'])) ? $data['linkUrl'] : $this->linkUrl;
        $this->disputeDetails = (!empty($data['disputeDetails'])) ? $data['disputeDetails'] : $this->disputeDetails;
        $this->ipAddress = (!empty($data['ipAddress'])) ? $data['ipAddress'] : $this->ipAddress;
        $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
    }

    public function makeTheDispute($theDisputeParams)
    {
       $this->exchangeArray($theDisputeParams);
       
        if(!isset($theDisputeParams['userId'])) {
            $this->userId = $this->getUserSession()->user->id;
        }
        $dateTime = new \DateTime("now", new \DateTimeZone('America/Chicago'));
        $this->created = $dateTime->format('Y-m-d H:i:s');

        // Capture Client Ip
        $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
        $this->ipAddress =  $remote->getIpAddress();
        
        //$this->disputeId = 100000;
        
        $newDispute = $this->saveTheDispute();

        return $newDispute;
    }

    public function editTheDispute($theDisputeParams)
    {
        $this->exchangeArray($theDisputeParams);
      
        $newDispute = $this->saveTheDispute();

        return $newDispute;
    }
    
    public function saveTheDispute()
    {
        $id = $this->getDbAdapter()->saveTheDispute($this);
        return $this->getDbAdapter()->getTheDisputeById($id);
    }

    public function getDisputeUserId()
    {
        return $this->getDbAdapter()->getTheDisputeUserId($this->getUserSession()->user->id);
    }
    
    public function getTheDisputeById($id)
    {
        return $this->getDbAdapter()->getTheDisputeById($id);
    }
           
    public function getTheDisputeWagerId($wagerId)
    {
        return $this->getDbAdapter()->getTheDisputeWagerId($wagerId);
    }    
    
    
    public function setDbAdapter($dbAdapter)
    {
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter()
    {
        if(!$this->_tableAdapter) {
            $this->setDbAdapter($this->setTableGateway($this, 'thedispute'));
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
