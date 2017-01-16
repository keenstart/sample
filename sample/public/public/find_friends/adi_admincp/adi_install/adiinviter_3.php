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


function adi_flush_contents($msg)
{
   echo '<script type="text/javascript"> 
$(".actions_list_ul").append(\''.$msg.'\'); 
</script>';
   ob_flush();
}

@ini_set('max_execution_time', 120);

?>

<form action="adi_install.php" method="POST" style="padding:0px; margin:0px;">
<?php
foreach($adiinviter->form_hidden_elements as $name => $value)
{
   echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
?>

<div class="inst_top_header ">
   <span class="opts_head">Creating Database Tables</span>
</div>

<div class="inst_content_out">
   <table style="width:100%;" class="opts_table" cellspacing="0" cellpadding="0">
      <tr class="first"><td colspan="2">
         <div class="actions_list">
            <ul class="actions_list_ul"></ul>
         </div>
      </td></tr>
   </table>
</div>

<div class="inst_top_footer">
   <input type="hidden" name="adi_step" value="<?php echo $adi_step+1; ?>">
   <input type="submit" name="" value="Next" class="btn_grn btn_left_space step_1_submit_btn" style="display:none;">
</div>

</form>
<?php


ob_flush();

// Load already created database tables
$tables_list = array();

$new_settings = $adiinviter->settings;
$adiinviter->settings = array_merge($adiinviter->settings, $pre_settings['invitation']);
$adiinviter->session_start();
$adiinviter->settings = $new_settings;
$result = adi_build_query_read('get_all_tables');
while($row = adi_fetch_array($result))
{
   $tbname = array_shift($row);
   if(!empty($tbname))
   {
      $tables_list[] = $tbname;
   }
}
$older_version_exists = false;
$temp_prefix = 'adi_tables_backup_'; // Prefix for temporary backup of older version database tables



// Look for older versions of AdiInviter Database tables.
$cur_prefix = '';
$cur_table_exists = false; 
$backup_tables = array();
foreach($tables_list as $table_name)
{
   if(strpos($table_name, 'adiinviter_settings') !== false)
   {
      $cur_prefix = trim(str_replace('adiinviter_settings', '', $table_name).'');
      if($cur_prefix != $temp_prefix)
      {
         $cur_table_exists = true;
      }
   }
}



if($cur_table_exists)
{
   $cur_fields = array();
   if($result = adi_build_query_read('check_table_structure', array(
      'table_name' => $cur_prefix.'adiinviter_settings',
   )))
   {
      while($row = adi_fetch_array($result))
      {
         $cur_fields[] = $row['Field'];
      }
   }
   if(count($cur_fields) > 0)
   {
      if(!in_array('group_name', $cur_fields))
      {
         $older_version_exists = true;
      }
   }
}
// If older version of AdiInviter is found then take the backup of database tables.
if($older_version_exists)
{
   $tbnames = array(
      'adiinviter',
      'adiinviter_group',
      'adiinviter_list',
      'adiinviter_queue',
      'adiinviter_settings',
   );
   foreach($tbnames as $tbname)
   {
      if(in_array($cur_prefix.$tbname, $tables_list))
      {
         if($result = adi_build_query_write('rename_table', array(
            'current_name' => $cur_prefix.$tbname,
            'new_name' => $temp_prefix.$tbname,
         )))
         {
            $backup_tables[] = $temp_prefix . $tbname;
         }
      }
   }
}


/********************************************************************************************/


// AdiInviter Settings
$settings_table = ADI_TABLE_PREFIX.'adiinviter_settings';
$settings_table_exists = false;
adi_flush_contents('<li class="head">AdiInviter Pro Settings</li>');

if(in_array($settings_table, $tables_list))
{
   adi_flush_contents('<li class="success">Settings table already exists.</li>');
   $settings_table_exists = true;
}
else
{
   // Create Settings Table
   if(adi_build_query_write('create_settings_table'))
   {
      adi_flush_contents('<li class="success">Settings table created.</li>');
      $settings_table_exists = true;
   }
   else
   {
      adi_flush_contents('<li class="error">Failed to create settings table.</li>');
   }
}
$setting_str = '';
if($settings_table_exists == true)
{
   // Insert default settings
   $insert_count = 0;
   $adi_installer = adi_allocate_pack('Adi_Installer');
   $default_settings = $adi_installer->get_default_settings();

   $all_settings =& $pre_settings;

   foreach($all_settings as $sg_name => $group_settings)
   {
      if(isset($adiinviter->pre_settings[$sg_name]))
      {
         $group_settings = array_merge($group_settings, $adiinviter->pre_settings[$sg_name]);
      }

      $db_settings = adi_getSetting($sg_name);

      // Check for existing settings.
      foreach($group_settings as $name => $value)
      {
         if(isset($db_settings[$name]))
         {
            unset($group_settings[$name]);
            if(isset($default_settings[$sg_name][$name]))
            {
               unset($default_settings[$sg_name][$name]);
            }
         }
      }

      if($sg_name == 'db_info')
      {
         $new_user_table = isset($default_settings[$sg_name]['user_table']) ? $default_settings[$sg_name]['user_table']['table_name'] : '';
         if(!empty($new_user_table))
         {
            $user_fields = array();
            if($result = adi_build_query_read('check_table_structure', array(
                  'table_name' => $new_user_table,
               )))
            {
               while($row = adi_fetch_assoc($result))
               {
                  $user_fields[] = $row['Field'];
               }
            }
            if(!in_array('adi_num_invites', $user_fields))
            {
               adi_build_query_write('add_invite_limit_column', array(
                  'table_name' => $new_user_table,
               ));
            }
         }
      }

      // Add settings
      if(count($group_settings) > 0)
      {
         foreach($group_settings as $name => $value)
         {
            if(isset($default_settings[$sg_name][$name]))
            {
               $value = $default_settings[$sg_name][$name];
               unset($default_settings[$sg_name][$name]);
            }
            if(in_array($name, $adiinviter->json_format_settings)) {
               $value = adi_json_encode($value);
            }
            adi_build_query_write('add_setting', array(
               'setting_group_name' => $sg_name,
               'setting_name'       => $name,
               'setting_value'      => adi_escape_string($value),
            ));
            $insert_count++;
         }
      }

      // Add Custom settings
      if(isset($default_settings[$sg_name]))
      {
         if(count($default_settings[$sg_name]) > 0)
         {
            foreach($default_settings[$sg_name] as $name => $value)
            {
               if(in_array($name, $adiinviter->json_format_settings)) {
                  $value = adi_json_encode($value);
               }
               adi_build_query_write('add_setting', array(
                  'setting_group_name' => $sg_name,
                  'setting_name'  => $name,
                  'setting_value' => adi_escape_string($value),
               ));
               $insert_count++;
            }
         }
      }
   }
   if($insert_count > 0)
   {
      adi_flush_contents('<li class="success">Default settings loaded.</li>');
   }
   else
   {
      adi_flush_contents('<li class="message">Settings already exists.</li>');
   }
}
else
{
   adi_flush_contents('<li class="error">Failed to load default settings.</li>');
}
adi_flush_contents('<li class="sep"></li>');




// AdiInviter Phrases
$lang_table_name = ADI_TABLE_PREFIX . 'adiinviter_lang';
$lang_table_exists = false;
adi_flush_contents('<li class="head">AdiInviter Pro Language</li>');
if(in_array($lang_table_name, $tables_list))
{
   adi_flush_contents('<li class="success">Language table already exists.</li>');
   $lang_table_exists = true;
}
else
{
   // Create Language Table
   if(adi_build_query_write('create_language_table'))
   {
      adi_flush_contents('<li class="success">Language table created.</li>');
      $lang_table_exists = true;
   }
   else
   {
      adi_flush_contents('<li class="error">Failed to create language table.</li>');
   }
}
if($lang_table_exists == true)
{
   // Insert Language Phrases
   $lang_path = ADI_LANG_PATH;
   $adiinviter->loadCache('language');
   $lang_ids = $adiinviter->get_lang_ids_for_install();

   if(count($lang_ids) > 0)
   {
      $adi_installer = adi_allocate_pack('Adi_Installer');
      foreach($lang_ids as $lang_id)
      {
         if($adi_installer->install_language($lang_id))
         {
            $lang_name = $adiinviter->cache['language'][$lang_id];
            adi_flush_contents('<li class="success">Default phrases loaded for the language : '.$lang_name.'.</li>');
         }
      }
   }
}
else
{
   adi_flush_contents('<li class="error">Failed to load default phrases.</li>');
}
adi_flush_contents('<li class="sep"></li>');


// AdiInviter Invitation Table
$invitation_table_name = ADI_TABLE_PREFIX . 'adiinviter';
$invitation_table_exists = false;
adi_flush_contents('<li class="head">AdiInviter Pro Invitations</li>');
if(in_array($invitation_table_name, $tables_list))
{
   adi_flush_contents('<li class="success">Invitations table already exists.</li>');
   $invitation_table_exists = true;
}
else
{
   // Create Language Table
   if( $older_version_exists )
   {
      // Restore Backup
      adi_build_query_write('rename_table', array(
         'current_name' => $temp_prefix.'adiinviter',
         'new_name' => ADI_TABLE_PREFIX.'adiinviter',
      ));
      $ind = array_search($temp_prefix.'adiinviter', $backup_tables);
      if(isset($backup_tables[$ind])) {
         unset($backup_tables[$ind]);
      }

      // Update Table Structure
      $adiinviter_table_name = ADI_TABLE_PREFIX.'adiinviter';
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `adihash` `invitation_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL FIRST");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `uid` `inviter_id` int(11) NOT NULL AFTER `invitation_id`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` ADD `guest_id` int(11) NOT NULL DEFAULT '0' AFTER `inviter_id`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `reguid` `receiver_userid` int(11) NOT NULL DEFAULT '0' AFTER `guest_id`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `uname` `receiver_username` varchar(100) COLLATE utf8_unicode_ci NOT NULL AFTER `receiver_userid`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `name` `receiver_name` text COLLATE utf8_unicode_ci NOT NULL AFTER `receiver_username`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `id` `receiver_social_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `receiver_name`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `email` `receiver_email` text COLLATE utf8_unicode_ci NOT NULL AFTER `receiver_social_id`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `status` `invitation_status` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `receiver_email`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `service` `service_used` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `invitation_status`");
      adi_query_write("SET time_zone = '+00:00'");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` ADD `issued_date` bigint(20) NOT NULL AFTER `service_used`");
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET issued_date = UNIX_TIMESTAMP( `issued` ) ");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` DROP `issued`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `type` `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `issued_date`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `sharetype` `campaign_id` varchar(60) NOT NULL DEFAULT '' AFTER `type`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `topicid` `content_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL AFTER `campaign_id`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` CHANGE `topic_redirect` `topic_redirect` int(11) NOT NULL DEFAULT '0' AFTER `content_id`");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` ADD `visited` int(1) NOT NULL DEFAULT '0'");
      adi_query_write("ALTER TABLE `".$adiinviter_table_name."` DROP `domain`");

      // Migrate existing data
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET invitation_status = 'invitation_sent' WHERE invitation_status IN ('Pending','pending','Expired','expired')");
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET invitation_status = 'blocked' WHERE invitation_status = 'Blocked'");
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET invitation_status = 'accepted' WHERE invitation_status = 'Accepted'");
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET invitation_status = 'waiting' WHERE invitation_status LIKE 'Wait%' OR invitation_status LIKE 'wait%'");
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET `receiver_username` = '' where `receiver_username` = 'None'");
      adi_query_write("UPDATE `".$adiinviter_table_name."` SET `campaign_id` = '' where `campaign_id` = 'inv'");

      $old_services = array( 'gmail' => 'Gmail', 'yahoo' => 'Yahoo', 'hotmail' => 'Hotmail', 'aol' => 'AOL', 'mail_com' => 'Mail.com', 'icloud' => 'iCloud', 'gmx_net' => 'Gmx.net', 'freenet_de' => 'Freenet', 'arcor' => 'Arcor', 'libero' => 'Libero', 'virgilio' => 'Virgilio', 'mailchimp' => 'MailChimp', 'excite_it' => 'Excite.it', 'tonline' => 't-online.de', 'email_it' => 'Email.it', 'sapo' => 'Sapo', 'fastmail' => 'Fastmail', 'gawab' => 'Gawab', 'terra' => 'Terra', 'bol_com_br' => 'Bol.com.br', 'operamail' => 'Operamail', 'interia' => 'Interia', 'mynet' => 'Mynet', 'inbox' => 'Inbox', 'qip' => 'Qip.ru', 'mail_ru' => 'Mail.ru', 'rambler' => 'Rambler', 'yandex' => 'Yandex', 'pochta' => 'Pochta', 'freemail' => 'Freemail', 'netaddress' => 'NetAddress', 'eventbrite' => 'Eventbrite', 'hushmail' => 'Hushmail', 'atlas' => 'Atlas', 'azet' => 'Azet', 'adiinviter_facebook' => 'Facebook', 'linkedin' => 'Linkedin', 'orkut' => 'Orkut', 'aussiemail' => 'Aussiemail', 'youtube' => 'YouTube', 'techemail' => 'Techemail', 'meta' => 'Meta', 'plazes' => 'Plazes', 'zoho_com' => 'Zoho.com', 'mail2world' => 'Mail2World', 'o2' => 'O2', 'wpl' => 'WPL', 'nextmail_ru' => 'NextMail.ru', 'bigstring' => 'BigString', 'india' => 'India', 'evite' => 'Evite', 'abv' => 'ABV', 'indiatimes' => 'Indiatimes', 'mail_in' => 'Mail.in', 'rediff' => 'Rediff', 'web_de' => 'Web.de', 'hi5' => 'Hi5', 'clix_pt' => 'Clix.pt', 'kincafe' => 'Kincafe', 'hyves' => 'Hyves', 'meinvz' => 'Meinvz', 'lastfm' => 'Last.fm', 'plaxo' => 'Plaxo', 'livejournal' => 'LiveJournal', 'lycos' => 'Lycos', 'skyrock' => 'Skyrock', 'bebo' => 'Bebo', 'flickr' => 'Flickr', 'myspace' => 'MySpace', 'twitter' => 'Twitter', 'xing' => 'Xing', 'iol_pt' => 'iOL.pt', 'paracalls_com' => 'Paracalls', 'runbox' => 'Runbox', 'citromail_hu' => 'CitroMail.hu', 'onet_pl' => 'Onet.pl', 'sina_com' => 'Sina', 'qq_com' => 'qq', 'tom' => 'Tom.com', 'sohu' => 'Sohu.com', 'yeah' => 'Yeah', 'ost_com' => '163.com', 'ots_com' => '126.com', 'daum_net' => 'Daum', 'naver_com' => 'Naver', 'laposte' => 'Laposte', 'bsnl_in' => 'Bsnl.in', 'dataone_in' => 'Dataone.in', 'walla' => 'Walla', 'nz11' => 'nz11', 'csv_inviter' => 'Contact File', 'manual_inviter' => 'Manually', );
      foreach($old_services as $serice_key => $service_name)
      {
         $query = "UPDATE `".$adiinviter_table_name."` SET `service_used` = '".$service_key."' WHERE `service_used` = '".$service_name."'";
         adi_query_write($query);
      }
   }
   else
   {
      if(adi_build_query_write('create_inviations_table'))
      {
         adi_flush_contents('<li class="success">Invitations table created.</li>');
         $invitation_table_exists = true;
      }
      else
      {
         adi_flush_contents('<li class="error">Failed to create invitations table.</li>');
      }
   }
}
adi_flush_contents('<li class="sep"></li>');


// AdiInviter Invitation Table
$guest_table_name = ADI_TABLE_PREFIX . 'adiinviter_guest';
$guest_table_exists = false;
adi_flush_contents('<li class="head">AdiInviter Pro Guest Info</li>');
if(in_array($guest_table_name, $tables_list))
{
   adi_flush_contents('<li class="success">Guest info table already exists.</li>');
   $guest_table_exists = true;
}
else
{
   // Create Language Table
   if(adi_build_query_write('create_guest_table'))
   {
      adi_flush_contents('<li class="success">Guest info table created.</li>');
      $guest_table_exists = true;
   }
   else
   {
      adi_flush_contents('<li class="error">Failed to create guest info table.</li>');
   }
}
adi_flush_contents('<li class="sep"></li>');



// AdiInviter Contacts Cache Table
$conts_table_name = ADI_TABLE_PREFIX . 'adiinviter_conts';
$conts_table_exists = false;
adi_flush_contents('<li class="head">AdiInviter Pro Conacts Cache</li>');
if(in_array($conts_table_name, $tables_list))
{
   adi_flush_contents('<li class="success">Conacts cache table already exists.</li>');
   $conts_table_exists = true;
}
else
{
   // Create Language Table
   if(adi_build_query_write('create_conts_table'))
   {
      adi_flush_contents('<li class="success">Conacts cache table created.</li>');
      $conts_table_exists = true;
   }
   else
   {
      adi_flush_contents('<li class="error">Failed to create conacts cache table.</li>');
   }
}
adi_flush_contents('<li class="sep"></li>');



// AdiInviter Invitation Table
$queue_table_name = ADI_TABLE_PREFIX . 'adiinviter_queue';
$queue_table_exists = false;
adi_flush_contents('<li class="head">AdiInviter Pro Mail Queue</li>');
if(in_array($queue_table_name, $tables_list))
{
   adi_flush_contents('<li class="success">Mail queue table already exists.</li>');
   $queue_table_exists = true;
}
else
{
   // Create Language Table
   if(adi_build_query_write('create_queue_table'))
   {
      adi_flush_contents('<li class="success">Mail queue table created.</li>');
      $queue_table_exists = true;
   }
   else
   {
      adi_flush_contents('<li class="error">Failed to create mail queue table.</li>');
   }
}
adi_flush_contents('<li class="sep"></li>');


// Check if there are any mails in the mail queue
if($older_version_exists)
{
   $o_settings = array();
   $old_settings_tablename = $temp_prefix.'adiinviter_settings';
   if(in_array($old_settings_tablename, $backup_tables))
   {
      $query = 'SELECT * FROM `'.$old_settings_tablename.'`';
      if($result = adi_query_read($query))
      {
         while($row = adi_fetch_array($result))
         {
            $o_settings[$row['name']] = $row['value'];
         }
      }
   }
   if(count($o_settings) > 0)
   {
      $backup_options = array(
         'global' => array(
            'adiinviter_recaptcha_private_key' => 'captcha_public_key',
            'adiinviter_recaptcha_public_key' => 'captcha_private_key',
            'adiinviter_website_logo' => 'adiinviter_website_logo',
         ),
         'oauth' => array(
            'adiinviter_google_consumer_key' => 'google_consumer_key',
            'adiinviter_google_consumer_secret' => 'google_consumer_secret',
            'adiinviter_hotmail_consumer_key' => 'hotmail_consumer_key',
            'adiinviter_hotmail_consumer_secret' => 'hotmail_consumer_secret',
            'adiinviter_yahoo_consumer_key' => 'yahoo_consumer_key',
            'adiinviter_yahoo_consumer_secret' => 'yahoo_consumer_secret',
            'adiinviter_linkedin_consumer_key' => 'linkedin_consumer_key',
            'adiinviter_linkedin_consumer_secret' => 'linkedin_consumer_secret',
            'adiinviter_mailchimp_consumer_api_key' => 'mailchimp_consumer_api_key',
            'adiinviter_mailchimp_consumer_key' => 'mailchimp_consumer_key',
            'adiinviter_mailchimp_consumer_secret' => 'mailchimp_consumer_secret',
         ),
      );
      foreach($backup_options as $gname => $suboptions)
      {
         foreach($suboptions as $old_name => $new_name)
         {
            if(isset($o_settings[$old_name]) && !empty($o_settings[$old_name]))
            {
               adi_saveSetting($gname, $new_name, $o_settings[$old_name]);
            }
         }
      }
   }

   // Remove backup tables
   if(count($backup_tables) > 0)
   {
      foreach($backup_tables as $btable_name)
      {
         $query = "DROP TABLE `".$btable_name."`";
         adi_query_write($query);
      }
   }
}


echo '<script type="text/javascript"> $(".step_1_submit_btn").show(); </script>';

?>