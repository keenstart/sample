<?php 

namespace Application\Models\XMLTeam;

use Application\Models\XMLTeam\XMLTeam;
use Application\Models\AWS\S3;
use Application\Models\Leagues;

class Feed extends XMLTeam{
  
  private $_feedXml = "http://feed5.xmlteam.com/";
  private $_filesToLoad = Array();
  private $_url;
  
  public function getFeedDocuments($options = Array()){
    $this->_url = $this->_feedXml . 'sportsml/getFeed';

    if(!empty($options)){
      $this->_url .= '?';
      $this->_url .= http_build_query($options);
    }
    
    while($this->loadArrayOfFiles($this->_url)){

    }
    
    $this->iterateThroughFiles();
  }
  
  public function getFeedFromAs3($options = Array()){
    $s3 = new S3();
    $dateArray = NULL;
    $dateRange = false;    
    
    if(isset($options['startDate'])){
      $dateRange = true;
      $startTime = new \DateTime($options['startDate']);
    } else{
      $startTime = new \DateTime('2014-09-01');
    }
    
    if(isset($options['endDate'])){
      $dateRange = true;
      $endTime = new \DateTime($options['endDate']);
    } else{
      $endTime = new \DateTime();
    }
    
    if(isset($options['specificFile'])){
      $specificFileArray = explode('_', $options['specificFile']);
      $fileName = array_pop($specificFileArray);
      $contents = $s3->getS3Contents($specificFileArray);
      
      foreach($contents as $contentResponse){
        foreach($contentResponse as $content){
          $s3Filename = array_pop(explode('/', $content['Key']));
          if($s3Filename === $fileName){
            $this->_filesToLoad[] = Array(
                'file' => 'https://s3-us-west-2.amazonaws.com/wagerwall/' . $content["Key"],
                'awsKey' => $content['Key'],
                'xmlTeam' => false);
          }
        }
      }
    } else if($dateRange){
      $lastUpdatedDay = $startTime;
      while($endTime >= $lastUpdatedDay){
        $dateArray[] = $lastUpdatedDay->format('Y/m/d');
        $lastUpdatedDay = $lastUpdatedDay->add(new \DateInterval('P1D'));
      }
      $contents = $s3->getS3Contents($dateArray);
      
      foreach($contents as $contentResponse){
        foreach($contentResponse as $content){
          $this->_filesToLoad[] = Array(
              'file' => 'https://s3-us-west-2.amazonaws.com/wagerwall/' . $content["Key"],
              'awsKey' => $content['Key'],
              'xmlTeam' => false);
        }
      }
    }
    
    $this->iterateThroughFiles();
  }
  
  protected function getClassToRunProcess($document){
    if($document['documentClass'] == 'schedules'){
      return 'Schedule';
    } else if($document['documentClass'] == 'event-summary'){
      if($document['fixtureKey'] == 'event-stats' || 
         $document['fixtureKey'] == 'event-stats-composite' ||
         $document['fixtureKey'] == 'event-stats-progressive' ||
         $document['fixtureKey'] == 'event-score'){
        return 'BoxScore';
      } else if($document['fixtureKey'] == 'odds-early' || $document['fixtureKey'] == 'odds'){
        return 'EarlyOdds';
      }
    }
    
    return false;
  }
  
  protected function iterateThroughFiles(){
    foreach($this->_filesToLoad as $file){
      
      if(strpos($file['file'], 'pre-event.xml') !== false){
        continue;
      }
      if(strpos($file['file'], 'mid-event.xml') !== false){
        continue;
      }
      if(strpos($file['file'], 'score-delayed.xml') !== false){
        continue;
      }
      
      $fileObj = $this->getFile($file);
      
      if($file['xmlTeam']){
        //We need to save the documents on our side.
        $saveAsFile = substr($file['file'], 15);
        $this->saveFileToS3($saveAsFile, $fileObj);
      }
      
      //Now with the document saved, we can move on and parse the document:
      $documentType = $this->parseDocumentType($fileObj);
    
      //Load the league based on the league sportsMlId:
      $leagues = new Leagues();
      $league = $leagues->getLeagueBySportsMlId($documentType['league']);
      
      //Now we know the namespace for the document type:
      $namespaceValue = str_replace(' ', '', $league->leagueName);
    
      //What class should we be calling?
      $className = $this->getClassToRunProcess($documentType);
    
      if(!$className) echo $documentType['documentClass'];
      
      echo 'Class Name: ' . $className . '; File Name: ' . $saveAsFile . ' - ';
      
      //Now, based on the document type, we can load the league specific actions for schedules, odds, and box scores
      call_user_func_array('\Application\Models\XMLTeam\Leagues\\' . $namespaceValue . '\\' . $className . '::processFile', Array($fileObj, $league));
    
      //Lastly we need to save the document info to the database for searching later if necessary:
    
    }
  }
  
