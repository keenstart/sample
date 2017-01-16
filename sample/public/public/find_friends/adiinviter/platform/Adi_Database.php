<?php

class Adi_Database_Platform extends Adi_Database_Base
{
	function reconnect()
	{
		// return $this->adi_connect_to_db($this->db_hostname, $this->db_username, $this->db_password, $this->db_name);
		return true;
	}
	function adi_connect_to_db($hostname, $username, $password, $dbname_or_error_report = null, $error_report = false)
	{
		global $adi_zend_adapter;

		$zd_global_config = require 'config/autoload/global.php';
		$zd_local_config  = require 'config/autoload/local.php';

		$db_config = array_merge($zd_global_config['db'], $zd_local_config['db']);

		$this->zd_adapter = new Zend\Db\Adapter\Adapter($db_config);
		$this->zd_platform = $this->zd_adapter->getPlatform();
		if(isset($this->zd_adapter))
		{
			$this->db_allowed = true;
			return true;
		}
		else {
			return false;
		}
	}
	function adi_escape_string($value)
	{
		$val = $this->zd_platform->quoteValue($value);
		if(!is_numeric($val)) {
			$val = substr($val, 1, -1);
		}
		return $val;
	}
	function adi_ping_db()
	{
		return true;
	}
	function adi_get_error()
	{
		return '';
	}
	function adi_query_read($query = '', $error_report = true)
	{
		$statement = $this->zd_adapter->query($query);
		return $statement->execute();
	}
	function adi_fetch_array($pointer, $error_report = true)
	{
		return $pointer->next();
	}
	function adi_fetch_assoc($pointer, $error_report = true)
	{
		return $pointer->next();
	}
	function adi_query_write($query = '', $error_report = true)
	{
		$statement = $this->zd_adapter->query($query);
		return $statement->execute();
	}
	function adi_free_result($pointer = '', $error_report = true)
	{
		return true;
	}
}

?>