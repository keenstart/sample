<?php

namespace Application\Models\Coinbase;

use Application\Models\Coinbase\TokensExpiredException;
use Application\Models\Coinbase\ApiException;
use Application\Models\Coinbase\ConnectionException;

class Rpc
{
    private $_requestor;
    private $authentication;

    public function __construct($requestor, $authentication)
    {
        $this->_requestor = $requestor;
        $this->_authentication = $authentication;
    }

    public function request($method, $url, $params)
    {
        // Create query string
        $queryString = http_build_query($params);
        $url = Coinbase::API_BASE . $url;

        // Initialize CURL
        $curl = curl_init();
        $curlOpts = array();

        // HTTP method
        $method = strtolower($method);
        if ($method == 'get') {
            $curlOpts[CURLOPT_HTTPGET] = 1;
            if ($queryString) {
                $url .= "?" . $queryString;
            }
        } else if ($method == 'post') {
            $curlOpts[CURLOPT_POST] = 1;
            $curlOpts[CURLOPT_POSTFIELDS] = $queryString;
        } else if ($method == 'delete') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = "DELETE";
            if ($queryString) {
                $url .= "?" . $queryString;
            }
        } else if ($method == 'put') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = "PUT";
            $curlOpts[CURLOPT_POSTFIELDS] = $queryString;
        }

        // Headers
        $headers = array('User-Agent: CoinbasePHP/v1');

        $auth = $this->_authentication->getData();

        // Get the authentication class and parse its payload into the HTTP header.
        $authClass = explode("\\", get_class($this->_authentication));
        $authenticationClass = end($authClass);
                
        switch ($authenticationClass) {
            case 'OAuthAuthentication':
                // Use OAuth
                if(time() > $auth->tokens["expire_time"]) {
                    throw new TokensExpiredException("The OAuth tokens are expired. Use refreshTokens to refresh them");
                }

                $headers[] = 'Authorization: Bearer ' . $auth->tokens["access_token"];
                break;

            case 'ApiKeyAuthentication':
                // Use HMAC API key
                $microseconds = sprintf('%0.0f',round(microtime(true) * 1000000));

                $dataToHash =  $microseconds . $url;
                if (array_key_exists(CURLOPT_POSTFIELDS, $curlOpts)) {
                    $dataToHash .= $curlOpts[CURLOPT_POSTFIELDS];
                }
                $signature = hash_hmac("sha256", $dataToHash, $auth->apiKeySecret);

                $headers[] = "ACCESS_KEY: {$auth->apiKey}";
                $headers[] = "ACCESS_SIGNATURE: $signature";
                $headers[] = "ACCESS_NONCE: $microseconds";
                break;

            case 'SimpleApiKeyAuthentication':
                // Use Simple API key
                // Warning! This authentication mechanism is deprecated
                $headers[] = 'Authorization: api_key ' . $auth->apiKey;
                break;

            default:
                throw new ApiException("Invalid authentication mechanism");
                break;
        }

        // CURL options
        $curlOpts[CURLOPT_URL] = $url;
        $curlOpts[CURLOPT_HTTPHEADER] = $headers;
        $curlOpts[CURLOPT_CAINFO] = dirname(__FILE__) . '/ca-coinbase.crt';
        $curlOpts[CURLOPT_RETURNTRANSFER] = true;

        // Do request
        curl_setopt_array($curl, $curlOpts);
        $response = $this->_requestor->doCurlRequest($curl);

        // Decode response
        try {
            $json = json_decode($response['body']);
        } catch (Exception $e) {
            throw new ConnectionException("Invalid response body", $response['statusCode'], $response['body']);
        }
        if($json === null) {
            throw new ApiException("Invalid response body", $response['statusCode'], $response['body']);
        }
        if(isset($json->error)) {
            throw new ApiException($json->error, $response['statusCode'], $response['body']);
        } else if(isset($json->errors)) {
            throw new ApiException(implode($json->errors, ', '), $response['statusCode'], $response['body']);
        }

        return $json;
    }
}
