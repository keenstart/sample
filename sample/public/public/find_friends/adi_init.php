<?php

/*
* You can write PHP code here to load base platform libraries.
*/

if(!class_exists('Zend\Mvc\Application'))
{
	chdir(dirname(dirname(__DIR__)));

	// Decline static file requests back to the PHP built-in webserver
	if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
	    return false;
	}

	// Setup autoloading
	require 'init_autoloader.php';

	// Run the application!
	$adi_zend_application = Zend\Mvc\Application::init(require 'config/application.config.php');


}
	
?>