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


$init_file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'adi_init.php';
include_once($init_file_path);
ob_start();

define('ADI_INSTALLER_CHECK', 1);
$adi_install = '7or36s033pnq2s091r571';

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta charset="UTF-8">
	<link href='adi_css/adiinviter.png' rel='shortcut icon'/>
	<title>AdiInviter Pro Installer</title>
	
	<link type="text/css" href="adi_css/index.css" rel="stylesheet" />
	<link type="text/css" href="adi_css/installer.css" rel="stylesheet" />
	<script type="text/javascript" src="adi_js/jquery.min.js"></script>
	<script type="text/javascript">

		var adi_scr_mode = 1024;
		var w = $(window).width();
		if(w >= 1263) {
			adi_scr_mode = 1280;
		}
		else if(w >= 1145) {
			adi_scr_mode = 1152;
		}
	</script>
	<script type="text/javascript" src="adi_js/adiinviter.js"></script>
	<script type="text/javascript" src="adi_js/installer.js"></script>
	<script type="text/javascript">
		adi.frm_elements_ob = <?php echo json_encode($adiinviter->form_hidden_elements); ?>;
		adi.frm_elements = '<?php echo http_build_query($adiinviter->form_hidden_elements, '', '&'); ?>';
	</script>
</head>
<body class="admin_body" style="overflow-y: scroll;">


<div class="overlay mask" id="modal_mask"></div>

<div class="overlay" id="modal_message">
	<table style="width:100%;height:100%;">
		<tr>
			<td style="text-align:center;vertical-align:middle;">
				<center>
					<div class="container" style="width:300px;">
						<span id="modal_msg_txt">Processing request..</span><br>
						<img src="adi_css/images/loading.gif" style="margin-top: 10px;">
					</div>
				</center>
			</td>
		</tr>
	</table>
</div>


<div class="adi_top_header_outer">
<center>
	<div class="adi_top_header">
		<table cellpadding="0" cellspacing="0" width="100%" class="adi_top_header_tble">
			<tr>
				<td style="width: 350px;" align="left">
					<div class="logo">
						<img src="adi_css/adiinviter_pro_logo.png" style="margin-top: 5px;">
					</div>
				</td>
				<td valign="middle" align="center">
					<span class="adi_top_bar_updates"><!-- 0 new updates. --></span>
				</td>
				<td align="right" class="admin-opts" style="padding-right: 8px;">
					<ul class="adi_top_bar_ul">
						<?php if(ADI_USE_CUSTOM_LOGIN) { ?>
						<li class="adi_top_bar_li adi_top_bar_first"><a href="adi_login.php?do=logout">Log out</a></li>
						<li class="adi_top_bar_sep">|</li>
						<li class="adi_top_bar_li adi_top_bar_last"><a href="adi_index.php">Go to Admincp</a></li>
						<?php } 
						else { ?>
						<li class="adi_top_bar_li adi_top_bar_first"><a href="adi_index.php">Go to Admincp</a></li>
						<?php } ?>
					</ul>
				</td>
			</tr>
		</table>
	</div>
</center>
</div>



<?php




$adi_step = AdiInviterPro::POST('adi_step', ADI_INT_VARS);
if(AdiInviterPro::GET('do', ADI_STRING_VARS) == 'install')
{
	$adi_step = 1;
}
$adi_settings_acc = 'str';
$installer_file = ADI_ADMIN_PATH.'adi_install'.ADI_DS.'index.php';
if( file_exists($installer_file) )
{
	$adi_install .= 'oo14nrs9noqp8qss377qs';
?>
	<center>
	<div class="body_table_outer">
		
		<center><table cellpadding="0" cellspacing="0" class="cont_outer"><tr><td valign="top">
		<?php 
		if($adi_step == 0)
		{ ?>
			<form action="adi_install.php" method="POST" class="installer_form">
			<?php
			foreach($adiinviter->form_hidden_elements as $name => $value)
			{
				echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
			}
			?>
				<div class="inst_top_header sect_head">AdiInviter Pro Package Installer</div>
			<?php 
			if(AdiInviterPro::GET('do', ADI_STRING_VARS) != 'installer')
			{
			?>
				<div class="inst_content_out">
					<?php
					$err_msgs = $adiinviter->check_compatibility();
					?>
					Welcome to AdiInviter Pro installation wizard. <?php
					if(count($err_msgs) == 0) {
						echo 'Click on continue button to start the installation process.';
					}
					?>
					<br><br><br>
					<?php
					if(count($err_msgs) > 0)
					{
						?>
						<div class="inst_err_out">
						<span class="inst_err_head">Your web server does not meet the basic server requirements for installing AdiInviter Pro. Please take care of following issues :</span>
						<ul class="inst_err_ul" style="margin-top:15px;">
						<?php
						foreach($err_msgs as $message) {
							echo '<li class="inst_err_li">'.$message.'</li>';
						}
						?>
						</ul>
						</div>
						<?php
					}
					?>
				</div>

				<script type="text/javascript">
					document.write('<input type="hidden" name="install_url" value="'+window.location.href+'">');
				</script>

				<?php $next_step = 1; ?>

				<div class="inst_top_footer">
					<input type="hidden" name="adi_step" value="<?php echo $next_step; ?>">
					<?php 
					if(count($err_msgs) == 0)
					{
						echo '<input type="submit" name="" value="Continue" class="btn_grn">';
					}
					?>
				</div>

			<?php }
			else
			{ ?>
				<div class="inst_content_out">
					<div style="padding:15px;">Invalid License Key : Please <a class="adi_link" href="adi_install.php">try again</a>.</div>
				</div>
			<?php 
			} ?>
			</form>
		<?php
		}
		else
		{
			$file_path = ADI_ADMIN_PATH.'adi_install'.ADI_DS.'class_init.php';
			if(file_exists($file_path))
			{
				include($file_path);
			}
			include_once($installer_file); 
		}

		?>
		</td></tr></table></center>
		
	</div>
	</center>
<?php

}
else // Installer does not exists
{
	?>
	<center>
	<div class="body_table_outer">
		<center>
		<table cellpadding="0" cellspacing="0" class="cont_outer"><tr><td valign="middle">
			<div class="outer_error_message">Installation files not found.</div>
		</td></tr></table>
		</center>
	</div>
	</center>
	<?php
}
?>

<?php

$adi_build_id = 2000;
$sett_file = ADI_ADMIN_PATH.'adi_install'.ADI_DS.'class_init.php';
if(is_file($sett_file))
{
	include($sett_file);
	if(isset($pre_settings)) {
		$adi_build_id = $pre_settings['updates']['adi_package_build_id'];
	}
}
else {
	if($adiinviter->adiinviter_installed)
	{
		$adiinviter->requireSettingsList('updates');
		$adi_build_id = isset($adiinviter->settings['adi_package_build_id']) ? $adiinviter->settings['adi_package_build_id'] : 2000;
	}
}

$num = floor($adi_build_id / 100);
$adi_version_num = number_format($num/10, 1);

?>
<center>
	<div style="">Powered By <a href="http://www.adiinviter.com" target="_blank" class="pp1 lnk2">AdiInviter Pro v<?php echo $adi_version_num; ?></a></div>
	<div style="">Build <?php echo $adi_build_id; ?></div>
</center>

</body>
</html>