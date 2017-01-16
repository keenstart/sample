<?php

namespace Application\Models\Coinbase;

use Application\Models\Coinbase\Authentication;

class SimpleApiKeyAuthentication extends Authentication
{
    private $_apiKey;

    public function __construct($apiKey)
    {
        $this->_apiKey = $apiKey;
    }

    public function getData()
    {
        $data = new \stdClass();
        $data->apiKey = $this->_apiKey;
        return $data;
    }
}