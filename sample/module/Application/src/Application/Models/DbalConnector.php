<?php

namespace Application\Models;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Config;
use Zend\Db\Metadata\Metadata;
use Application\Models\TableEntityMapper;

//use Application\Models\DbalConnectorInterface;
use Zend\Db\Adapter\AdapterInterface;

class DbalConnector //implements DbalConnectorInterface
{
    
    private $_tableAdapters; 
    private $_config;
    private $_envConfig;
    protected $_dbAdapter;
    
    protected $_serviceManager;
    
    public function __construct(AdapterInterface $dbAdapter = null) {
      $this->_dbAdapter = $dbAdapter;
      $this->_serviceManager = $dbAdapter; //Work around for debug purposed will be remove when done.
    }
    /*public function __construct($serviceManager = null){
      if($serviceManager){
        $this->_serviceManager = $serviceManager;
        $this->_dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
      }
    }*/
    public function setAdapterDb(AdapterInterface $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
    }
    
    public function setTableGateway($object, $tableName, $dbAdapter = null){
      if(isset($this->_tableAdapters[$tableName])){
        return $this->_tableAdapters[$tableName];
      } else{
        $env = getenv('APPLICATION_ENV');
        if(!$env){
            $env = 'production';
        }
        $config = $this->getConfig();
        $envConfig = $this->getEnvConfig($env);
        $objectPrototype = get_class($object);
        $dbConfig = (object)array_replace_recursive((array)$config->db, (array)$envConfig->db);
        $db = array();
        foreach($dbConfig as $key => $value){
            $db[$key] = $value;
        }
        //if(!$dbAdapter) $dbAdapter = $this->getDbAdapter(); // ---recursive call the function
        $dbAdapter = $this->_dbAdapter;
        $metadata = new Metadata($dbAdapter);
        $table = $metadata->getTable($tableName);
        $columns = $table->getColumns();
        $columnArray = array();
        foreach($columns as $column){
            $columnArray[$column->getName()] = $column->getName();
        }
        $hydrator = new TableEntityMapper($columnArray);
        $resultSetPrototype = new $objectPrototype;
        $resultSet = new HydratingResultSet($hydrator, $resultSetPrototype);
        $tableGateway = new TableGateway($tableName, $dbAdapter, null, $resultSet);
        $objectTable = $objectPrototype . 'Table';
        $table = new $objectTable($tableGateway);
        $this->_tableAdapters[$tableName] = $table;
      }
      return $this->_tableAdapters[$tableName];
    }
    
    public function getDbAdapter(){
      if(!$this->_dbAdapter){
        $this->_dbAdapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
      }
      return $this->_dbAdapter;
    }
    
    public function getConfig(){
      if(!$this->_config) $this->_config = new Config(include ROOT_PATH . '/config/autoload/global.php');
      return $this->_config;
    }
    
    public function getEnvConfig($env){
      if(!isset($this->_envConfig[$env])) $this->_envConfig[$env] = new Config(include ROOT_PATH . '/config/autoload/' . $env . '.php');;
      return $this->_envConfig[$env];
    }
}