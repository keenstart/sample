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

?>

<div class="inst_top_header ">
	<span class="opts_head">Database Connection Details</span><br>
</div>

<form action="adi_install.php" method="POST" class="inst_input_form" data="checkConnDetails">
<?php
foreach($adiinviter->form_hidden_elements as $name => $value)
{
	echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
?>
	<div class="inst_content_out">
	<table style="width:100%;" class="opts_table" cellspacing="0" cellpadding="0">
		<tr class="first">
			<td class="label_box">
				<span class="opts_head">Database Connection Type</span><br>
				<label class="opts_note">Choose your database connection type.</label>
			</td>
			<td>
				<select class="opts" name="subsettings[adminconfig][adiinviter_db_type]" id="adiinviter_db_type" style="width: 393px;">
					<?php $db_types = $adiinviter_settings['adiinviter_available_db_types'];
					unset($db_types['none']);
					$db_tp = $adiinviter_settings['adiinviter_db_type'];
					if(!isset($db_types[$db_tp]))
					{
						$db_tp = 'mysqli';
					}
					foreach($db_types as $val => $name)
					{
						if($val == $db_tp)
							echo '<option value="'.$val.'" selected>'.$name.'</option>';
						else
							echo '<option value="'.$val.'">'.$name.'</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<!-- <tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr> -->
		
		<tr>
			<td class="label_box">
				<span class="opts_head">Host Name</span><br>
				<label class="opts_note">Enter your database host.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" autocomplete="off" name="subsettings[adminconfig][adiinviter_hostname]" value="<?php echo $adiinviter_settings['adiinviter_hostname']; ?>" id="adiinviter_hostname">
			</td>
		</tr>

		<!-- <tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr> -->

		<tr>
			<td class="label_box">
				<span class="opts_head">Username</span><br>
				<label class="opts_note">Enter your database username.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" autocomplete="off" name="subsettings[adminconfig][adiinviter_username]" value="<?php echo $adiinviter_settings['adiinviter_username']; ?>" id="adiinviter_username">
			</td>
		</tr>

		<!-- <tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr> -->

		<tr>
			<td class="label_box">
				<span class="opts_head">Password</span><br>
				<label class="opts_note">Enter your database password.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" autocomplete="off" name="subsettings[adminconfig][adiinviter_password]" value="<?php echo $adiinviter_settings['adiinviter_password']; ?>" id="adiinviter_password">
			</td>
		</tr>

		<!-- <tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr> -->

		<tr>
			<td class="label_box">
				<span class="opts_head">Database Name</span><br>
				<label class="opts_note">Enter your database name (with prefix : if any).</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" autocomplete="off" name="subsettings[adminconfig][adiinviter_dbname]" value="<?php echo $adiinviter_settings['adiinviter_dbname']; ?>" id="adiinviter_dbname">
			</td>
		</tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">Table Prefix</span><br>
				<label class="opts_note">Enter your database table prefix.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" autocomplete="off" name="subsettings[adminconfig][adiinviter_table_prefix]" value="<?php echo $adiinviter_settings['adiinviter_table_prefix']; ?>" id="adiinviter_table_prefix">
			</td>
		</tr>
	</table>
</div>

<div class="inst_top_footer">
	<div class="action_err_msg"><?php echo $error_msg; ?></div>
	<input type="hidden" name="adi_step" value="<?php echo $adi_step+1; ?>">
	<!-- <input type="button" name="" value="Skip" class="btn_grn inst_skip_btn"> -->
	<input type="submit" name="" value="Next" class="btn_grn inst_submit_form">
</div>
</form>


<!-- <form action="adi_install.php" method="POST" class="inst_skip_form" style="padding:0px; margin:0px;">
	<input type="hidden" name="adi_step" value="3">
	<input type="hidden" name="adi_action" value="skip_db">
</form> -->


<script type="text/javascript">
adi_inst.set_step_2();
</script>