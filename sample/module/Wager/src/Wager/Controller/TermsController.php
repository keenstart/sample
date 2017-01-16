<?php 

namespace Wager\Controller;

use Wager\Controller\AbstractWagerController;

use Zend\Db\Adapter\AdapterInterface;

//use Application\Models\The\TheMessages;
//use Application\Models\User;
//use Zend\View\Model\JsonModel;
//use Application\Models\Pusher\Pusher;

class TermsController extends AbstractWagerController
{
    public function __construct(AdapterInterface $dbAdapter) 
    {
        parent::__construct($dbAdapter);
    }

    public function indexAction()
    { 
//      $theMessages = new TheMessages($this->_dbAdapter);
//      $messages = $theMessages->getTheMessageToUserId($this->getUserSession()->user->id, 0, 100);
//      $this->_view->setVariable('directmsgs', $messages);
        return $this->_view;
    }
    public function privacyAction()
    { 
//      $theMessages = new TheMessages($this->_dbAdapter);
//      $messages = $theMessages->getTheMessageToUserId($this->getUserSession()->user->id, 0, 100);
//      $this->_view->setVariable('directmsgs', $messages);
        return $this->_view;
    }
    
    public function rulesAction()
    { 
//      $theMessages = new TheMessages($this->_dbAdapter);
//      $messages = $theMessages->getTheMessageToUserId($this->getUserSession()->user->id, 0, 100);
//      $this->_view->setVariable('directmsgs', $messages);
        return $this->_view;
    }
 
    

    
}