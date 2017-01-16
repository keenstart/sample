<?php

namespace Application\Models;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Models\Wagers;
use Zend\Db\ResultSet\ResultSet;

class WagersTable{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway = null){
    $this->table = 'wagers';
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll(){
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }
  
  public function getWagerById($id){
    $id = (int) $id;
    $rowset = $this->tableGateway->select(function(Select $select) use ($id){
      $select->where->equalTo('id', $id);
    });
    
    $row = $rowset->current();
    if(!$row){
      return null;
    }
    return $row;
  }
  
  public function getAllWagersByFinishedGames(){
    $response = $this->tableGateway->select(function(Select $select){
      $select->join('games', 'games.id = wagers.gameId', array('gameStatus'));
      $select->where->equalTo('gameStatus', 3);
    });
    return $response;
  }
  
  public function getAllIncompleteWagersOfFinishedGames(){
    $response = $this->tableGateway->select(function(Select $select){
      $select->where->notEqualTo('status', 2);
      $select->join('games', 'games.id = wagers.gameId', array('gameStatus'));
      $select->where->equalTo('gameStatus', 3);
    });
    return $response;
  }
  
  public function getActiveWagerCountForUser($userId){
    $response = $this->tableGateway->select(function(Select $select) use ($userId){
      $select->where->equalTo('placerUserId', $userId);
      $select->where->AND->equalTo('status', 1);
      $select->where->OR->equalTo('takerUserId', $userId);
      $select->where->AND->equalTo('status', 1);
    });
    return $response->count();
  }
  
  public function getCompletedWagerCountForUser($userId){
    $response = $this->tableGateway->select(function(Select $select) use ($userId){
      $select->where->equalTo('placerUserId', $userId);
      $select->where->AND->equalTo('status', 2);
      $select->where->OR->equalTo('takerUserId', $userId);
      $select->where->AND->equalTo('status', 2);
    });
    return $response->count();
  }
  
  public function getAllUnresolvedWagersByEvent($eventId){
    $resultSet = $this->tableGateway->select(function(Select $select) use ($eventId){
      $select->where->equalTo('status', 1);
      $select->where->equalTo('gameId', $eventId);
      $select->where->isNotNull('takerUserId');
    });
    return $resultSet;
  }
  
  public function getAllCurrentWagers(){
    $resultSet = $this->tableGateway->select(function(Select $select){
      $select->where->equalTo('status', 1);
    });
    
    return $resultSet;
  }
  
  public function getAllAvailableWagers($user, $request){
    $resultSet = $this->tableGateway->select(function(Select $select) use ($user, $request){
      $select->join('games', 'games.id = wagers.gameId', array('datetime'));
      $select->where->equalTo('status', 1);
      $select->where->AND->isNull('takerUserId');
      $date = new \DateTime();
      $date->setTimeZone(new \DateTimeZone("UTC"));
      $select->where->AND->greaterThan('datetime', $date->format('Y-m-d H:i:s'));
      //if($user) $select->where->notEqualTo('placerUserId', $user);
      $select->order('games.datetime ASC');
      if($request && $request->getQuery()->offset) $select->offset(intval($request->getQuery()->offset));
    });
    
    return $resultSet;
  }
  
  public function getAllAvailableWagersBySortValues($user, $sort){
    $resultSet = $this->tableGateway->select(function(Select $select) use ($user, $sort){
      $select->join('games', 'games.id = wagers.gameId', array('datetime', 'homeTeamId', 'visitorTeamId'));
      $select->where->equalTo('status', 1);
      $select->where->AND->isNull('takerUserId');
      $date = new \DateTime();
      $date->setTimeZone(new \DateTimeZone("UTC"));
      $select->where->AND->greaterThan('datetime', $date->format('Y-m-d H:i:s'));
      if($user) $select->where->notEqualTo('placerUserId', $user);
      if(isset($sort['sort_league']) && $sort['sort_league'] != '0') $select->where->equalTo('wagers.leagueId', $sort['sort_league']);
      if(isset($sort['sort_team']) && $sort['sort_team'] != '0'){
        $select->where->AND->equalTo('homeTeamId', $sort['sort_team']);
        $select->where->OR->equalTo('status', 1);
        $select->where->AND->isNull('takerUserId');
        if($user) $select->where->notEqualTo('placerUserId', $user);
        if(isset($sort['sort_league']) && $sort['sort_league'] != '0') $select->where->equalTo('wagers.leagueId', $sort['sort_league']);
        $select->where->AND->equalTo('visitorTeamId', $sort['sort_team']);
        $select->where->AND->greaterThan('datetime', $date->format('Y-m-d H:i:s'));
      }
      $select->order('games.datetime ASC');
    });
    
    return $resultSet;
  }
  
