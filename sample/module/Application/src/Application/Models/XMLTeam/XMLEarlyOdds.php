<?php

namespace Application\Models\XMLTeam;

use Application\Models\Games;
use Application\Models\Teams;
use Application\Models\WagerTypes;
use Application\Models\XMLTeam\ProcessDateTime;
use Application\Models\Lines;
use Application\Models\Bookmaker;

class XMLEarlyOdds{
  
  protected static $_wagerType = Array(
      'wagering-moneyline' => 'moneyLine',
      'wagering-total-score' => 'overUnder',
      'wagering-straight-spread' => 'pointSpread'
  );
  
  protected static $_wagerTypesArray = Array();
  
  public static function processFile($file = NULL, $league = NULL){
    
    $doc = new \DOMDocument();
    $doc->loadXML($file);
    
    $wagerTypeObj = new WagerTypes();
    
    $sportsEvents = $doc->getElementsByTagName('sports-event');
    
    foreach($sportsEvents as $event){
      $eventData = $event->getElementsByTagName('event-metadata');
    
      $gameObj = new Games();
      $games = null;
    
      foreach($eventData as $data){
        $eventKey = $data->getAttribute('event-key');
        $games = $gameObj->getAllGamesBySportsMlId($eventKey);
      }
    
      foreach($games as $game){
        $teams = $event->getElementsByTagName('team');
        $team = new Teams();
        
        foreach($teams as $teamGroup){
          $teamMetadata = $teamGroup->getElementsByTagName('team-metadata');
    
          foreach($teamMetadata as $teamObj){
            $teamKey = $teamObj->getAttribute('team-key');
            $team = $team->getTeamBySportsMlId($teamKey);
          }
    
          $wageringStats = $teamGroup->getElementsByTagName('wagering-stats');
    
          foreach($wageringStats as $stat){
            foreach(self::$_wagerType as $type=>$dbName){
              
              //Run method for each type here
              $lineInfo = $stat->getElementsByTagName($type);
              if($lineInfo){
                call_user_func_array('self::process_' . $dbName, Array($lineInfo, $team, $game));
              }
            }
          }
        }
      }
    }
  }
  
  protected static function process_moneyLine($lineInfoObj, $team, $game){
    foreach($lineInfoObj as $lineInfo){
      $wagerType = new WagerTypes();
      $wagerType = $wagerType->getWagerTypeByName('moneyLine');
      
      $line = new Lines();
      $existingLine = $line->getLineByGameTeamWagerTypeId($game->id, $team->id, $wagerType);
      $bookmaker = new Bookmaker();
      if($existingLine){
        $array = Array(
          'datetime' => ProcessDateTime::processTime($lineInfo->getAttribute('date-time')),
          'value' => $lineInfo->getAttribute('line'),
          'valueOpening' => $lineInfo->getAttribute('line-opening'),
          'prediction' => $lineInfo->getAttribute('prediction'),
          'predictionOpening' => $lineInfo->getAttribute('prediction-opening'),
          'wagerMakerId' => $bookmaker->getBookmaker($lineInfo->getAttribute('bookmaker-key'), $lineInfo->getAttribute('bookmaker-name'))
        );
        $existingLine->exchangeArray($array);
        $existingLine->saveLine();
      } else{
        $array = Array(
          'gameId' => $game->id,
          'teamId' => $team->id,
          'wagerTypeId' => $wagerType,
          'datetime' => ProcessDateTime::processTime($lineInfo->getAttribute('date-time')),
          'value' => $lineInfo->getAttribute('line'),
          'valueOpening' => $lineInfo->getAttribute('line-opening'),
          'prediction' => $lineInfo->getAttribute('prediction'),
          'predictionOpening' => $lineInfo->getAttribute('prediction-opening'),
          'wagerMakerId' => $bookmaker->getBookmaker($lineInfo->getAttribute('bookmaker-key'), $lineInfo->getAttribute('bookmaker-name'))
        );
        $line->exchangeArray($array);
        $line->saveLine();
      }
    }
  }
  
