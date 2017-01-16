<?php

namespace Application\Models;

use Application\Models\Stats;
use Application\Models\Games;
use Application\Models\Lines;

class CronDbAccess extends DbalConnector{
  
  protected $_dbAdapter;
  protected $_table;
  
  protected $_tableAdapter = null;
  
  public function __construct($dbAdapter=null, $table=null){
    $this->_dbAdapter = $dbAdapter;
    $this->_table = $table;
  }
  
  public function getAllStatsUpdatesSinceLastPull($lastId){
    $stats = $this->getDbAdapter()->getAllStatsUpdatesSinceLastPull($lastId);
    $lastId = 1;
    foreach($stats as $stat){
      $statsObject = new Stats();
      $statsArray = get_object_vars($stat);
      $statsObject->exchangeArray($statsArray);
      $statsObject->saveGameStats();
      $lastId = $statsArray['id'];
    }
    return $lastId;
  }
  
  public function getAllUpdatesSinceLastPull($lastId){
    $games = $this->getDbAdapter()->getAllUpdatesSinceLastPull($lastId);
    $lastId = 1;
    foreach($games as $game){
      $gameArray = get_object_vars($game);
      $gameObject = new Games();
      $gameObject->exchangeArray($gameArray);
      $insertedGame = $gameObject->saveGame();
      
      if($insertedGame->winnerId){
        $wager = new Wagers();
        $wager->resolveByGame($insertedGame);
      }
      $lastId = $gameArray['id'];
    }
    return $lastId;
  }
  
  public function getAllLinesUpdatesSinceLastPull($lastId){
    $lines = $this->getDbAdapter()->getAllLinesUpdatesSinceLastPull($lastId);
    $lastId = 1;
    foreach($lines as $line){
      $lineArray = get_object_vars($line);
      $lineObject = new Lines();
      $lineObject->exchangeArray($lineArray);
      $insertedLine = $lineObject->saveLine();
      
      $lastId = $lineArray['id'];
    }
    return $lastId;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, $this->_table, $this->_dbAdapter));
    }
    return $this->_tableAdapter;
  }
  
}