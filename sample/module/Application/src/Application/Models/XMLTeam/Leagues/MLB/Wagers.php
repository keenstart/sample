<?php

namespace Application\Models\XMLTeam\Leagues\MLB;

use Application\Models\ProcessWagers;

class Wagers extends ProcessWagers{
  
  public static function mlbPointSpread($wager, $winningTeam, $winningTeamScore, $losingTeamScore){
    $wagerCustom = json_decode($wager->wagerCustom);
    
    //We need to see if the winning team is still the winning team after we calculate the point spread handicap:
    $adjustedWinningScore = $winningTeamScore;
    $adjustedLosingScore = $losingTeamScore;
    
    if($wager->favoringId == $winningTeam){
      //Now we calculate the plus/minus for the final score:
      if($wager->wagerValue == 1){
        $adjustedWinningScore = $winningTeamScore + 1.5;
      } else{
        $adjustedWinningScore = $winningTeamScore - 1.5;
      }
    } else{
      if($wager->wagerValue == 1){
        $adjustedLosingScore = $losingTeamScore + 1.5;
      } else{
        $adjustedLosingScore = $losingTeamScore - 1.5;
      }
    }
    
    //Still the same winner?
    if($adjustedWinningScore > $adjustedLosingScore){
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
  
}