<?php

namespace Application\Models;

class ProcessWagers{
  
  public static function pointSpread($wager, $winningTeam, $winningTeamScore, $losingTeamScore){
    
    $wagerCustom = json_decode($wager->wagerCustom);
    $plusMinus = $wagerCustom->plus_minus;

    //We need to see if the winning team is still the winning team after we calculate the point spread handicap:
    $adjustedWinningScore = $winningTeamScore;
    $adjustedLosingScore = $losingTeamScore;
    
    if($wager->favoringId == $winningTeam){
      //Now we calculate the plus/minus for the final score:
      if($plusMinus == 'plus'){
        $adjustedWinningScore = $winningTeamScore + $wager->wagerValue;
      } else{
        $adjustedWinningScore = $winningTeamScore - $wager->wagerValue;
      }
    } else{
      if($plusMinus == 'plus'){
        $adjustedLosingScore = $losingTeamScore + $wager->wagerValue;
      } else{
        $adjustedLosingScore = $losingTeamScore - $wager->wagerValue;
      }
    }
    
    //Still the same winner?
    if($adjustedWinningScore == $adjustedLosingScore){
      return 'tie';
    } else if($adjustedWinningScore > $adjustedLosingScore){
      if($wager->favoringId == $winningTeam){
        return 'placer';
      } else{
        return 'taker';
      }
    } else{
      if($wager->favoringId != $winningTeam){
        return 'placer';
      } else{
        return 'taker';
      }
    }
  }
  
  public static function moneyLine($wager, $winningTeam, $winningTeamScore, $losingTeamScore){
    if($wager->favoringId == $winningTeam){
      return 'placer';
    } else{
      return 'taker';
    }
  }
  
  public static function overUnder($wager, $winningTeam, $winningTeamScore, $losingTeamScore){
    //Calculate total score:
    $totalScore = $winningTeamScore + $losingTeamScore;
    $outcome = null;
    
    if($totalScore > $wager->wagerValue){
      $outcome = 1;
    } else if($totalScore < $wager->wagerValue){
      $outcome = -1;
    } else{
      $outcome = 0;
    }
    
    if($outcome === 0){
      return 'tie';
    } else if(intval($outcome) === intval($wager->favoringId)){
      return 'placer';
    } else{
      return 'taker';
    }
  } 
}