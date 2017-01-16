<?php

namespace Application\Models\AWS;

use Application\Models\AWS\AWSFactory;

class S3{
  
  protected $_client;
  
  
  protected $_bucket = 'xxxxxxxxxxxxx';
  
  public function getS3Contents($prefixArray = Array(), $limit=NULL){
    $returnArray = Array();
    $secondaryRequestData = Array();
    $requestArray = Array('Bucket' => $this->_bucket);
    if($limit) $secondaryRequestData['limit'] = $limit;
    if(!empty($prefixArray)){
      foreach($prefixArray as $prefix){
        $requestArray['Prefix'] ='team.com/sportsml/files/' .  $prefix;
        if(!empty($secondaryRequestData)){
          $returnArray[] = $this->getS3Client()->getIterator('ListObjects', $requestArray, $secondaryRequestData);
        } else{
          $returnArray[] = $this->getS3Client()->getIterator('ListObjects', $requestArray);
        }
      }
    } else{
      $returnArray[] = $this->getS3Client()->getIterator('ListObjects', $requestArray, $secondaryRequestData);
    }
    return $returnArray;
  }
  
  public function getFile($key){
    return $this->getS3Client()->getObject(Array(
        'Bucket' => $this->_bucket,
        'Key' => $key
    ));
  }
  
  protected function getS3Client(){
    if(!$this->_client){
      $aws = AWSFactory::getCommonAwsObject();
      $this->_client = $aws->get('s3');
    }
    return $this->_client;
  }
  
  public function preserveXmlFile($savedFileName, $body){
    $this->getS3Client()->putObject(array(
        'Bucket' => $this->_bucket,
        'Key' => $savedFileName,
        'Body' => $body
    ));
  }
  
  public function uploadFile($savedFileName, $sourceFile){
  
    $this->getS3Client()->putObject(array(
        'ACL'    => 'public-read',
        'Bucket' => $this->_bucket,
        'Key' => $savedFileName,
        'SourceFile' => $sourceFile
    ));
  }  
}