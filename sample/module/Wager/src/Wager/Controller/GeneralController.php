<?php 

namespace Wager\Controller;

use Wager\Controller\AbstractWagerController;

use Zend\Db\Adapter\AdapterInterface;

use Application\Models\The\TheConsoles;
use Application\Models\The\TheGames;
use Application\Models\The\TheWagers;
//use Application\Models\User;
use Zend\View\Model\JsonModel;
//use Application\Models\Pusher\Pusher;
use Wager\Models\Email\HelpEmail;
use Wager\Models\Email\HelpEmailConfirm;

class GeneralController extends AbstractWagerController
{
    public function __construct(AdapterInterface $dbAdapter) 
    {
        parent::__construct($dbAdapter);
    }


    public function getgamesAction()
    { 
        $consoleId = $this->params()->fromPost('consoleId');

        $theGames = new TheGames($this->_dbAdapter);

        $consoleGame = $theGames->getGameConsoleId($consoleId);

        $result = array();
        foreach($consoleGame as $r )
            $result[$r->id] = $r->gameName;
        
        //Console type  //
        $theConsoles = new TheConsoles($this->_dbAdapter);
        $consoleIdtype = $theConsoles->getConsoleId($consoleId);
        
        $return = array(
           'success' => true,
           'count' => count($result),
           'pageon' => 'openwager',
           'consoleGame' => $result,
           'whichConsole' => $consoleIdtype->whichConsole
        );  

        return new JsonModel($return);
    }
    
    public function openwagerfilterAction()
    { 
        $consoleId = $this->params()->fromPost('consoleId');
        $gameId = $this->params()->fromPost('gameId');

        $theWagers = new TheWagers($this->_dbAdapter);

        $openwager = $theWagers->getOpenWagersFilterConsoleGames($consoleId, $gameId);

        $return = array(
           'success' => true,
           'count' => count($openwager),
           'pageon' => 'openwager',
           'openwager' => $openwager,
        );  

        return new JsonModel($return);
    }    
    
    
    public function getwagerAction()
    { 
        $Id = $this->params()->fromPost('Id');

        $theWagers = new TheWagers($this->_dbAdapter);

        $wager = $theWagers->getWagerById($Id);
        
        $return = array(
           'success' => true,
           'count' => count($wager),
           //'pageon' => 'openwager',
           'wager' => $wager,
        );  

        return new JsonModel($return);
    }
    
    
    public function helpemailAction()
    { 
        $question = $this->params()->fromPost('question');

        $helpEmail = new HelpEmail();
        $helpemail = $helpEmail->sendEmail($question, $this->getUserSession()->user->email);
        if($helpemail) {
            $helpEmailConfirm = new HelpEmailConfirm();
            $helpemailconfirm = $helpEmailConfirm->sendEmail($this->getUserSession()->user->email, $question);
        
            $return = array(
               'success' => true,
            );     
            return new JsonModel($return);
        }
        
        $return = array(
           'success' => false,
        );  

        return new JsonModel($return);
    }    
}