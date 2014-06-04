<?php

/*
	Name:QW Facebook Like Box
	Version:1.0
	Author: Amiya Sahu
	Description:For showing Facebook like Box at the side bar 
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('widget', 'addons/facebook-like-box/widget.php', 'qw_fb_likebox_widget', 'QW Facebook Likebox');

class Qw_Fb_LikeBox_Addon{
	function __construct(){
		qw_event_hook('register_language', NULL, array($this, 'language'));
		qw_event_hook('enqueue_css', NULL, array($this, 'css'));
		qw_event_hook('enqueue_scripts', NULL, array($this, 'script'));
	}
		
	public function language($lang_arr){
		$lang_arr['qw_fb_like_box']   = QW_CONTROL_DIR .'/addons/facebook-like-box/language-*.php';
		return $lang_arr;
	}

	public function css($css_src){
		$css_src['qw_fb_like_box']    = QW_CONTROL_URL . '/addons/facebook-like-box/styles.css';
		return  $css_src;
	}
	
	public function script($script_src){	
        if (!!qa_opt("facebook_app_id")) {
			$script_src['qw_fb_like_box'] = QW_CONTROL_URL . '/addons/facebook-like-box/scripts.js?applicationId='.qa_opt("facebook_app_id") ;
		}
		return  $script_src;
	}
	
}

$qw_fb_likebox_addon = new Qw_Fb_LikeBox_Addon;