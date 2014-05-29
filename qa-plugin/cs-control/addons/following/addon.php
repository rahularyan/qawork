<?php

/*
	Name:Following
	Version:1.0
	Author: Rahul Aryan
	Description:User followers list and widgets
*/	

if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


qa_register_plugin_module('widget', 'addons/following/widget-following.php', 'cs_following_widget', 'CS Following');
qa_register_plugin_module('page', 'addons/following/page.php', 'cs_following_page', 'CS following Page');

$cs_following = new Cs_Follwing;
	
	class Cs_Follwing{
		function __construct(){
			cs_add_filter('template_array', array($this, 'page_templates'));
			cs_event_hook('doctype', NULL, array($this, 'navigation'));
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
				
				if(qa_request_part(0) == 'followers') {
					$themeclass['navigation']['user']['following']['selected'] = true;
				}
			
			}	
		
			return $themeclass;
		}
		function page_templates($templates){			
			$templates['following'] = 'Following';
			return $templates;
		}

	}