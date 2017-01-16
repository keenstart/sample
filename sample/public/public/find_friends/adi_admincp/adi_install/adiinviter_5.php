<?php 
/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
| AdiInviter Pro (http://www.adiinviter.com)                                                |
+-------------------------------------------------------------------------------------------+
| @license    For full copyright and license information, please see the LICENSE.txt        |
+ @copyright  Copyright (c) 2015 AdiInviter Inc. All rights reserved.                       +
| @link       http://www.adiinviter.com                                                     |
+ @author     AdiInviter Dev Team                                                           +
| @docs       http://www.adiinviter.com/docs                                                |
+ @support    Email us at support@adiinviter.com                                            +
| @contact    http://www.adiinviter.com/support                                             |
+-------------------------------------------------------------------------------------------+
| Do not edit or add to this file if you wish to upgrade AdiInviter Pro to newer versions.  |
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+*/


$adi_action = AdiInviterPro::POST('adi_action', ADI_STRING_VARS);

$adiinviter->requireSettingsList(array('global','db_info'));

// Modifying permisisons according to User system and Userrgoup system integrations.
$adiinviter->permissions->reset_usergroup_perms();


// Install Languages
$adi_installer = adi_allocate_pack('Adi_Installer');



// Install Plugins
if($adiinviter->db_allowed === true)
{
	require_once(ADI_LIB_PATH.'adiinviter_plugins.php');
	$adi_plugins = new Adi_Plugin_Handler();
	$adi_plugins->init($adiinviter);
	$adi_plugins->scan_for_plugins();
}
	

// Install All themes
$installed_themes_list = $adiinviter->settings['adiinviter_themes_list'];
$all_themes_list = $adiinviter->get_themes_list();
if(count($installed_themes_list) != count($all_themes_list))
{
	$uninstalled_themes_list = array_diff(array_keys($all_themes_list), array_keys($installed_themes_list));
	if(count($uninstalled_themes_list) > 0)
	{
		$adi_themes = adi_allocate_pack('Adi_Themes');
		foreach($uninstalled_themes_list as $theme_id)
		{
			$adi_themes->install_theme($theme_id);
		}
	}
}


// Set current build id
$current_build_id = 2000;
$adi_updates = adi_allocate_pack('Adi_Updates');
$adi_updates->set_current_build_id($current_build_id);



// Install Updates if exists.
$adiinviter->install_updates();


// Installation finished notifier.
$adi_installer->finish_installation();


?>
<form action="adi_index.php" method="GET" class="installer_form">
<?php
foreach($adiinviter->form_hidden_elements as $name => $value)
{
	echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
?>
	<div class="inst_top_header sect_head">Installation Complete</div>

	<div class="inst_content_out">
		Remove AdiInviter Pro installation directory <font color="red"><b>(<?php echo ADI_DS; ?>adi_install)</b></font> from your FTP :<br>
		<ul class="inst_rem_files_ul" style="margin-top:10px;margin-bottom:25px;">
			<li><b><?php 
			$install_dir_path = ADI_ADMIN_PATH.'adi_install'.ADI_DS;
			if(DIRECTORY_SEPARATOR == '/') {
				$se = '\\'; $rp = '/';
			}
			else {
				$se = '/'; $rp = '\\';
			}
			$install_dir_path = str_replace($se, $rp, $install_dir_path);
			echo $install_dir_path; ?></b></li>
		</ul>

		<?php if(!$adiinviter->user_system) { ?>
		<div class="fin_head">How To Integrate AdiInviter Pro With Your Website Database?</div>
		<div style="margin-bottom:25px;">If you want to integrate AdiInviter Pro with your website database then simply click on "Finish" button and go to "Integration" tab.</div>
		<?php } ?>

		<div class="fin_head">URLS To Access AdiInviter Pro</div>
		<div style="margin-bottom:10px;">You can access AdiInviter Pro interfaces at following urls :</div>
		<div class="fin_desc" style="margin-bottom:25px;">
		<?php
		$admin_url = $adiinviter->adi_root_url
		?>
		Admin Panel : 
		<script type="text/javascript">
		var url = window.location.href;
		url=url.replace('adi_install.php', 'adi_index.php');
		document.write('<a href="'+url+'" class="adi_link" target="_blank">'+url+'</a>');
		</script>
		<br>Popup Interface : <a href="<?php echo $adiinviter->popup_model_url; ?>" class="adi_link" target="_blank"><?php echo trim($adiinviter->popup_model_url, '?&'); ?></a>
		<br>Inpage Interface : <a href="<?php echo $adiinviter->inpage_model_url; ?>" class="adi_link" target="_blank"><?php echo trim($adiinviter->inpage_model_url, '?&'); ?></a>
		<br>Invite History : <a href="<?php echo $adiinviter->invite_history_url; ?>" class="adi_link" target="_blank"><?php echo trim($adiinviter->invite_history_url, '?&'); ?></a>
		</div>
	</div>

	<div class="inst_top_footer">
		<input type="submit" value="Finish" class="btn_grn">
	</div>
</form>
<?php 


$admin_config_file = ADI_ADMIN_PATH.'adi_admin_config.php';
include($admin_config_file);
if(isset($adiinviter_settings) && is_array($adiinviter_settings))
{
	$adiinviter_settings['first_login'] = 0;
	if($adi_action	== 'skip_user_system')
	{
		$adiinviter_settings['first_login'] = 0;
	}

	file_put_contents($admin_config_file, '<?php
$adiinviter_settings = '.var_export($adiinviter_settings, true).';
?>');
}

?>