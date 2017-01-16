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


$adiinviter->requireSettingsList('global');

$install_url = AdiInviterPro::POST('install_url', ADI_STRING_VARS);

$adi_installer = adi_allocate_pack('Adi_Installer');
if(!empty($install_url))
{
	$adi_installer->set_install_url($install_url);
}

$settings =& $adiinviter->settings;

$val = $settings['adiinviter_website_name'];
$website_name       = empty($val) ? $adi_installer->get_website_name() : $val;

$val = $settings['adiinviter_root_url'];
$adi_root_url       = empty($val) ? $adi_installer->get_adi_root_url() : $val;

$val = $settings['adiinviter_website_root_url'];
$website_root_url   = empty($val) ? $adi_installer->get_website_root_url() : $val;

$val = $settings['adiinviter_website_register_url'];
$register_page_url  = empty($val) ? $adi_installer->get_register_page_url() : $val;

$val = $settings['adiinviter_website_login_url'];
$login_page_url     = empty($val) ? $adi_installer->get_login_page_url() : $val;

$val = $settings['adiinviter_sender_name'];
$sender_name        = empty($val) ? $adi_installer->get_sender_name() : $val;

$val = $settings['adiinviter_email_address'];
$sender_email       = empty($val) ? $adi_installer->get_sender_email() : $val;

$val = $settings['adiinviter_website_logo'];
$website_logo       = empty($val) ? $adi_installer->get_website_logo() : $val;


?>
<form action="adi_install.php" method="POST" class="inst_input_form">
<?php
foreach($adiinviter->form_hidden_elements as $name => $value)
{
	echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
?>

<div class="inst_top_header ">
	<span class="opts_head">AdiInviter Pro Settings</span>
</div>


<div class="inst_content_out">
	<table style="width:100%;" class="opts_table" cellspacing="0" cellpadding="0">
		<tr class="first">
			<td class="label_box">
				<span class="opts_head">Website Name</span><br>
				<label class="opts_note">Enter your website name.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_website_name]" value="<?php echo $website_name; ?>">
			</td>
		</tr>

		<tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">Website Root URL</span><br>
				<label class="opts_note">Enter full url to your website root without trailing slash (/).<br>
				e.g. http://www.yourdomain.com</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_website_root_url]" value="<?php echo $website_root_url; ?>">
			</td>
		</tr>

		<tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">AdiInviter Pro Root URL</span><br>
				<label class="opts_note">Enter full url to AdiInviter Pro root without trailing slash (/).<br>
				e.g. http://www.yourdomain.com/find_friends</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_root_url]" value="<?php echo $adi_root_url; ?>">
			</td>
		</tr>

		<tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">Website Register/Sign Up Page URL</span><br>
				<label class="opts_note">Enter full url to your website's register or sign up page.<br>
				For e.g. http://www.yourdomain.com/register.php?invitation_id=[invitation_id]<br>
				<span class="label_red_note">Note:</span> The parameter [invitation_id] is required to uniquely identify invitations.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_website_register_url]" value="<?php echo $register_page_url; ?>">
			</td>
		</tr>

		<tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">Website Login Page URL</span><br>
				<label class="opts_note">Enter full url to your website's Login page.<br>
				e.g. http://www.yourdomain.com/login</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_website_login_url]" value="<?php echo $login_page_url; ?>">
			</td>
		</tr>

		<tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">Sender Name</span><br>
				<label class="opts_note">Enter default "from name" for all outgoing emails.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_sender_name]" value="<?php echo $sender_name; ?>">
			</td>
		</tr>

		<!-- <tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr> -->

		<tr>
			<td class="label_box">
				<span class="opts_head">Sender Email</span><br>
				<label class="opts_note">Enter default "from email address" for all outgoing emails.</label>
			</td>
			<td>
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_email_address]" value="<?php echo $sender_email; ?>">
			</td>
		</tr>

		<tr><td colspan="2" class="hr_sep_td"><hr class="sep"></td></tr>

		<tr>
			<td class="label_box">
				<span class="opts_head">Website Logo (100x100 Max Size)</span><br>
				<label class="opts_note">For e.g. http://www.yourdomain.com/logo.png</label>
			</td>
			<td valign="top">
				<input type="textbox" class="txinput reg" name="subsettings[global][adiinviter_website_logo]" value="<?php echo $website_logo; ?>" onkeyup="adi_inst.show_logo_preview(this, 'adi_logo_prev_img');">
				<?php 
				if($website_logo != '')
				{
					echo '
					<div class="adi_logo_preview">
					<a href="'.$website_logo.'" target="_blank" class="adi_logo_prev" style="margin: 3px 0px; display: block;">
					<img src="' . $website_logo . 
					'" class="adi_logo_img adi_logo_prev_img">
					</a>
					</div>
					';
				}
				else {
					echo '<div class="adi_no_logo"><span class="adi_no_logo_txt">No Logo</span><div class="clr"></div></div>';
				}
				?>
			</td>
		</tr>

	</table>

</div>

<div class="inst_top_footer">
	<input type="hidden" name="adi_step" value="<?php echo $adi_step+1; ?>">
	<input type="submit" name="" value="Next" class="btn_grn btn_left_space step_3_submit_btn">
</div>

</form>



<script type="text/javascript">
// adi_inst.set_step_1();
</script>