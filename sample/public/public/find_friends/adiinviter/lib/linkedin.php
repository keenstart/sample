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


class Adi_Service_linkedin_OAuth extends AdiInviter_Pro_Core
{
	public $version      = 1001;
	public $service_name = 'Linkedin.com';
	public $media_key    = 'linkedin';
	public $use_ssl      = true;
	public $use_pm       = true;
	public $email_or_id  = 0;

	function fetchContacts()
	{
		// Execute encrypted Block
		return $this->contacts;
	}

	function sendInvitations($subject, $body, $receivers_data)
	{
		
		return true;
	}

	function endSession()
	{
		
	}
}

?>