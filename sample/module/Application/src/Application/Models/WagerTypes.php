<?php

namespace Application\Models;

use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Application\Models\DbalConnector;
use Application\Models\Games;
use Application\Models\Teams;
use Application\Models\WagerTypesByLeagueId;

class WagerTypes extends DbalConnector{
  
  public $id;
  public $type;
  public $displayName;
  public $description;
  
  protected $_tableAdapter;
  
  public function exchangeArray($data){
    $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
    $this->type = (!empty($data['type'])) ? $data['type'] : $this->type;
    $this->displayName = (!empty($data['displayName'])) ? $data['displayName'] : $this->displayName;
    $this->description = (!empty($data['description'])) ? $data['description'] : $this->description;
  }
  
  public function getWagerTypeById($id){
    $wagerType = $this->getDbAdapter()->getWagerTypeById($id);
    return $wagerType;
  }
  
  public function getWagerTypes(){
    $wagerTypes = $this->getDbAdapter()->fetchAll();
    
    $return = Array();
    
    foreach($wagerTypes as $wagerType){
      $return[$wagerType->id]['display'] = $wagerType->displayName;
      $return[$wagerType->id]['name'] = $wagerType->type;
    }
    
    return $return;
  }
  
  public function getWagerOptions(Games $game){
    $wagerTypesByLeague = new WagerTypesByLeagueId();
    $wagerTypes = $wagerTypesByLeague->getWagerTypesByLeagueId($game->leagueId);
    $wagerOptions = Array();
    
    foreach($wagerTypes as $type){
      $wagerType = $this->getWagerTypeById($type->wagerTypeId);
      $wagerOptions[] = Array(
      	'id' => $wagerType->id,
        'type' => $wagerType->displayName,
        'description' => $wagerType->description,
        'options' => $this->getWagerTypeOptions($wagerType->type, $game)
      );
    }
    
    return $wagerOptions;
  }
  
  public function getWagerTypeByName($typeName){
    $wagerType = $this->getDbAdapter()->getWagerTypeByName($typeName);
    return $wagerType->id;
  }
  
  public function getWagerTypeDisplayOptions($type, $wager, $favoringTeamName, $otherTeamName){
    switch($type){
      case 'pointSpread':
        return $this->getPointSpreadDisplay($favoringTeamName, $otherTeamName, $wager);
        break;
      case 'moneyLine':
        return $this->getMoneyLineDisplay($favoringTeamName, $otherTeamName, $wager);
        break;
      case 'overUnder':
        return $this->getOverUnderDisplay($wager);
        break;
      case 'mlbPointSpread':
        return $this->getMlbPointSpreadDisplay($favoringTeamName, $otherTeamName, $wager);
        break;
    }
  }
  
  protected function getWagerTypeOptions($type, $game){
    switch($type){
    	case 'pointSpread':
    	  return $this->getPointSpreadOptions($game);
    	  break;
    	case 'moneyLine':
    	  return $this->getMoneyLineOptions($game);
    	  break;
    	case 'overUnder':
    	  return $this->getOverUnderOptions($game);
    	  break;
    	case 'mlbPointSpread':
    	  return $this->getMlbPointSpreadOptions($game);
    	  break;
    }
  }
  
  protected function getPointSpreadDisplay($favoringName, $otherTeamName, $wager){
    $custom = json_decode($wager->wagerCustom);
    if($custom->plus_minus == 'minus'){
      $plusMinusSign = '+';
      $plusMinusLanguage = 'recieve a handicap of ' . $this->roundValue($wager->wagerValue) . ', meaning they must either win the game, or lose by no more than';
      $plusMinusOpposite = '-';
      $plusMinusLanguageOpposite = 'win by more than';
    } else{
      $plusMinusSign = '+';
      $plusMinusLanguage = 'win by more than';
      $plusMinusOpposite = '-';
      $plusMinusLanguageOpposite = 'recieve a handicap of ' . $this->roundValue($wager->wagerValue) . ', meaning they must either win the game, or lose by no more than';
    }
    return Array(
        'advanced' => $favoringName . ' ' . $plusMinusSign . $this->roundValue(abs($wager->wagerValue)),
        'beginner' => $favoringName . ' ' . $plusMinusLanguage . ' ' . $this->roundValue(abs($wager->wagerValue)),
        'advanced_opposite' => $otherTeamName . ' ' . $plusMinusOpposite . $this->roundValue(abs($wager->wagerValue)),
        'beginner_opposite' => $otherTeamName . ' ' . $plusMinusLanguage . ' ' . $this->roundValue(abs($wager->wagerValue))
      );
  }
  
  protected function getMoneyLineDisplay($favoringName, $otherTeamName, $wager){
    return Array(
        'advanced' => $favoringName . ' to win',
        'beginner' => $favoringName . ' must win the game outright',
        'advanced_opposite' => $otherTeamName . ' to win',
        'beginner_opposite' => $otherTeamName . ' must win the game outright'
      );
  }
  
