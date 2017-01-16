<?php 


class AdiInviterPro_Platform extends AdiInviterPro_Base
{
	public $adi_admincp_folder           = 'find_friends/adi_admincp';
	public $current_platform             = 'zend';
	public $current_platform_version     = 2;

	public $max_page_width               = 1060;

	public $verify_invitation_url        = '[website_root_url]/find-friends/verify';
	public $invite_history_url           = '[website_root_url]/find-friends/invite-history';
	public $inpage_model_url             = '[website_root_url]/find-friends';
	public $popup_model_url              = '[website_root_url]/';
}


?>