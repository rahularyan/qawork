<?php

/*
	Name:Featured
	Version:1.0
	Author: Rahul Aryan
	Description:Widget for showing users followers list
*/	

if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


qa_register_plugin_module('widget', 'addons/followers/widget-followers.php', 'qw_followers_widget', 'QW Followers');
qa_register_plugin_module('page', 'addons/followers/page.php', 'qw_followers_page', 'QW followers Page');

$qw_followers = new Qw_Follwers;
	
	class Qw_Follwers{
		function __construct(){
			qw_add_filter('template_array', array($this, 'page_templates'));
			qw_event_hook('doctype', NULL, array($this, 'navigation'));
		}
		
		public function navigation($themeclass){		
			if(qa_is_logged_in())	{
				$request = qa_request_parts(0);
				//show tags-admin menu if user is admin
				
				$themeclass['navigation']['user']['followers'] = array(
					'label' => qa_lang('cleanstrap/followers'),
					'url' => qa_path_html('followers'),
					'icon' => 'icon-group'
				);
				if(qa_request_part(0) == 'followers') {
					$themeclass['navigation']['user']['followers']['selected'] = true;
				}
			}
			if(qa_request_part(0) == 'followers') {
				$themeclass['navigation']['sub']['followers']['selected'] = true;
				$themeclass['navigation']['user']['followers']['selected'] = true;
			}
			
			$handle = qa_request_part(1) ;
			if(empty($handle)) {
				$handle = qa_get_logged_in_handle();
			}
			
			if(qw_is_user())
				$themeclass['navigation']['sub']['followers'] = array(
					'label' => qa_lang('cleanstrap/followers'),
					'url' => qa_path_html('followers/'.$handle),
					'icon' => 'icon-group'
				);			
		
			return $themeclass;
		}
		function page_templates($templates){			
			$templates['followers'] = 'Followers';
			return $templates;
		}

	}