  protected function getOverUnderDisplay($wager){
    if($wager->favoringId === "1"){
      $overUnder = 'over';
      $overUnderOpposite = 'under';
    } else{
      $overUnder = 'under';
      $overUnderOpposite = 'over';
    }
    
    return Array(
        'advanced' => ucfirst($overUnder) . ' ' . $this->roundValue($wager->wagerValue),
        'beginner' => 'Both teams combined score must be ' . $overUnder . ' ' . $this->roundValue($wager->wagerValue),
        'advanced_opposite' => ucfirst($overUnderOpposite) . ' ' . $this->roundValue($wager->wagerValue),
        'beginner_opposite' => 'Both teams combined score must be ' . $overUnderOpposite . ' ' . $this->roundValue($wager->wagerValue),
      );
  }
  
  protected function getMlbPointSpreadDisplay($favoringName, $otherTeamName, $wager){
    if($wager->wagerValue == 1){ 
      $advanced = '+1.5';
      $beginner = 'recieve a handicap of 1 point, meaning they must win the game, or lose by no more than 1';
      $advancedOpposite = '-1.5';
      $beginnerOpposite = 'are a 1.5 run favorite, meaning they must win the game by at least 2 runs';
    } else{
      $advanced = '-1.5';
      $beginner = 'are a 1.5 run favorite, meaning they must win the game by at least 2 runs';
      $advancedOpposite = '+1.5';
      $beginnerOpposite = 'recieve a handicap of 1 point, meaning they must win the game, or lose by no more than 1';
    }
    
    return Array(
        'advanced' => $favoringName . ' ' . $advanced,
        'beginner' => $favoringName . ' ' . $beginner,
        'advanced_opposite' => $otherTeamName . ' ' . $advancedOpposite,
        'beginner_opposite' => $otherTeamName . ' ' . $beginnerOpposite
      );
  }
  
  public function get_pointSpread_custom($params){
    $customArray = Array(
    	'plus_minus' => $params['plus_minus']
    );
    
    return json_encode($customArray);
  }
  
  protected function getPointSpreadOptions(Games $game){
    //We need to get the teams to choose from for the point favorite
    $teams = new Teams($this->_serviceManager);
    $teamArray = Array(
    	'home' => $teams->getTeamById($game->homeTeamId),
      'visitor' => $teams->getTeamById($game->visitorTeamId)
    );
    
    return $this->loadView('pointspread', Array('teams' => $teamArray));
  }
  
  public function get_moneyLine_custom($params){
    return null;
  }
  
  protected function getMoneyLineOptions(Games $game){
    //We need to get the teams to choose from for the win
    $teams = new Teams($this->_serviceManager);
    $teamArray = Array(
        'home' => $teams->getTeamById($game->homeTeamId),
        'visitor' => $teams->getTeamById($game->visitorTeamId)
    );
    
    return $this->loadView('moneyline', Array('teams' => $teamArray));
  }
  
  public function get_overUnder_custom($params){
    return null;
  }
  
  protected function getOverUnderOptions(Games $game){
    //We need to get the teams to choose from for the win
    $teams = new Teams($this->_serviceManager);
    $teamArray = Array(
        'home' => $teams->getTeamById($game->homeTeamId),
        'visitor' => $teams->getTeamById($game->visitorTeamId)
    );
    
    return $this->loadView('overunder', Array('teams' => $teamArray));
  }
  
  protected function getMlbPointSpreadOptions(Games $game){
    //We need to get the teams to choose from for the win
    $teams = new Teams($this->_serviceManager);
    $teamArray = Array(
        'home' => $teams->getTeamById($game->homeTeamId),
        'visitor' => $teams->getTeamById($game->visitorTeamId)
    );
  
    return $this->loadView('mlbpointspread', Array('teams' => $teamArray));
  }
  
  public function get_mlbPointSpread_custom($params){
    return null;
  }
  
  private function loadView($view, $data){
    //Set the path to the view template.
    $resolver = new TemplateMapResolver();
    $resolver->setMap(array(
        'stepTemplate' => ROOT_PATH . '/module/BackOffice/view/back-office/wager/partials/wagerTypes/' . $view . '.phtml'
    ));
  
    //Create a view object and resolve the path base on the template map resolver above.
    $view = new PhpRenderer();
    $view->setResolver($resolver);
  
    //Create a view to use with the established template and add any variables that view will use.
    $viewModel = new ViewModel();
    $viewModel->setTemplate('stepTemplate')->setVariables(array(
        'data' => $data
    ));
  
    return $view->render($viewModel);
  }
  
  private function roundValue($value){
    $value = $value + 0;
    return $value;
  }
  
  public function setDbAdapter($dbAdapter){
    $this->_tableAdapter = $dbAdapter;
  }
  
  public function getDbAdapter(){
    if(!$this->_tableAdapter){
      $this->setDbAdapter($this->setTableGateway($this, 'wagertypes'));
    }
    return $this->_tableAdapter;
  }
  
}