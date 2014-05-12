<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class cs_social_invite_friends_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		if ($request=='invite-friends')
			return true;

		return false;
	}
	
	function process_request($request)
	{

		$start=qa_get_start();
		$userid=qa_get_logged_in_userid();
		//	Prepare content for theme

		$qa_content=qa_content_prepare();
		
		$qa_content['site_title']='Invite Your Friends ';
		$qa_content['title']='Invite Your Friends ';
		
		if (QA_FINAL_EXTERNAL_USERS)
			qa_fatal_error('User accounts are handled by external code');
		
		$userid=qa_get_logged_in_userid();
		
		if (!isset($userid))
			qa_redirect('login');

		$qa_content['navigation']['sub']=qa_account_sub_navigation();

		return $qa_content ;
	}
	
	function page_content($qa_content){

	}
	
}

