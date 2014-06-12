<?php

/*
	Name:QW Google Plus Badge
	Version:1.0
	Author: Amiya Sahu
	Description:For showing Google Plus Badge at the side bar 
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('widget', 'addons/google-plus-badge/widget.php', 'qw_google_plus_badge', 'QW Google Plus Badge');

class Qw_Google_Plus_Addon{
	function __construct(){
		qw_event_hook('register_language', NULL, array($this, 'language'));
		qw_event_hook('enqueue_css', NULL, array($this, 'css'));
		qw_event_hook('enqueue_scripts', NULL, array($this, 'script'));
		// qw_add_action('head_script', array($this, 'head_script'));
	}
		
	public function language($lang_arr){
		$lang_arr['qw_gp_badge']   = QW_CONTROL_DIR .'/addons/google-plus-badge/language-*.php';
		return $lang_arr;
	}

	public function css($css_src){
		$css_src['qw_google_plus']    = QW_CONTROL_URL . '/addons/google-plus-badge/styles.css';
		return  $css_src;
	}
	
	public function script($script_src){	
		// $script_src['qw_google_plus_script'] = 'https://apis.google.com/js/plusone.js' ;
		$script_src['qw_google_plus'] = QW_CONTROL_URL . '/addons/google-plus-badge/scripts.js' ;
		return  $script_src;
	}
}

$qw_google_plus_addon = new Qw_Google_Plus_Addon;