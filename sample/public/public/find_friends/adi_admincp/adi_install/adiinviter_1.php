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



$admin_config_file = ADI_ADMIN_PATH.DIRECTORY_SEPARATOR.'adi_admin_config.php';
include($admin_config_file);

$adi_installer = adi_allocate_pack('Adi_Installer');
$adi_installer->before_installation();

?>

<div class="inst_top_header ">
	<span class="opts_head">License Key</span><br>
</div>

<form action="adi_install.php" method="POST" class="inst_input_form">
<?php
foreach($adiinviter->form_hidden_elements as $name => $value)
{
	echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
?>
<div class="inst_content_out">
	<table style="width:100%;" class="opts_table" cellspacing="0" cellpadding="0">
		<tr class="">
			<td class="label_box">
				<span class="opts_head">Enter Your License Key</span><br>
				<label class="opts_note">AdiInviter Pro license key.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" autocomplete="off" name="adi_license_id" value="" id="adi_license_val">
			</td>
		</tr>
	</table>
</div>

<div class="inst_top_footer">
<?php
$error_msg = '';
if(AdiInviterPro::isGET('do') && AdiInviterPro::GET('do', ADI_STRING_VARS) == 'install') {
	$error_msg = '<font color="red">Invalid License Key</font>';
}
?>
	<div class="action_err_msg"><?php echo $error_msg; ?></div>
	<input type="hidden" name="adi_step" value="<?php echo $adi_step+1; ?>">
	<input type="submit" name="" value="Next" class="btn_grn inst_submit_form">
</div>
</form>

<script type="text/javascript">
adi_inst.set_step_1();
</script>
