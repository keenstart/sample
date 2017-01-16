<?php

namespace Application\Models\The;


use Application\Models\DbalConnector;
use Zend\Session\Container;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class TheWagers extends DbalConnector
{
    public $id;
    public $wagerId;
    public $userAskId;
    public $userAcceptId;
    public $riskAmount;
    public $askResult;
    public $acceptResult;
    public $commentAsk;
    public $commentAccept;
    public $winAmount;
    public $consoleId;
    public $gameId;
    public $typeId;
    public $status;
    public $consoleUsername;
    public $consoleUsernameAccept;
    public $askRules;
    public $created;
    public $updated;
    public $gameStartTime;
    public $gameResultTime;
    

    
    protected $_tableAdapter;
    protected $_userSession;

    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : $this->id;
        $this->wagerId = (!empty($data['wagerId'])) ? $data['wagerId'] : $this->wagerId;
        $this->userAskId = (!empty($data['userAskId'])) ? $data['userAskId'] : $this->userAskId;
        $this->userAcceptId = (!empty($data['userAcceptId'])) ? $data['userAcceptId'] : $this->userAcceptId;
        $this->riskAmount = (!empty($data['riskAmount'])) ? $data['riskAmount'] : $this->riskAmount;
        $this->winAmount = (!empty($data['winAmount'])) ? $data['winAmount'] : $this->winAmount;
        
        $this->askResult = (!empty($data['askResult'])) ? $data['askResult'] : $this->askResult;
        $this->acceptResult = (!empty($data['acceptResult'])) ? $data['acceptResult'] : $this->acceptResult;
        $this->commentAsk = (!empty($data['commentAsk'])) ? $data['commentAsk'] : $this->commentAsk;  
        $this->commentAccept = (!empty($data['commentAccept'])) ? $data['commentAccept'] : $this->commentAccept; 
        
        $this->consoleUsername = (!empty($data['consoleUsername'])) ? $data['consoleUsername'] : $this->consoleUsername; 
        $this->consoleUsernameAccept = (!empty($data['consoleUsernameAccept'])) ? $data['consoleUsernameAccept'] : $this->consoleUsernameAccept; 
        $this->askRules = (!empty($data['askRules'])) ? $data['askRules'] : $this->askRules; 

        $this->consoleId = (!empty($data['consoleId'])) ? $data['consoleId'] : $this->consoleId;
        $this->gameId = (isset($data['gameId'])) ? $data['gameId'] : $this->gameId;
        $this->typeId = (isset($data['typeId'])) ? $data['typeId'] : $this->typeId;   
        $this->status = (isset($data['status'])) ? $data['status'] : $this->status;   
        $this->created = (!empty($data['created'])) ? $data['created'] : $this->created;
        $this->updated = (!empty($data['updated'])) ? $data['updated'] : $this->updated;     
        $this->gameStartTime = (!empty($data['gameStartTime'])) ? $data['gameStartTime'] : $this->gameStartTime;      
        $this->gameResultTime = (!empty($data['gameResultTime'])) ? $data['gameResultTime'] : $this->gameResultTime;         
    }

    public function makeTheWager($thewagerParams)
    {
       $this->exchangeArray($thewagerParams);
      
        $this->userAskId = $this->getUserSession()->user->id;
        //$this->typeId = 0; 
        $this->acceptResult = 0;
        $this->askResult = 0;
                
        $this->status = 1;
        $this->winAmount = 0;
        $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));
        $this->created = $dateTime->format('Y-m-d H:i:s');
        $this->wagerId = 100000;
        $newMakeWager = $this->saveTheWager();

        return $newMakeWager;
    }

    public function setStatusTheWager($thewagerParams)
    {
        $this->exchangeArray($thewagerParams);
      
        $newMakeWager = $this->saveTheWager();

        return $newMakeWager;
    }

    public function setMatchResult($thewagerParams)
    {
        $this->exchangeArray($thewagerParams);
      
        $newMakeWager = $this->saveTheWager();

        return $newMakeWager;
    }

    public function saveTheWager()
    {
        $id = $this->getDbAdapter()->saveTheWager($this);
        return $this->getDbAdapter()->getTheWagerById($id);
    }

    public function getMyWagers()
    {
        return $this->getDbAdapter()->getMyWagers($this->getUserSession()->user->id);
    }
    
    public function getWagerById($id)
    {
        return $this->getDbAdapter()->getTheWagerById($id);
    }
       
    public function getMyWagersWithConsoleGames()
    {
        $userId =$this->getUserSession()->user->id;
        
        $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));
        $dateTime->modify('-2 hour');
        $created = $dateTime->format('Y-m-d H:i:s');    
              

        $where = new Where();
        $where->equalTo('w.userAskId', $userId);
        $where->AND->in('w.status',array(1,2,7));
        $where->AND->notEqualTo('w.typeId',1);
        $where->AND->notIn('w.askResult',array(4,5,6));
        //$where->OR->equalTo('w.userAskId', $userId);
        //$where->AND->equalTo('w.status',1);
        //$where->AND->notEqualTo('w.typeId',1);
        //$where->AND->greaterThan('w.created',$created);        
        $where->OR->equalTo('w.userAcceptId', $userId);
        $where->AND->in('w.status',array(2,7));
        $where->AND->notEqualTo('w.typeId',1);
        $where->AND->notIn('w.acceptResult',array(4,5,6));        
        $where->OR->equalTo('w.userAcceptId', $userId);
        $where->AND->equalTo('w.status',1);
        $where->AND->notEqualTo('w.typeId',1);
        $where->AND->greaterThan('w.created',$created);

                

        
        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('w'=>'thewagers'))
                ->join(array('c'=>'theconsoles'),'c.id = w.consoleId',array('consoleName','whichConsole'))
                ->join(array('g'=>'thegames'),'g.id = w.gameId',array('gameName'))
                ->join(array('u'=>'users'),'u.id = w.userAcceptId',array('acceptusername'=>'username'),'left')
                ->join(array('ur'=>'users'),'ur.id = w.userAskId',array('askusername'=>'username'))
                ->where($where)
                ->order('created DESC');

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $t = $results->count();
        return $results;
    }
    
    public function getHistoryCount()
        {
            $userId =$this->getUserSession()->user->id;

            $where = new Where();
            $where->equalTo('w.userAskId', $userId);
            $where->AND->equalTo('w.status',5);
            //$where->AND->isNotNull('w.askResult');
            $where->OR->equalTo('w.userAcceptId', $userId);
            //$where->AND->isNotNull('w.acceptResult');
            $where->AND->equalTo('w.status',5);

            $sql = new Sql($this->_dbAdapter);
            $select = $sql->select();
            $select->from(array('w'=>'thewagers'))
                    ->where($where)
                    ->order('created DESC');

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            //$t = $results->count();
            return $results->count();
        }
        
        public function getHistoryWinsCount()
        {
            $userId =$this->getUserSession()->user->id;

            $where = new Where();
            $where->equalTo('w.userAskId', $userId);
            $where->AND->equalTo('w.status',5);
            $where->AND->in('w.askResult', array(1,5));
            //$where->AND->isNotNull('w.askResult');

            $where->OR->equalTo('w.userAcceptId', $userId);
            //$where->AND->isNotNull('w.acceptResult');
            $where->AND->equalTo('w.status',5);
            $where->AND->in('w.acceptResult', array(1,5));

            $sql = new Sql($this->_dbAdapter);
            $select = $sql->select();
            $select->from(array('w'=>'thewagers'))
                    ->where($where)
                    ->order('created DESC');

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            //$t = $results->count();
            return $results->count();
        }
        
        public function getHistoryLossesCount()
        {
            $userId =$this->getUserSession()->user->id;

            $where = new Where();
            $where->equalTo('w.userAskId', $userId);
            $where->AND->equalTo('w.status',5);
            $where->AND->in('w.askResult', array(2,6));
            //$where->AND->isNotNull('w.askResult');
            $where->OR->equalTo('w.userAcceptId', $userId);
            //$where->AND->isNotNull('w.acceptResult');
            $where->AND->equalTo('w.status',5);
            $where->AND->in('w.acceptResult',array(2,6));

            $sql = new Sql($this->_dbAdapter);
            $select = $sql->select();
            $select->from(array('w'=>'thewagers'))
                    ->where($where)
                    ->order('created DESC');

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            //$t = $results->count();
            return $results->count();
        }

     public function getMyWagersCount()
    {
        $userId =$this->getUserSession()->user->id;

        $where = new Where();
        $where->equalTo('w.userAcceptId', $userId);
        $where->AND->equalTo('w.acceptResult',0);
        $where->AND->equalTo('w.status', 1);
        $where->AND->equalTo('w.typeId',0);

        
        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('w'=>'thewagers'))
                ->where($where);


        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
       // $t = $results->count();
        return $results->count();
    }
    
     public function getMyActiveWagers($userId)
    {
        //$userId =$this->getUserSession()->user->id;

        $where = new Where();
        $where->equalTo('w.userAskId', $userId);
        $where->AND->equalTo('w.askResult',0);
        $where->AND->equalTo('w.status', 2);

        $where->OR->equalTo('w.userAcceptId', $userId);
        $where->AND->equalTo('w.acceptResult',0);
        $where->AND->equalTo('w.status', 2);
        
        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('w'=>'thewagers'))
                ->where($where);


        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $c = $results->count();
        return $results->count();
    }    
   

    public function getOpenWagersWithConsoleGames()
    {
        $userId =$this->getUserSession()->user->id;
        
        $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));
        $dateTime->modify('-2 hour');
        $created = $dateTime->format('Y-m-d H:i:s');         

        $where = new Where();
        $where->equalTo('w.userAskId', $userId);
        $where->AND->equalTo('w.status',1);
        $where->AND->equalTo('w.askResult',0);
        $where->AND->equalTo('w.typeId',1);

        $where->OR->equalTo('w.status',1);
        $where->AND->equalTo('w.askResult',0);
        $where->AND->equalTo('w.typeId',1);
        $where->AND->greaterThan('w.created',$created);        

        
        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('w'=>'thewagers'))
                ->join(array('c'=>'theconsoles'),'c.id = w.consoleId',array('consoleName','whichConsole'))
                ->join(array('g'=>'thegames'),'g.id = w.gameId',array('gameName'))
                //->join(array('u'=>'users'),'u.id = w.userAcceptId',array('acceptusername'=>'username'))
                ->join(array('ur'=>'users'),'ur.id = w.userAskId',array('askusername'=>'username'))
                ->where($where)
                ->order('created DESC');

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $t = $results->count();
        return $results;
    }
    
    public function getMyWagersHistory()
    {
        $userId =$this->getUserSession()->user->id;

        $where = new Where();
        $where->equalTo('w.userAskId', $userId);
        $where->AND->in('w.status',array(3,4,5,6,9));
        //$where->AND->isNotNull('w.askResult');
        $where->OR->equalTo('w.userAcceptId', $userId);
        //$where->AND->isNotNull('w.acceptResult');
        $where->AND->in('w.status',array(3,4,5,6,9));

        
        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('w'=>'thewagers'))
                ->join(array('c'=>'theconsoles'),'c.id = w.consoleId',array('consoleName','whichConsole'))
                ->join(array('g'=>'thegames'),'g.id = w.gameId',array('gameName'))
                ->join(array('u'=>'users'),'u.id = w.userAcceptId',array('acceptusername'=>'username'))
                ->join(array('ur'=>'users'),'ur.id = w.userAskId',array('askusername'=>'username'))
                ->where($where)
                ->order('created DESC');

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $t = $results->count();
        return $results;
    }
    
    public function getOpenWagersFilterConsoleGames($consoleId, $gameId)
    {
//        $userId =$this->getUserSession()->user->id;

        $where = new Where();
        //$where->equalTo('w.userAskId', $userId);
        $where->AND->equalTo('w.status',1);
        $where->AND->equalTo('w.askResult',0);
        $where->AND->equalTo('w.typeId',1);
        
        //Get all open wager regardless of console and games//
        if ($consoleId != 1) {
            $where->AND->EqualTo('w.consoleId',$consoleId);
            $where->AND->EqualTo('w.gameId',$gameId);
        }

        
        $sql = new Sql($this->_dbAdapter);
        $select = $sql->select();
        $select->from(array('w'=>'thewagers'))
                ->join(array('c'=>'theconsoles'),'c.id = w.consoleId',array('consoleName','whichConsole'))
                ->join(array('g'=>'thegames'),'g.id = w.gameId',array('gameName'))
                //->join(array('u'=>'users'),'u.id = w.userAcceptId',array('acceptusername'=>'username'))
                ->join(array('ur'=>'users'),'ur.id = w.userAskId',array('askusername'=>'username'))
                ->where($where)
                ->order('created DESC');

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        
        if ($results instanceof ResultInterface && $results->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            $t = $resultSet->count();
            return $resultSet->toArray();
        } 
        return $results;
    }   
    
//    public function getOpenWagers()
//    {
//        return $this->getDbAdapter()->getOpenWagers($this->getUserSession()->user->id);
//    }
    
    public function setDbAdapter($dbAdapter)
    {
        $this->_tableAdapter = $dbAdapter;
    }
    
    public function getDbAdapter()
    {
        if(!$this->_tableAdapter) {
            $this->setDbAdapter($this->setTableGateway($this, 'thewagers'));
        }
        return $this->_tableAdapter;
    }
    
    protected function getUserSession()
    {
        if(!$this->_userSession) {
          $this->_userSession = new Container('user');
        }
        return $this->_userSession;
    }
}
