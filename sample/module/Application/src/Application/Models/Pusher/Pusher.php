<?php

namespace Application\Models\Pusher;

use ZfrPusher\Client\Credentials;
use ZfrPusher\Client\PusherClient;
use ZfrPusher\Service\PusherService;

class Pusher 
{
  
    private  $_key = 'xxxxxxxxxxxxx';
    private  $_secret = 'xxxxxxxxxxxxx';
    private  $_app_id ='xxxxxxxxxxxxx';
        
    protected $credentials;
    protected $client;
    protected $service;
   
  
    public function getPusherService()
    {
        $this->credentials = new Credentials($this->_app_id, $this->_key, $this->_secret);
        $this->client      = new PusherClient($this->credentials);
        $this->service     = new PusherService($this->client);
        return $this->service;
    }
}