<?php 

namespace Deploy\Controller;

use Deploy\Models\Deploy;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ViewModel\JsonModel;

class DeployController extends AbstractActionController{
  
  public function deployAction(){
    
    $validIps = Array('131.103.20.165', '131.103.20.166', '50.247.60.146');
    
    $serverParam = $this->getRequest()->getServer();
    $remoteAddress = $serverParam->get('REMOTE_ADDR');
    
    if(in_array($remoteAddress, $validIps)){
      $deploy = new Deploy($this->getServiceLocator());
      
      $deploy->exchangeArray(Array('deploy_data' => $this->getRequest()->getPost()));
      $deploy->save();
      
      $return = new JsonModel(array(
        'success' => true
      ));
    } else{
      $deploy = new Deploy($this->getServiceLocator());
      
      $deploy->exchangeArray(Array('deploy_data' => "Not in the IP Range. " . $remoteAddress));
      $deploy->save();
      
      $return = new JsonModel(array(
          'success' => true
      ));
    }
    return $return;
  }
  
}