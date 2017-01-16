<?php 

namespace Wager\Controller;

use Wager\Controller\AbstractWagerController;

use Zend\Db\Adapter\AdapterInterface;

use Application\Models\The\TheMessages;
use Application\Models\The\TheTalkWall;
use Application\Models\User;
use Zend\View\Model\JsonModel;
use Application\Models\Pusher\Pusher;
use Wager\Models\Email\NewMessageReceived;

class MessageController extends AbstractWagerController
{
    public function __construct(AdapterInterface $dbAdapter) 
    {
        parent::__construct($dbAdapter);
    }

    public function indexAction()
    { 
      $theMessages = new TheMessages($this->_dbAdapter);
      $messages = $theMessages->getTheMessageToUserId($this->getUserSession()->user->id, 0, 100);
      $this->_view->setVariable('directmsgs', $messages);
      return $this->_view;
    }
    
    public function getmsgAction()
    { 
        $type = $this->params()->fromPost('type');
        $offset = (int)$this->params()->fromPost('offset');
        
        $append = false;
        if(!$offset) {
            $append = true;
        }
        $theMessages = new TheMessages($this->_dbAdapter);

        if ($type == 'inbox') {
            $messages = $theMessages->getTheMessageToUserId($this->getUserSession()->user->id, $offset,25);
            $direction = "From:"; //To me but show from user
        } else {
            $messages = $theMessages->getTheMessageFromUserId($this->getUserSession()->user->id, $offset,25);
            $direction = "To:"; //From me but show to user
        }
        
        $return = array(
           'success' => true,
           'count' => count($messages),
           'pageon' => $type,
           'direction' => $direction,
           'append' => $append,
           'msg' => $messages,
        );  

        return new JsonModel($return);
    }
    
    public function deleteAction()
    { 
        $id = $this->params()->fromPost('id');

        $theMessages = new TheMessages($this->_dbAdapter);

        $messages = $theMessages->getTheMessageById($id);
        
        $return = array(
           'success' => true,
            'id' => $id,
           'messages'   => 'Mail Deleted.',
        );  

        return new JsonModel($return);
    }

    
    
    public function sendAction()
    { 
        $this->validateUser(); 
        $request = $this->getRequest();
        if ($request->isPost()) {

            $data = $request->getPost();
            
            //Get opponent Id from username 
            $user = new User($this->_dbAdapter);
            $touserId = $user->getUserByUsername($data['touserId']);
            if(!$touserId) {
                $return = Array(
                    'success' => false,
                    'messages'   => 'This User not Found. Try again.',
                );
                return new JsonModel($return);                    
            }
            $data['touserId'] = $touserId->id;
            

                    
            $theMessages = new TheMessages($this->_dbAdapter);
            $message = $theMessages->sendTheMessage($data);
  
            // Current Details 
            $currenyUser = $user->getUser($message->fromuserId);
            
            //---Pusher ---//
            $pusher = new Pusher();
            $socket = $this->params()->fromPost('socket_id');
            $return = array(
                'touserId' => $message->touserId,
                'username' => $currenyUser->username,
                'subject' => $message->subject,
                'messages' => $message->messages,
                'created' => $message->created,
                'direction' => 'From: ',
                'read' => false,
            );
            
            $pusher->getPusherService()->trigger('messageevents', 'msgsent', $return,$socket);
            
            
            // Recieve party 
            $toUser = $user->getUser($message->touserId);
            
            // Send reminder Email to player who as not confirmed match as yet.
            //Uri //
            $uri = $this->getRequest()->getUri();
            $scheme = $uri->getScheme();
            $myhost = $uri->getHost();
            $url = $scheme.'://'.$myhost .'/wager/message/';   
            
            $newMessageReceived = new NewMessageReceived($this->_dbAdapter);
            $messageReceived = $newMessageReceived->sendEmail($message, $url);            
            
            $return['username'] = $toUser->username;
            $return['direction'] = 'To: ';
            $return['success'] = true;
            $return['messages'] = 'Message was sent successful.';

            return new JsonModel($return); 
        }
    }
    
    public function talkAction()
    { 
        $this->validateUser(); 
        $talkPost = $this->params()->fromPost();
        
        $theTalk = new TheTalkWall($this->_dbAdapter);
        $talk = $theTalk->sendTalk($talkPost);

        //Get My username 
        $user = new User($this->_dbAdapter);
        $currenyUser = $user->getUser($this->getUserSession()->user->id);
            
        //---Pusher ---//
        $pusher = new Pusher();
        $return = array(
            'id' => $talk->id,
            'userId' => $currenyUser->id,
            'username' => $currenyUser->username,
            'talk' => $talk->talk,
            'channelId' => $talk->channelId,
            'created' => $talk->created,
        );
            
        $pusher->getPusherService()->trigger('talkevents','talkchannel', $return);
        
        $return = array(
           'success' => true,
           'messages' => count($messages),
        );  

        return new JsonModel($return);
    }   

    public function gettalkAction()
    { 
        $this->validateUser(); 
        $channelId = $this->params()->fromPost('channelId');
        $offset = (int)$this->params()->fromPost('offset');
        
        $limit = 25;
        $append = false;
        if($offset) {
            $append = true;
            $limit = 10;
        }
        
        if(!$channelId) $channelId = 1;
        //if($offset) $offset = $offset;

        $theTalk = new TheTalkWall($this->_dbAdapter);
        $talkpage = $theTalk->getTalkPage($channelId, $limit, $offset);
        
        $data = array();
        $data = array(
           'success' => true,
           'count' => count($talkpage),
           'append' => $append,
           'talk' => array_reverse($talkpage),
        );  
        return new JsonModel($data);
    }

    public function isreadAction()
    { 
        $this->validateUser(); 
        $id = $this->params()->fromPost('id');
  
        $data = array();
        $theMessage = new TheMessages($this->_dbAdapter);
        $message = $theMessage->getTheMessageById($id);
        $message->isRead = 1;
        $message->isDeleted = 0;
        
        $data = get_object_vars($message);
        $message = $theMessage->editTheMessage($data);
        
        $data = array(
           'success' => true,
           'message' => $data,
        );  

        
        return new JsonModel($data);
    
    }
    public function deletemessageAction()
    { 
        $this->validateUser(); 
        $id = $this->params()->fromPost('id');
  
        $data = array();
        $theMessage = new TheMessages($this->_dbAdapter);
        $message = $theMessage->getTheMessageById($id);
        // $message->isRead = 1;
        $message->isDeleted = true;
        
        $data = get_object_vars($message);
        $message = $theMessage->editTheMessage($data);
        
        $data = array(
           'success' => true,
           'message' => $data,
        );  

        
        return new JsonModel($data);
    
    }
    
}
