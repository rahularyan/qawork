<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_messages_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{		
		if ($request=='messages')
			return true;

		return false;
	}
	
	function process_request($request)
	{
		$start=qa_get_start();
		$userid=qa_get_logged_in_userid();
		//	Prepare content for theme
		
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		
		if (!isset($userid))
			qa_redirect('login');
		
		$qa_content=qa_content_prepare();
		$qa_content['title']=qa_lang_html('messages/messages');
		$qa_content['site_title']= qa_opt('site_title') ;
		
		/*if (isset($qa_content['navigation']['sub'])) {
			unset($qa_content['navigation']['sub']) ;
		}*/
		$qa_content['custom'] = $this->get_messages();
		return $qa_content;	
	}
	function get_messages()
	{
		$userid = qa_get_logged_in_userid() ;
		$all_conversations = qw_db_get_all_conversations($userid);
		
		$users = $this->users_from_msg_list($all_conversations);
		$user_details = qw_get_name_handle_of_users($users);
		$users_html = "" ;
		foreach ($user_details as $user_detail) {
			$users_html .= '<li data-mhandle="'.$user_detail['handle'].'" data-mid="'.$user_detail['userid'].'" >
								<div class="avatar" data-mhandle="'.$user_detail['handle'].'" data-mid="'.$user_detail['userid'].'"> 
									<span class="icon-bell status offline"></span>'
								.qw_get_avatar($user_detail['handle']).
								'</div>
							</li>';
		}
		$left_side_bar = '<div id="msg-user-list" class="col-md-4 user-list well">
								<ul>'.$users_html.'</ul>
						  </div>' ;
		
		$msgs = 'select a conversation to see messages ' ;	
		$right_side_bar = '<div id="messages" class="col-md-8 well">'.$msgs.' </div>' ;
		return '<div class="col-md-12">
				'.$left_side_bar.'
				'.$right_side_bar.'
				</div>';			
	}

	function users_from_msg_list($all_conversations , $exclude = array() )
	{
		$loggedin_userid = qa_get_logged_in_userid();
		$users = array() ; 
		foreach ($all_conversations as $conversation) {
			if (isset($conversation['touserid'])) {
				if (!in_array($conversation['touserid'], $users )) {
					$users[] = $conversation['touserid'] ;
				}
			}
			if (isset($conversation['fromuserid'])) {
				if (!in_array($conversation['fromuserid'], $users )) {
					$users[] = $conversation['fromuserid'] ;
				}
			}
		}
		$users = array_diff($users, array($loggedin_userid));
		return array_unique($users);
	}

	function get_template($type)
	{
		
	}
}