  protected static function process_overUnder($lineInfoObj, $team, $game){
    foreach($lineInfoObj as $lineInfo){
      $wagerType = new WagerTypes();
      $wagerType = $wagerType->getWagerTypeByName('overUnder');
      
      $line = new Lines();
      $existingLine = $line->getLineByGameTeamWagerTypeId($game->id, $team->id, $wagerType);
      $bookmaker = new Bookmaker();
      if($existingLine){
        $array = Array(
            'datetime' => ProcessDateTime::processTime($lineInfo->getAttribute('date-time')),
            'value' => $lineInfo->getAttribute('total'),
            'valueOpening' => $lineInfo->getAttribute('total-opening'),
            'prediction' => $lineInfo->getAttribute('prediction'),
            'predictionOpening' => $lineInfo->getAttribute('prediction-opening'),
            'wagerMakerId' => $bookmaker->getBookmaker($lineInfo->getAttribute('bookmaker-key'), $lineInfo->getAttribute('bookmaker-name'))
        );
        $existingLine->exchangeArray($array);
        $existingLine->saveLine();
      } else{
        $array = Array(
            'gameId' => $game->id,
            'teamId' => $team->id,
            'wagerTypeId' => $wagerType,
            'datetime' => ProcessDateTime::processTime($lineInfo->getAttribute('date-time')),
            'value' => $lineInfo->getAttribute('total'),
            'valueOpening' => $lineInfo->getAttribute('total-opening'),
            'prediction' => $lineInfo->getAttribute('prediction'),
            'predictionOpening' => $lineInfo->getAttribute('prediction-opening'),
            'wagerMakerId' => $bookmaker->getBookmaker($lineInfo->getAttribute('bookmaker-key'), $lineInfo->getAttribute('bookmaker-name'))
        );
        $line->exchangeArray($array);
        $line->saveLine();
      }
    }
  }
  
  protected static function process_pointSpread($lineInfoObj, $team, $game){
    foreach($lineInfoObj as $lineInfo){
      $wagerType = new WagerTypes();
      $wagerType = $wagerType->getWagerTypeByName('pointSpread');
      
      $line = new Lines();
      $existingLine = $line->getLineByGameTeamWagerTypeId($game->id, $team->id, $wagerType);
      $bookmaker = new Bookmaker();
      if($existingLine){
        $array = Array(
            'datetime' => ProcessDateTime::processTime($lineInfo->getAttribute('date-time')),
            'value' => $lineInfo->getAttribute('value'),
            'valueOpening' => $lineInfo->getAttribute('value-opening'),
            'prediction' => $lineInfo->getAttribute('prediction'),
            'predictionOpening' => $lineInfo->getAttribute('prediction-opening'),
            'wagerMakerId' => $bookmaker->getBookmaker($lineInfo->getAttribute('bookmaker-key'), $lineInfo->getAttribute('bookmaker-name'))
        );
        $existingLine->exchangeArray($array);
        $existingLine->saveLine();
      } else{
        $array = Array(
            'gameId' => $game->id,
            'teamId' => $team->id,
            'wagerTypeId' => $wagerType,
            'datetime' => ProcessDateTime::processTime($lineInfo->getAttribute('date-time')),
            'value' => $lineInfo->getAttribute('value'),
            'valueOpening' => $lineInfo->getAttribute('value-opening'),
            'prediction' => $lineInfo->getAttribute('prediction'),
            'predictionOpening' => $lineInfo->getAttribute('prediction-opening'),
            'wagerMakerId' => $bookmaker->getBookmaker($lineInfo->getAttribute('bookmaker-key'), $lineInfo->getAttribute('bookmaker-name'))
        );
        $line->exchangeArray($array);
        $line->saveLine();
      }
    }
  }
}