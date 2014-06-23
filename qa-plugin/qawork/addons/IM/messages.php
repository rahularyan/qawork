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
		/*$all_conversations = qw_db_get_all_conversations($userid);
		
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
		}*/
		$left_side_bar = '<div id="msg-user-list" class="col-md-4  well">
								<div class="hidden" id="logged-in-user-details" data-userid ="'.qa_get_logged_in_userid().'" data-handle="'.qa_get_logged_in_handle().'">
									 '.qw_get_avatar(qa_get_logged_in_handle()).'
								</div> 
								<ul class="user-list">
									
								</ul>
						  </div>' ;
		
		$msgs = 'select a conversation to see messages ' ;	
		$right_side_bar = '<div id="messages" class="col-md-8 well">'.$msgs.' </div>' ;
		$content =  '<div class="col-md-12">
				'.$left_side_bar.'
				'.$right_side_bar.'
				</div>' ;		
		$templates = $this->get_templates();	
		return $content . $templates;
	}


	function get_templates()
	{
		$messages_template = '
			<script id="messages-template" type="text/x-handlebars-template" class="hidden">
  				<ul>
	  				{{#each messages}}
	  						<li>
	  							<div class="content">
		  							{{{getAvatar this}}}
		  							{{{getContent this}}}
	  							</div>
	  							<span class="time"> {{this.ago}} </span>
	  						</li>
	  				{{/each}}
  				</ul>
			</script>
		';
		$users_template = '
			<script id="users-template" type="text/x-handlebars-template" class="hidden">
	  				{{#each users}}
	  						<li data-mhandle="{{this.handle}}" data-mid="{{this.userid}}" >
								<div class="avatar" data-mhandle="{{this.handle}}" data-mid="{{this.userid}}"> 
									<span class="icon-bell status {{this.status}}" title="{{this.status}}"></span>
									{{{this.avatar}}}
									{{this.handle}}
								</div>
							</li>
	  				{{/each}}
			</script>
		';

		return $messages_template.$users_template;
	}
}