  public function getAllAvailableWagersForGame($gameId, $userId){
    $resultSet = $this->tableGateway->select(function(Select $select) use ($gameId, $userId){
      $select->join('games', 'games.id = wagers.gameId', array('datetime', 'homeTeamId', 'visitorTeamId'));
      $select->where->equalTo('status', 1);
      $select->where->AND->isNull('takerUserId');
      $select->where->AND->equalTo('gameId', $gameId);
      if($userId) $select->where->notEqualTo('placerUserId', $userId);
    });
    
    return $resultSet;
  }
  
  public function getAllWagersForGame($gameId){
    $resultSet = $this->tableGateway->select(function(Select $select) use ($gameId){
      $select->where->equalTo('gameId', $gameId);
    });
    
    return $resultSet;
  }
  
  public function getAllCurrentWagersByUser($userId){
    $now = new \DateTime();
    $rowset = $this->tableGateway->select(function(Select $select) use ($userId, $now){
      $select->join('games', 'games.id = wagers.gameId', array('datetime'));
      $select->where->equalTo('placerUserId', $userId);
      $select->where->AND->isNull('takerUserId');
      $select->where->AND->equalTo('status', 1);
      $select->where->AND->lessThan('gameStatus', 2);
      $select->where->AND->greaterThan('games.datetime', $now->format('Y-m-d H:i:s'));
      $select->where->OR->equalTo('takerUserId', $userId);
      $select->where->AND->equalTo('status', 1);
      $select->where->AND->lessThan('gameStatus', 3);
      $select->where->OR->equalTo('placerUserId', $userId);
      $select->where->AND->equalTo('status', 1);
      $select->where->AND->lessThan('gameStatus', 3);
      $select->where->AND->isNotNull('takerUserId');
      $select->order('games.datetime ASC');
    });
    return $rowset;
  }
  
  public function getAllPastWagersByUser($userId){
    $rowset = $this->tableGateway->select(function(Select $select) use ($userId){
      $select->join('games', 'games.id = wagers.gameId');
      $select->where->equalTo('placerUserId', $userId);
      $select->where->AND->equalTo('status', 2);
      $select->where->OR->equalTo('takerUserId', $userId);
      $select->where->AND->equalTo('status', 2);
      $select->order('games.datetime DESC');
    });
    return $rowset;
  }
  
  public function getAllUnfundedWagersByUser($userId){
    $now = new \DateTime();
    $rowset = $this->tableGateway->select(function(Select $select) use ($userId, $now){
      $select->join('games', 'games.id = wagers.gameId', array('datetime'));
      $select->where->equalTo('placerUserId', $userId);
      $select->where->AND->equalTo('status', -1);
      $select->where->AND->greaterThan('games.datetime', $now->format('Y-m-d H:i:s'));
      $select->where->OR->equalTo('takerUserId', $userId);
      $select->where->AND->equalTo('status', -1);
      $select->where->AND->greaterThan('games.datetime', $now->format('Y-m-d H:i:s'));
      $select->order('games.datetime ASC');
    });
    return $rowset;
  }
  
  public function getUnfundedWagersByUser($userId){
    $rowset = $this->tableGateway->select(function(Select $select) use ($userId){
      $select->where->equalTo('placerUserId', $userId);
    });
    return $rowset;
  }
  
  public function getUnfundedWagerCount($userId){
    $now = new \DateTime();
    $now->setTimezone(new \DateTimeZone('UTC'));
    $rowset = $this->tableGateway->select(function(Select $select) use ($userId, $now){
      $select->join('games', 'games.id = wagers.gameId', array('datetime'));
      $select->where->equalTo('placerUserId', $userId);
      $select->where->AND->equalTo('status', -1); 
      $select->where->AND->greaterThan('games.datetime', $now->format('Y-m-d H:i:s'));
    });
    
    return count($rowset);
  }
  
  public function getUntakenWagers($now){
    
    $resultSet = $this->tableGateway->select(function(Select $select) use ($now){
      $select->join('games', 'games.id = wagers.gameId', array('datetime', 'homeTeamId', 'visitorTeamId'));
      $select->where->equalTo('status', 1);
      $select->where->AND->isNull('takerUserId');
      $select->where->AND->lessThan('games.datetime', $now->format('Y-m-d H:i:s'));
    });
    
    return $resultSet;
    
  }
  
  public function saveWager(Wagers $wager){
    $data = array();
    foreach($wager as $key => $value){
      $data[$key] = $value;
    }
  
    $id = (int) $wager->id;
  
    if($id == 0){
      $this->tableGateway->insert($data);
    } else {
      if($this->getWagerById($id)){
        $this->tableGateway->update($data, array('id' => $id));
        return true;
      } else{
        throw new \Exception('Error inserting record');
      }
    }
    return $this->tableGateway->lastInsertValue;
  }
}