<?php 

namespace Application\Models\XMLTeam;

use Application\Models\XMLTeam\XMLTeam;
use Application\Models\Leagues;
use Application\Models\Teams;

class LoadLeagueInfo extends XMLTeam{
  
  private $_fileLocations = Array(
      'NBA' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.nba.com.xml'),
          'databaseName' => 'NBA'),
      'MLB' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.mlb.com.xml'),
          'databaseName' => 'MLB'),
      'NCAABasketball' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.ncaa.org.mbasket.xml'),
          'databaseName' => 'NCAA Basketball'),
      'NCAAFootball' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.ncaa.org.mfoot.xml',
              'http://private.xmlteam.com/league-directory/xt.tsn.l.ncaa.org.mfoot.div1.aa.xml'),
          'databaseName' => 'NCAA Football'),
      'NFL' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.nfl.com.xml'),
          'databaseName' => 'NFL'),
      'NHL' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.nhl.com.xml'),
          'databaseName' => 'NHL'),
      'MLS' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.tsn.l.mlsnet.com.xml'),
          'databaseName' => 'MLS'),
      'EnglishPremierLeague' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.inf.l.premierleague.com.xml'),
          'databaseName' => 'English Premier League'),
      'LaLigaPrimeraDivision' => Array(
          'dataUrl' => Array('http://private.xmlteam.com/league-directory/xt.inf.l.lfp.es.primera.xml'),
          'databaseName' => 'La Liga Primera Division'
        )
  );
  
  public function UpdateTeams($league = Array()){
    if(empty($league)){
      $league = Array('NBA', 'MLB', 'NCAABasketball', 'NCAAFootball', 'NFL', 'NHL', 'MLS', 'EnglishPremierLeague', 'LaLigaPrimeraDivision');
    }
    
    foreach($league as $l){
      foreach($this->_fileLocations[$l]['dataUrl'] as $url){
        $teams = $this->getFile($url);
        
        $dom = new \DOMDocument;
        $dom->loadXML($teams);
        $xmlHeaders = $dom->getElementsByTagName('sports-content-code');
        
        $leagues = new Leagues();
        $leagueDb = $leagues->getLeagueByName($this->_fileLocations[$l]['databaseName']);
        foreach ($xmlHeaders as $header) {
          $type = $header->getAttribute('code-type');
          $key = $header->getAttribute('code-key');
          
          switch($type){
          	case 'sport':
          	  $leagueDb->sportId = $key;
          	  break;
          	case 'league':
          	  $leagueDb->sportsMlId = $key;
          	  break; 
          }
        }
        
        $leagueDb->saveLeague();

        $teams = $dom->getElementsByTagName('team-metadata');
        $teamsObj = new Teams();
        
        foreach($teams as $team){
          $key = $team->getAttribute('team-key');
          //First check to see if the team exists in the database:
          $teamDb = $teamsObj->getTeamBySportsMlId($key);
          
          $name = $team->getElementsByTagName('name');
          $teamName = NULL;
          $teamAbbrev = NULL;
          foreach($name as $name){
            $teamName = $name->getAttribute('full');
            $teamAbbrev = $name->getAttribute('abbreviation');
          }
          
          $exchangeArray = Array(
            "teamName" => $teamName,
            "leagueId" => $leagueDb->id,
            "sportsMlAbbrev" => $teamAbbrev,
            "sportsMlId" => $key
          );
          
          if(!$teamDb){
            $teamDb = $teamsObj;
          }
          $teamDb->exchangeArray($exchangeArray);
          $teamDb->saveTeam();
        }
      }
    }
  }
  
}