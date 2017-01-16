<?php

namespace Application\Models\XMLTeam;

use Application\Models\Games;
use Application\Models\Wagers;
use Application\Models\Teams;
use Application\Models\Stats;

class XMLBoxScores{
  
  protected static $_eventStatus = Array(
    'pre-event' => 1,
    'mid-event' => 2,
    'undecided' => 2,
    'post-event' => 3,
    'postponed' => -1,
    'canceled' => -2
  );
  
  protected static $_winLoss = Array(
      'win' => true,
      'loss' => false
  );
  
  public static function processFile($file = NULL, $league = NULL){
    
    $doc = new \DOMDocument();
    $doc->loadXML($file);
    
    $sportsEvents = $doc->getElementsByTagName('sports-event');
    
    foreach($sportsEvents as $event){
      $eventData = $event->getElementsByTagName('event-metadata');
      
      $gameObj = new Games();
      $games = Array();
      $eventStatus = null;
      
      foreach($eventData as $data){
        $eventKey = $data->getAttribute('event-key');
        $games = $gameObj->getAllGamesBySportsMlId($eventKey);
        $eventStatus = self::$_eventStatus[$data->getAttribute('event-status')];
      }
      
      foreach($games as $game){
        $teams = $event->getElementsByTagName('team');
        
        $winningTeam = null;
        $winningScore = null;
        $losingScore = null;
        
        foreach($teams as $teamGroup){
          $teamMetadata = $teamGroup->getElementsByTagName('team-metadata');
          $teamId = null;
          
          foreach($teamMetadata as $team){
            $teamObj = new Teams();
            $teamId = $team->getAttribute('team-key');
          }
          
          $teamStats = $teamGroup->getElementsByTagName('team-stats');
          
          $winLoss = null;
                    
          foreach($teamStats as $stat){
            $winLoss = self::$_winLoss[$stat->getAttribute('event-outcome')];
            if($winLoss){
              $winningScore = $stat->getAttribute('score');
              $losingScore = $stat->getAttribute('score-opposing');
            }
          }
          
          $teamVal = $teamObj->getTeamBySportsMlId($teamId);
          if($winLoss) $winningTeam = $teamVal->id;
          
          $stats = new Stats();
          $stats->exchangeArray(Array('gameId' => $game->id, 'teamId' => $teamVal->id, 'teamScore' => $stat->getAttribute('score')));
          $stats->saveGameStats();
        }
        
        if($eventStatus === 3 && $game->gameStatus !== 3){
          $game->exchangeArray(Array(
              'winnerId' => $winningTeam,
              'gameStatus' => $eventStatus
          ));
          
          $game->saveGame();
        } else{
          $game->exchangeArray(Array(
              'gameStatus' => $eventStatus
          ));
          $game->saveGame();
        }
      }
    }
  }
}