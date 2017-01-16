<?php



class Adi_Installer_Platform extends Adi_Installer_Base
{
	public $default_settings = array();
	public $campaigns_list = array();
	function get_default_settings()
	{
		return $this->default_settings;
	}

	function finish_installation()
	{
	}

	function install_default_campaigns()
	{

	}
	function get_campaigns_list()
	{

	}

	function before_installation()
	{
		$zd_global_config = require 'config/autoload/global.php';
		$zd_local_config  = require 'config/autoload/local.php';

		$db_config = array_merge($zd_global_config['db'], $zd_local_config['db']);

		$parts = explode(':', $db_config['dsn'], 2);
		$db_type = $parts[0];

		$parts = explode(';', $parts[1], 3);
		$sett1 = explode('=', $parts[0]);
		$sett2 = explode('=', $parts[1]);

		$hostname = $sett1[0] == 'hostname' ? $sett1[1] : $sett2[1];
		$dbname = $sett2[0] == 'dbname' ? $sett2[1] : $sett1[1];
		$settings = array(
			'adiinviter_db_type'      => isset($this->adi->admin_settings['adiinviter_available_db_types'][$db_type]) ? $db_type : 'mysqli', 
			'adiinviter_hostname'     => $hostname, 
			'adiinviter_username'     => $db_config['username'], 
			'adiinviter_password'     => $db_config['password'],
			'adiinviter_dbname'       => $dbname,
			'adiinviter_table_prefix' => '',
		);
		$this->update_admin_settings($settings);
	}
}


?>