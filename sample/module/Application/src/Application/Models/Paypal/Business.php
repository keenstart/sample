<?php

namespace Application\Models\Paypal;


Class Business
{
    protected function getConfig() 
    {
        //if(!$env) $env = 'production';
        $env = getenv('APPLICATION_ENV') ? 'production' : 'development';
        if($env == 'production') {
            return array(
                'username' => 'xxxxxxxxxxxxx',
                'password' => 'xxxxxxxxxxxxx',
                'signature' => 'xxxxxxxxxxxxx',

                // Sandbox: https://api-3t.sandbox.paypal.com/nvp
                // Live: https://api-3t.paypal.com/nvp
                'endpoint' => 'https://api-3t.paypal.com/nvp'
            );
        }else{
            return array(
                'username' => 'xxxxxxxxxxxxx',
                'password' => 'xxxxxxxxxxxxx',
                'signature' => 'xxxxxxxxxxxxx',

                // Sandbox: https://api-3t.sandbox.paypal.com/nvp
                // Live: https://api-3t.paypal.com/nvp
                'endpoint' => 'https://api-3t.sandbox.paypal.com/nvp'
            );          
            
            
        }
    }
    
    public function getPaypalRequest()
    {
        $apiConfig  = $this->getConfig();
  
        $client = new \Zend\Http\Client;
        $client->setAdapter(new \Zend\Http\Client\Adapter\Curl);

        $request = new \SpeckPaypal\Service\Request;
        $request->setClient($client);
        $request->setConfig(
            new \SpeckPaypal\Element\Config($apiConfig)
        );

        return $request;         
    }
    
    public function getDetail($amt)
    {
        $item = new \SpeckPaypal\Element\PaymentItem;
        $item->setName('xxxxxxxxxxxxx');
        $item->setDesc('xxxxxxxxxxxxx');
        $item->setAmt((string)$amt);
        $item->setNumber('1234567');
        $item->setQty('1');
        $item->setTaxAmt('0.00');

        return $item;
    }
}