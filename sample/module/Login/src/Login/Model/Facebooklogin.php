<?php

namespace Login\Model;

use Facebook\Facebook;
use Facebook\FacebookApp;

Class Facebooklogin
{
    protected $_fb;
    protected $_fbApp;
    
    public function __construct()
    {
        if(!$this->_fb) {
            $this->_fb = new Facebook([
                'app_id' => 'xxxxxxxxxxxxx',
                'app_secret' => 'xxxxxxxxxxxxx',
                'default_graph_version' => 'v2.5',
            ]);
        }       
    }
    
    protected function getFacebookApp()
    {
        if(!$this->_fbApp) {
            $this->_fbApp = new FacebookApp('xxxxxxxxxxxxx', 'xxxxxxxxxxxxx');
        } 
        return $this->_fbApp;
    }
    
    public function getFacebook()
    {
        return $this->_fb;
    }
    
    public function getAccessToken() 
    {
        return $this->_fb;
    }
    
    public function getRedirectLoginHelper() 
    {
        return $this->getFacebook()->getRedirectLoginHelper();
    }

    public function getFacebookUrl() 
    {
        if(array_key_exists('HTTP_REFERER',$_SERVER)) {
            $url = parse_url($_SERVER['HTTP_REFERER']);
        }else{
            $url['scheme'] = 'http';
        }  
        
        //$uri = $this->getRequest()->getUri();
        //$scheme = $uri->getScheme();
        //$host = $uri->getHost();
    
     
        $url = $url['scheme'].'://'.$_SERVER['HTTP_HOST'];//.$_SERVER['REDIRECT_URL'];
        $helper = $this->getFacebook()->getRedirectLoginHelper();
        
        $permissions = ['email', 'public_profile']; // optional
        $loginUrl = $helper->getLoginUrl($url, $permissions);

        return $loginUrl;
    }
    
    public function getFacebookGraph($accessToken)
    {
        $this->getFacebook()->setDefaultAccessToken($accessToken);
        try {
          $response = $this->getFacebook()->get('/me?fields=id,first_name,last_name,email');
          $userGraph = $response->getDecodedBody();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }
        return $userGraph;
    }
}