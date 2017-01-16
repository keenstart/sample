<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
define('ROOT_PATH', dirname(__DIR__));
//var_dump(curl_version());
//echo phpinfo();
// Setup autoloading
require 'init_autoloader.php';

// Run the application!
try{
  Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch(Exception $e){
  var_dump($e->getMessage());
}