  protected function loadArrayOfFiles($url){
    $additionalFiles = false;
    
    libxml_use_internal_errors(true);
    
    $doc = new \DOMDocument();
    $loadedFile = $this->getFile($url);
    $doc->loadHTML($loadedFile);
    
    $doc->normalizeDocument();
    $a = $doc->getElementsByTagName('a');
    
    foreach($a as $file){
      if($file->getAttribute('class') == 'file'){
        $this->_filesToLoad[] = Array(
            'file' => $this->_feedXml . $file->getAttribute('href'),
            'xmlTeam' => true);
      } else if($file->getAttribute('class') == 'folder'){
        $this->_url = $file->getAttribute('href');
        $additionalFiles = true;
      }
    }
    
    return $additionalFiles;
  }
  
  protected function parseDocumentType($fileObj){
    $return = Array();
    
    $doc = new \DOMDocument();
    $doc->loadXML($fileObj);
    
    //Get the info about the document. Is this a schedule, early odds, or a closing score document?
    $documentData = $doc->getElementsByTagName('sports-metadata');
    
    $documentClass = null;
    $fixtureKey = null;
    foreach($documentData as $data){
      $documentClass = $data->getAttribute('document-class');
      $fixtureKey = $data->getAttribute('fixture-key');
    }
    $return['documentClass'] = $documentClass;
    $return['fixtureKey'] = $fixtureKey;
    
    //Now we get data about the document concerning the league, team, etc.
    $documentData = $doc->getElementsByTagName('sports-content-code');
    
    $league = null;
    $team = null;
    foreach ($documentData as $header) {
      $type = $header->getAttribute('code-type');
      $key = $header->getAttribute('code-key');
    
      switch($type){
      	case 'league':
      	  $league = $key;
      	  break;
      	case 'team':
          if(!$team){
            $team = $key;
          } else if(is_array($team)){
            if(in_array($key, $team)){
              //Do nothing
            } else{
              $team[] = $key;
            }
          } else{
            if($key != $team){
              $team = Array($key, $team);
            }
          }
      	  break;
      }
    }
    
    //If this document is a schedule, this would have multiple teams involved.  We want to know so we can save it appropriately.
    
    $documentData = $doc->getElementsByTagName('team-metadata');
    
    foreach($documentData as $teams){
      $key = $teams->getAttribute('team-key');
      
      if(!$team){
        $team = $key;
      } else if(is_array($team)){
        if(in_array($key, $team)){
          //Do nothing
        } else{
          $team[] = $key;
        }
      } else{
        if($key != $team){
          $team = Array($key, $team);
        }
      }
    }
    
    //Lastly, we want to get the event info for this document, if there is one, or multiples if it includes info on multiples.
    $documentData = $doc->getElementsByTagName('event-metadata');
    
    $event = null;
    foreach ($documentData as $events) {
      $key = $events->getAttribute('event-key');
      
      if(!$event){
        $event = $key;
      } else if(is_array($event)){
        if(in_array($key, $event)){
          //Do nothing
        } else{
          $event[] = $key;
        }
      } else{
        $event = Array($event, $key);
      }
    }
    $return['league'] = $league;
    $return['team'] = $team;
    $return['event'] = $event;
    
    return $return;
  }
  
  protected function saveFileToS3($url, $body){
    $s3 = new S3();
    $s3->preserveXmlFile($url, $body);
  }
  
}