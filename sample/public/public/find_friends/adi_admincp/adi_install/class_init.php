<?php

$pre_settings = array(
	// Global Settings
	'global' => array(
		'adiinviter_store_guest_user_info'         => '1',
		'adiinviter_sender_name'                   => 'Default Sender Name',
		'adiinviter_onoff'                         => '1',
		'adiinviter_theme'                         => 'default',
		'adiinviter_themes_list'                   => '&adi_cBraceOpen;&adi_cBraceClose;',
		'adiinviter_root_url'                      => '',
		'adiinviter_website_root_url'              => '',
		'adiinviter_website_name'                  => 'Your Website Name',
		'check_for_updates_last_time'              => 0,
		'adiinviter_invite_already_invited'        => 1,
		'adiinviter_email_notification'            => '',
		'adiinviter_website_register_url'          => '',
		'adiinviter_website_logo'                  => '',
		'adiinviter_cookie_path'                   => '/tmp',
		'adiinviter_show_already_registered'       => '1',
		'adiinviter_show_already_invited_contacts' => '1',
		'adiinviter_website_login_url'             => '',
		'adiinviter_email_address'                 => '',
		'adiinviter_store_imported_contacts'       => '1',
		'language'                                 => 'en',
		'text_direction'                           => 'ltr',
		'captcha_public_key'                       => '',
		'captcha_private_key'                      => '',
		'max_contacts_count'                       => '2000',
		'contact_file_size_limit'                  => '1024',
		'contacts_list_length_limit'               => '50000',
		
		'services_onoff' => array(
			'on' => array("gmail", "yahoo", "hotmail", "aol", /*"linkedin",*/ "icloud", "twitter", "mailchimp", "mail_com", "eventbrite", "plaxo", "lycos", "viadeo", "laposte", "terra", "bol_com_br", "sapo", "iol_pt", "atlas", "gmx_net", "freenet_de", "web_de", "tonline", "xing", "wpl", "onet_pl", "interia", "o2", "virgilio", "libero", "email_it", "mynet", "citromail_hu", "india", "rediff", "qip", "mail_ru", "rambler", "yandex", "meta", "abv", "qq_com", "naver_com", "yeah", "ost_com", "ots_com", "daum_net", "sohu", "evite", "fastmail",),
			'off' => array(),
		),
	),

	// Database Information settings
	'db_info' => array(
		'avatar_table' => array(
			'table_name' => '',
			'userid'     => '',
			'avatar'     => '',
		),
		'usergroup_mapping' => array(
			'table_name'  => '',
			'userid'      => '',
			'usergroupid' => '',
		),
		'user_table' => array(
			'table_name'   => '',
			'userfullname' => '',
			'userid'       => '',
			'username'     => '',
			'email'        => '',
			'usergroupid'  => '',
			'avatar'       => '',
		),
		'adiinviter_avatar_url' => '',
		'adiinviter_profile_page_url' => '',
		'usergroup_table' => array(
			'table_name'  => '',
			'usergroupid' => '',
			'name'        => '',
		),
		'friends_table' => array(
			'table_name'    => '',
			'userid'        => '',
			'friend_id'     => '',
			'status'        => '',
			'yes_value'     => '',
			'pending_value' => '',
		),
		'usergroup_permisssions' => array(
			0 => array(1,1,1, 0,0,0, 'Unlimited'),
		),
	),

	// Invitation Settings
	'invitation' => array(
		'invitation_subject_en' => 'Invitation to Join [website_name]',
		'invitation_body_en' => '<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="format-detection" content="telephone=no"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
<title>Invitation to join [website_name]</title>

<style type="text/css">
@media all and (max-width: 400px) {
	.feature-column { width:100% !important; display: block; }
	.feature-text { margin-bottom: 50px; }
}
@media all and (max-width: 500px) {
	.feature-column { padding: 0px 10px !important; }
	.width-500-100 { width: 100% !important; }
	.mbot-30 { margin-bottom: 30px !important; }
	.leftright-text { padding-right: 0px !important; padding-left: 0px !important; }
}
</style>

</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" yahoo="fix" style="font-family: Verdana, Georgia, Times, serif; background-color:#FFFFFF; " bgcolor="FFFFFF">

<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
<tr><td align="center">

<div style="width:100%;max-width:600px;min-width:320px;">

	<table width="100%" border="0" cellpadding="0" cellspacing="0"  align="center" style="background-color: #FFFFFF;">

	<tr>
		<td style="background-color: #00BBF5;">

			<table border="0" cellpadding="0" cellspacing="0"  align="center" style="width:100%;">
			<tr>
				<td style="padding: 15px;"><img src="[invitation_assets_url]/default/logo.png"></td>
			</tr>
			<tr>
				<td align="center" style="padding: 15px;">
					<div style="font-size:14px; font-weight:bold;color: #ffffff;line-height: 21px;margin-bottom:25px;">INTRODUCING</div>
					<div style="font-size:38px;color: #ffffff;line-height: 25px;">Your Website</div>
				</td>
			</tr>
			<tr>
				<td align="center" style="padding: 15px;">
					<div style="width:100%;height:100%;max-width:190px;max-height:190px;background: url([invitation_assets_url]/default/avatar_bg.png) 0 0 no-repeat;margin: 0px auto;">
						<div style=""><img src="[sender_avatar_url]" style="margin: 20px;max-height:150px;max-heigh:150px;border-radius:50%;"></div>
					</div>
				</td>
			</tr>
			<tr>
				<td align="center" style="padding: 15px;">
					<div style="font-size:18px;color: #ffffff;line-height: 25px;">Your friend [sender_name] invited you to Your Website.</div>
				</td>
			</tr>
			<tr>
				<td align="center" style="padding: 15px;">
					<a href="[verify_invitation_url]invitation_id=[invitation_id]&adi_do=accept" style="background-color:#007396;display:block;padding: 15px 15px;margin:0px 15px 20px 15px;width:100%;max-width:175px;font-size:15px;font-weight:bold;color: #ffffff;text-decoration: none;border-radius:4px;" class="">Get Started</a>
				</td>
			</tr>
			</table>

		</td>
	</tr>


	<tr>
		<td style="padding: 40px 20px;border: 1px solid #DDD;border-top: none;" align="center">
			<div style="display:inline;margin: 0 auto;">
			<table border="0" cellpadding="0" cellspacing="0"  align="center" style="width:100%;table-layout: fixed;">
			<tr>
				<td style="padding: 0px 20px;" align="center" class="feature-column">
					<table border="0" cellpadding="0" cellspacing="0"  align="center">
					<tr><td align="center">
						<img src="[invitation_assets_url]/default/feature1.png" style="margin-bottom: 10px;">
						<div style="font-size:16px;line-height: 22px;color: #68B975;" class="feature-text">Your Website<br>Feature #1</div>
					</td></tr>
					</table>
				</td>
				<td style="padding: 0px 20px;" align="center" class="feature-column">
					<table border="0" cellpadding="0" cellspacing="0"  align="center">
					<tr><td align="center">
					<img src="[invitation_assets_url]/default/feature2.png" style="margin-bottom: 10px;">
					<div style="font-size:16px;line-height: 22px;color: #ff7373;" class="feature-text">Your Website<br>Feature #2</div>
					</td></tr>
					</table>
				</td>
				<td style="padding: 0px 20px;" align="center" class="feature-column">
					<table border="0" cellpadding="0" cellspacing="0"  align="center">
					<tr><td align="center">
					<img src="[invitation_assets_url]/default/feature3.png" style="margin-bottom: 10px;">
					<div style="font-size:16px;line-height: 22px;color: #957bb7;" class="feature-text">Your Website<br>Feature #3</div>
					</td></tr>
					</table>
				</td>
			</tr>
			</table>
			</div>
		</td>
	</tr>


	<tr>
		<td style="padding: 50px 10px;border-left: 1px solid #DDD;border-right: 1px solid #DDD;" align="center">
			<table border="0" cellpadding="0" cellspacing="0"  align="center" style="width:100%;">
			<tr>
				<td style="padding: 10px;" align="center"><div style="text-align:center;">

					<table border="0" cellpadding="0" cellspacing="0"  align="right" width="44%" class="width-500-100 mbot-30">
					<tr><td align="center">
						<img src="[invitation_assets_url]/default/browser.png" style="width:100%;max-width:243px;">
					</td></tr>
					</table>

					<table border="0" cellpadding="0" cellspacing="0"  align="left" width="56%" class="width-500-100">
					<tr><td align="left" style="padding-right: 20px;" class="leftright-text">
						<div style="font-size:14px; font-weight: bold;color: #6a6a6a;margin-bottom: 10px;">Great Feature About Your Website</div>
						<div style="font-size:13px; line-height: 18px; color: #9d9d9d;margin-bottom: 30px;">Write a short description about some great features in your website. This is just a simple test.</div>
						<div style="font-size:14px; font-weight: bold;color: #6a6a6a;margin-bottom: 10px;">Great Feature About Your Website</div>
						<div style="font-size:13px; line-height: 18px; color: #9d9d9d;">Write a short description about some great features in your website. This is just a simple test.</div>
					</td></tr>
					</table>

				</div></td>
			</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td style="padding: 30px 10px;border: 1px solid #DDD;border-top:none;" align="center">
			<table border="0" cellpadding="0" cellspacing="0"  align="center" style="width:100%;">
			<tr>
				<td style="padding: 10px;" align="center"><div style="text-align:center;">

					<table border="0" cellpadding="0" cellspacing="0"  align="left" width="44%" class="width-500-100 mbot-30">
					<tr><td align="center">
						<img src="[invitation_assets_url]/default/browser.png" style="width:100%;max-width:243px;">
					</td></tr>
					</table>

					<table border="0" cellpadding="0" cellspacing="0"  align="right" width="56%" class="width-500-100">
					<tr><td align="left" style="padding-left: 20px;" class="leftright-text">
						<div style="font-size:14px; font-weight: bold;color: #6a6a6a;margin-bottom: 10px;">Great Feature About Your Website</div>
						<div style="font-size:13px; line-height: 18px; color: #9d9d9d;margin-bottom: 30px;">Write a short description about some great features in your website. This is just a simple test.</div>
						<div style="font-size:14px; font-weight: bold;color: #6a6a6a;margin-bottom: 10px;">Great Feature About Your Website</div>
						<div style="font-size:13px; line-height: 18px; color: #9d9d9d;">Write a short description about some great features in your website. This is just a simple test.</div>
					</td></tr>
					</table>

				</div></td>
			</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td style="padding: 45px 10px 20px 10px;border: 1px solid #DDD;border-top:none;" align="center">
			<div style="max-width:400px;">
				<a href=""><img src="[invitation_assets_url]/default/fb.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
				<a href=""><img src="[invitation_assets_url]/default/g.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
				<a href=""><img src="[invitation_assets_url]/default/tw.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
				<a href=""><img src="[invitation_assets_url]/default/in.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
				<a href=""><img src="[invitation_assets_url]/default/vm.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
				<a href=""><img src="[invitation_assets_url]/default/be.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
				<a href=""><img src="[invitation_assets_url]/default/db.png" style="margin:0 auto;margin-bottom: 15px; margin-right: 20px;"></a>
			</div>
		</td>
	</tr>
	</table>

	<table border="0" cellpadding="0" cellspacing="0"  align="center" style="width:100%;max-width:600px;">
	<tr>
		<td style="padding: 20px 10px 0px 10px;">
			<div style="color: #ababab;font-family:Verdana,Arial;font-size: 12px;text-align:left;line-height:17px;">
				This email was sent to you on behalf of [sender_email]&#39;s request. You can safely <a href="[verify_invitation_url]invitation_id=[invitation_id]&adi_do=unsubscribe" style="text-decoration:none;color:#999999;color:#0084b4;text-decoration:underline;">unsubscribe</a> from these emails.<br><br>
				Your Website, Inc. 1003 Market Street, Palo Alto, CA 94001.
			</div>
		</td>
	</tr>
	</table>
</div>
</td></tr>
</table>

</body>
</html>',
		'invitation_attachment' => '1',
		'invitation_social_body_en' => 'AdiInviter Contacts Importer / Inviter is an addressbook importer script. It allows your users to invite their contacts from various webmail and social networks such as Gmail, Yahoo, Hotmail, AOL and many more.',
		'attach_note_length_limit' => '150',
		'twitter_invitation_body_en' => 'This is a sample invitation message sent using AdiInviter Pro Live Demo. Check out AdiInviter Pro 2.0 here : http://goo.gl/8PVxQ7',

		'A7C987C926E5B640308C6B930EA243C8C7C8B6E02A' => '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' => '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',


	),

	// OAuth Settings
	'oauth' => array(
		'google_consumer_key'        => '',
		'google_consumer_secret'     => '',

		'hotmail_consumer_key'       => '',
		'hotmail_consumer_secret'    => '',

		'yahoo_consumer_key'         => '',
		'yahoo_consumer_secret'      => '',
		
		'xing_consumer_key'          => '',
		'xing_consumer_secret'       => '',
		
		'aol_consumer_key'           => '',
		'aol_consumer_secret'        => '',
		
		'mailchimp_consumer_key'     => '',
		'mailchimp_consumer_secret'  => '',
		'mailchimp_consumer_api_key' => '',

		'eventbrite_consumer_key'    => '',
		'eventbrite_consumer_secret' => '',

		'twitter_consumer_key'       => '',
		'twitter_consumer_secret'    => '',

		'viadeo_consumer_key'        => '',
		'viadeo_consumer_secret'     => '',
	),

	// Campaign list
	'campaigns' => array(
		'campaigns_list' => array(),
	),

	// Updates
	'updates' => array(
		'adi_package_build_id' => '2000',
		'adi_updates_list' => '',
		'check_for_updates_link' => 'http://www.adiinviter.com/updates.php',
		'download_updates_link' => 'https://www.adiinviter.com/download',
		'adi_email_notification_subject' => 'AdiInviter Pro Update Notification : [updates_count] New Updates',
		'adi_email_notification_body' => '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="format-detection" content="telephone=no"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
<title>AdiInviter Pro Update Notification : [updates_count] New Updates</title>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" yahoo="fix" style="font-family: Verdana, Georgia, Times, serif; background-color:#FFF; " bgcolor="FFF">

	<p style="margin: 0px 0px 15px 0px;font-family: Verdana,Arial;font-size:13px;color: #181818;line-height:18px;">Hello,</p>
	<p style="margin: 0px 0px 15px 0px;font-family: Verdana,Arial;font-size:13px;color: #181818;line-height:18px;">There are [updates_count] new updates available for AdiInviter Pro. Please login to our <a href="http://www.adiinviter.com/download" style="text-decoration:none;color:#00B4FF;"><b>Members Area</b></a> to download the updates.</p>

	<div style="margin: 0px 0px 15px 0px;font-family: Verdana,Arial;font-size:13px;color: #181818;line-height:18px;"><span style="font-weight:bold; color:#FF0000;">Note :</span> Please do not reply to this email. Unfortunately, we are unable to respond to inquiries sent to this email address. If you have any questions then please send us an email at <a href="mailto:support@adiinviter.com" style="color:#00B4FF;text-decoration:none;">support@adiinviter.com</a>. You can also contact us by visiting our official <a href="http://www.adiinviter.com/support" target="_blank" style="color:#00B4FF;text-decoration:none;">Support Page</a>.</div>

	<p style="margin: 0px;font-family: Verdana,Arial;font-size:13px;color: #181818;line-height:18px;">Thank you,<br>AdiInviter Pro</p>

</body>
</html>',
	),

);

?>