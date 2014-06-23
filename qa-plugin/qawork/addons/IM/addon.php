<?php

/*
	Name:Instant Messaging
	Type:layer
	Class:qw_notification_layer
	Version:1.0
	Author: Rahul Aryan
	Description:For showing ajax users notification
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

global $qa_modules;
//unset Q2A Event Notify so that we can override it
unset($qa_modules['event']['Q2A Event Notify']);

// qa_register_plugin_overrides('addons/IM/overrides.php');
$qw_instant_messenger_addon = new Qw_Instant_Messenger_Addon;

// qa_register_plugin_layer('addons/IM/notification-layer.php', 'QW Notification Layer');
qa_register_plugin_module('page', 'addons/IM/messages.php', 'qw_messages_page', 'QW Messages');

require_once QW_CONTROL_DIR .'/addons/IM/utils.php';
require_once QW_CONTROL_DIR .'/addons/IM/functions.php';

class Qw_Instant_Messenger_Addon{
	function __construct(){
		qw_add_filter('init_queries', array($this, 'init_queries'));
		qw_event_hook('enqueue_css', NULL, array($this, 'css'));
		qw_event_hook('enqueue_scripts', NULL, array($this, 'scripts'));
		qw_event_hook('qw_ajax_read_all_messages', NULL, array($this, 'read_all_messages'));
		qw_event_hook('qw_ajax_get_all_users_list', NULL, array($this, 'get_all_users_list'));
		// qw_event_hook('qw_ajax_messagelist', NULL, array($this, 'messagelist'));
		// qw_event_hook('qw_ajax_mark_all_activity', NULL, array($this, 'mark_all_activity'));
		// qw_event_hook('qw_ajax_mark_all_messages', NULL, array($this, 'mark_all_messages'));
		// qw_event_hook('qw_ajax_activity_count', NULL, array($this, 'activity_count'));
		// qw_event_hook('qw_ajax_messages_count', NULL, array($this, 'messages_count'));
        qw_event_hook('register_language', NULL, array($this, 'language'));
		
		// added hooks for options and option tabs 
		/*qw_add_action('qw_theme_option_tab', array($this, 'option_tab'));
        qw_add_action('qw_theme_option_tab_content', array($this, 'option_tab_content'));
        qw_add_action('qw_reset_theme_options', array($this, 'reset_theme_options'));*/
        qw_event_hook('doctype', NULL, array($this, 'navigation'));
	}
	
	public function init_queries($queries, $tableslc){
		
		$tablename=qa_db_add_table_prefix('ra_email_queue');			
		if (!in_array($tablename, $tableslc)) {

			$queries[] ='
				CREATE TABLE IF NOT EXISTS ^ra_email_queue (
				  id int(6) NOT NULL AUTO_INCREMENT,
				  event varchar(250) NOT NULL,
				  body text NOT NULL,
				  created_by varchar(250) NOT NULL,
				  created_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  status tinyint(1) DEFAULT "0",
				  sent_on timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
			';			
		}
		
		$tablename=qa_db_add_table_prefix('ra_email_queue_receiver');	

		if (!in_array($tablename, $tableslc)) {

			$queries[] ='
				CREATE TABLE IF NOT EXISTS ^ra_email_queue_receiver (
				  id int(6) NOT NULL AUTO_INCREMENT,
				  userid int(10) NOT NULL,
				  email varchar(250) NOT NULL,
				  name varchar(250) NOT NULL,
				  handle varchar(20) NULL,
				  queue_id int(6) NOT NULL,
				  PRIMARY KEY (id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			';			
		}
		
		return $queries;
	}
	
	public function css($css_src){
		
		$css_src['qw_messages'] = QW_CONTROL_URL . '/addons/IM/styles.css';
		return  $css_src;
	}
	
	public function scripts($src){		
		$src['qw_messages'] = QW_CONTROL_URL . '/addons/IM/script.js';

		return  $src;
	}

	public function navigation($themeclass) {
			$link = array(
					'label' => qa_lang_html('messages/messages'),
					'url'   => qa_path_html('messages'),
					'icon'  => 'icon-email' ,
				);
			if(qa_is_logged_in())	{
				$themeclass['navigation']['user']['messages'] = $link ;
				if(qa_request_part(0) == 'messages') {
					$themeclass['navigation']['user']['messages']['selected'] = true;
				}
			}
			
			$handle = qa_request_part(1) ;
			
			if (qa_is_logged_in() && ($handle === qa_get_logged_in_handle() || qa_request_part(0) == 'messages') ) {
				$themeclass['navigation']['sub']['messages'] = $link ;
				if(qa_request_part(0) == 'messages') {
					$themeclass['navigation']['sub']['messages']['selected'] = true;
				}
			}
			return $themeclass;
    }

	public function activitylist(){}
	
	public function read_all_messages(){

		$userid = qa_get_logged_in_userid();
		$messages['messages'] =qw_db_get_all_conversations($userid);
		foreach ($messages['messages'] as &$message) {
			if ($message['fromuserid']==$userid) {
				$message['sent'] = true ;
				$message['received'] = false ;
			}else{
				$message['sent'] = false ;
				$message['received'] = true ;
			}
			$message['ago'] = qa_when_to_html( $message['created'] , 7)['data'];
		}

		echo json_encode($messages) ;
		die();
	}

	public function get_all_users_list(){
		$userid = qa_get_logged_in_userid() ;
		$all_conversations = qw_db_get_all_conversations($userid);
		$users = qw_users_from_msg_list($all_conversations);
		$user_details = qw_get_name_handle_of_users($users);
		foreach ($user_details as &$user_detail) {
			$user_detail['avatar']=qw_get_avatar($user_detail['handle']);
		}
		$data['users'] = $user_details ;
		echo json_encode($data) ;
		die();
	}

	public function language($lang_arr){
		$lang_arr['messages'] = QW_CONTROL_DIR .'/addons/IM/language-*.php';
		return $lang_arr;
	}
	// adding options and option tab 
	public function option_tab(){
		$saved = false;
			
            if (qa_clicked('qw_save_button')) {
                  
            }

		return '<li>
				<a href="#" data-toggle=".qa-part-form-tc-notify">Messages Settings</a>
			</li>';
	  }

	 public function option_tab_content(){}

	 public function reset_theme_options() {}

}

