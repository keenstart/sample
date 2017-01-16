<?php

namespace Application\Models\XMLTeam;

use Application\Models\Teams;
use Application\Models\Games;
use Application\Models\XMLTeam\ProcessDateTime;
use Application\Models\Wagers;

class XMLSchedule{
  
  protected static $_eventStatus = Array(
    'canceled' => -2,
    'postponed' => -1,
    'pre-event' => 1,
    'mid-event' => 2,
    'post-event' => 3,
  );
  
  public static function processFile($file = NULL, $league = NULL){
    
    $doc = new \DOMDocument();
    $doc->loadXML($file);
    
    $events = $doc->getElementsByTagName('sports-event');
    
    $now = new \DateTime();
    $now = $now->setTimezone(new \DateTimeZone('UTC'));
    
    //Load the event information: EventId, EventStatus, Time, Teams, Winners, etc.
    foreach ($events as $event) {
      $gameObj = new Games();
      
      $eventMetadata = $event->getElementsByTagName('event-metadata');
      
      $certain = true;
      $eventId = null;
      $eventStatus = self::$_eventStatus['pre-event'];
      $eventTime = null;
      
      foreach($eventMetadata as $meta){
        $eventId = $meta->getAttribute('event-key');
        
        $eventDateTime = $meta->getAttribute('start-date-time');
        
        if(isset(self::$_eventStatus[$meta->getAttribute('event-status')])){
          $eventStatus = self::$_eventStatus[$meta->getAttribute('event-status')];
        } else if($eventDateTime < $now) {
          $eventStatus = self::$_eventStatus['mid-event'];
        }
      }
      
      $games = $gameObj->getAllGamesBySportsMlId($eventId);
      
      if($games){
        foreach($games as $game){
          if(!$game || $game->gameStatus != self::$_eventStatus['post-event']){
          
            //We need to precess the eventDateTime
            $eventDateTime = ProcessDateTime::processTime($eventDateTime, true);
            
            //If the game was cancelled, we need to update the wagers that were involved:
            if($game && $game->gameStatus != self::$_eventStatus['canceled'] && $eventStatus === self::$_eventStatus['canceled']){
              $wager = new Wagers();
              $wager->cancelWagersByGameId($game->id);
            }
            
            $homeId = null;
            $awayId = null;
            $winnerId = null;
            
            $teams = $event->getElementsByTagName('team');
            foreach($teams as $team){
              $teamData = $team->getElementsByTagName('team-metadata');
              $teamStats = $team->getElementsByTagName('team-stats');
              
              $teamObj = new Teams();
              $teamVal = null;
              
              foreach($teamData as $data){
                
                $teamId = $data->getAttribute('team-key');
                $alignment = $data->getAttribute('alignment');
                
                $teamVal = $teamObj->getTeamBySportsMlId($teamId);
                if($teamVal){
                  if($alignment == 'home'){
                    $homeId = $teamVal->id;
                  } else{
                    $awayId = $teamVal->id;
                  }
                }
              }
              
              foreach($teamStats as $stat){
                $outcome = $stat->getAttribute('event-outcome');
                
                if($outcome == 'win'){
                  $winnerId = $teamVal->id;
                }
              }
            }
            
            if($homeId && $awayId){
              //Lastly, we need to save or update the game in the system:
              if(!$game) $game = $gameObj;
              if($game->gameStatus > $eventStatus) $eventStatus = $game->gameStatus;
              $updated = $now;
              $updateGamesArray = Array(
                  "sportsMlId" => $eventId,
                  "leagueId" => $league->id,
                  "homeTeamId" => $homeId,
                  "visitorTeamId"  => $awayId,
                  "winnerId" => $winnerId,
                  "datetime" => $eventDateTime->format('Y-m-d H:i:s'),
                  "gameStatus" => $eventStatus,
                  "updated" => $updated->format('Y-m-d H:i:s')
                );
              
              $game->exchangeArray($updateGamesArray);
              $game->saveGame();
            }
          }
        }
      } else{
        //We need to process the eventDateTime
        $eventDateTime = ProcessDateTime::processTime($eventDateTime, true);
        
        $homeId = null;
        $awayId = null;
        $winnerId = null;
        
        $teams = $event->getElementsByTagName('team');
        foreach($teams as $team){
          $teamData = $team->getElementsByTagName('team-metadata');
          $teamStats = $team->getElementsByTagName('team-stats');
        
          $teamObj = new Teams();
          $teamVal = null;
        
          foreach($teamData as $data){
        
            $teamId = $data->getAttribute('team-key');
            $alignment = $data->getAttribute('alignment');
        
            $teamVal = $teamObj->getTeamBySportsMlId($teamId);
            if($teamVal){
              if($alignment == 'home'){
                $homeId = $teamVal->id;
              } else{
                $awayId = $teamVal->id;
              }
            }
          }
        
          foreach($teamStats as $stat){
            $outcome = $stat->getAttribute('event-outcome');
        
            if($outcome == 'win'){
              $winnerId = $teamVal->id;
            }
          }
        }
        
        if($homeId && $awayId){
          //Lastly, we need to save or update the game in the system:
          $game = $gameObj;
          if($game->gameStatus > $eventStatus) $eventStatus = $game->gameStatus;
          $updated = $now;
          $updateGamesArray = Array(
              "sportsMlId" => $eventId,
              "leagueId" => $league->id,
              "homeTeamId" => $homeId,
              "visitorTeamId"  => $awayId,
              "winnerId" => $winnerId,
              "datetime" => $eventDateTime->format('Y-m-d H:i:s'),
              "gameStatus" => $eventStatus,
              "updated" => $updated->format('Y-m-d H:i:s')
          );
        
          $game->exchangeArray($updateGamesArray);
          $game->saveGame();
        }
      }
    }
  }
}