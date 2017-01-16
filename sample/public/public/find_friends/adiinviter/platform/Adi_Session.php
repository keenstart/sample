<?php

use Zend\Session\SessionManager;
use Zend\Session\Config\StandardConfig;

class Adi_Session_Platform extends Adi_Session_Base
{
	public $sessionManager = NULL;

	function init()
	{
		$this->sessionManager = new SessionManager();
		$this->sessionManager->start();
	}
}


?>