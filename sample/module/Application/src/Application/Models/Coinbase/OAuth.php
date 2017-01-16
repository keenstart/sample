<?php

namespace Application\Models\Coinbase;

use Application\Models\Coinbase\ConnectionException;
use Application\Models\Coinbase\ApiException;

class OAuth{
    public $_clientId;
    public $_clientSecret;
    public $_redirectUri;

    public function __construct($clientId=null, $clientSecret=null, $redirectUri=null)
    {
      $env = getenv('APPLICATION_ENV');
      if($clientId){  
        $this->_clientId = $clientId;
      } else{
        $this->_clientId = ($env == 'production' ? 'xxxxxxxxxxxxx' : 'xxxxxxxxxxxxx');
      }

      if($clientSecret){
        $this->_clientSecret = $clientSecret;
      } else{
        $this->_clientSecret = ($env == 'production' ? 'xxxxxxxxxxxxx' : 'xxxxxxxxxxxxx');
      }
      
      if($redirectUri){
        $this->_redirectUri = $redirectUri;
      } else{
        $this->_redirectUri = ($env == 'production' ? 'xxxxxxxxxxxxx' : 'xxxxxxxxxxxxx');
      }
    }

    public function createAuthorizeUrl(Array $scope)
    {
        $url = "https://coinbase.com/oauth/authorize?response_type=code" .
            "&client_id=" . urlencode($this->_clientId) .
            "&redirect_uri=" . urlencode($this->_redirectUri);

        foreach($scope as $key => $scopeItem)
        {
            if(0 == $key) {
                $url .= '&scope=' . urlencode($scopeItem);
            } else {
                $url .= "+" . urlencode($scopeItem);
            }
        }
        
        $url .= '&meta[send_limit_amount]=100&meta[send_limit_currency]=USD&meta[send_limit_period]=day';

        return $url;
    }

    public function refreshTokens($oldTokens)
    {
        return $this->getTokens($oldTokens["refresh_token"], "refresh_token");
    }

    public function getTokens($code, $grantType='authorization_code')
    {
        $postFields["grant_type"] = $grantType;
        $postFields["redirect_uri"] = $this->_redirectUri;
        $postFields["client_id"] = $this->_clientId;
        $postFields["client_secret"] = $this->_clientSecret;

        if("refresh_token" === $grantType) {
            $postFields["refresh_token"] = $code;
        } else {
            $postFields["code"] = $code;
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($curl, CURLOPT_URL, 'https://coinbase.com/oauth/token');
        curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/ca-coinbase.crt');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: CoinbasePHP/v1'));

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if($response === false) {
            $error = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            throw new ConnectionException("Could not get tokens - network error " . $message . " (" . $error . ")");
        }
        if($statusCode !== 200) {
            throw new ApiException("Could not get tokens - code " . $statusCode, $statusCode, $response);
        }
        curl_close($curl);

        try {
            $json = json_decode($response);
        } catch (Exception $e) {
            throw new ConnectionException("Could not get tokens - JSON error", $statusCode, $response);
        }

        return array(
            "access_token" => $json->access_token,
            "refresh_token" => $json->refresh_token,
            "expire_time" => time() + 7200 );
    }
}
