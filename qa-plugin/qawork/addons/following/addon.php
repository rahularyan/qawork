<?php

/*
	Name:Following
	Version:1.0
	Author: Rahul Aryan
	Description:User following list and widgets
*/	

if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


qa_register_plugin_module('widget', 'addons/following/widget-following.php', 'qw_following_widget', 'QW Following');
qa_register_plugin_module('page', 'addons/following/page.php', 'qw_following_page', 'QW following Page');

$qw_following = new Qw_Follwing;
	
	class Qw_Follwing{
		function __construct(){
			qw_add_filter('template_array', array($this, 'page_templates'));
			qw_event_hook('doctype', NULL, array($this, 'navigation'));
		}
		
		public function navigation($themeclass){		
			if(qa_is_logged_in())	{
				$request = qa_request_parts(0);
				//show tags-admin menu if user is admin
				
				$themeclass['navigation']['user']['following'] = array(
					'label' => qa_lang('cleanstrap/following'),
					'url' => qa_path_html('following'),
					'icon' => 'icon-group'
				);
					
				if(qa_request_part(0) == 'following') {					
					$themeclass['navigation']['user']['following']['selected'] = true;
				}
				
				if(qw_is_user())
					$themeclass['navigation']['sub']['following'] = array(
						'label' => qa_lang('cleanstrap/following'),
						'url' => qa_path_html('following'),
						'icon' => 'icon-group'
					);
			}
			if(qa_request_part(0) == 'following') {					
					$themeclass['navigation']['sub']['following']['selected'] = true;
					$themeclass['navigation']['user']['following']['selected'] = true;
				}
			
			$handle = qa_request_part(1) ;
			if(empty($handle)) {
				$handle = qa_get_logged_in_handle();
			}	
			
			if(qw_is_user())
				$themeclass['navigation']['sub']['following'] = array(
					'label' => qa_lang('cleanstrap/following'),
					'url' => qa_path_html('following/'.$handle),
					'icon' => 'icon-group'
				);			
		
			return $themeclass;
		}
		function page_templates($templates){			
			$templates['following'] = 'Following';
			return $templates;
		}

	}