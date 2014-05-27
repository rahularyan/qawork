<?php

/*
	Name:CS Facebook Like Box
	Version:1.0
	Author: Amiya Sahu
	Description:For showing Facebook like Box at the side bar 
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('widget', 'addons/facebook-like-box/widget.php', 'cs_fb_likebox_widget', 'CS Facebook Likebox');

class Cs_Fb_LikeBox_Addon{
	function __construct(){
		cs_event_hook('register_language', NULL, array($this, 'language'));
		cs_event_hook('enqueue_css', NULL, array($this, 'css'));
		cs_event_hook('enqueue_scripts', NULL, array($this, 'script'));
	}
		
	public function language($lang_arr){
		$lang_arr['cs_fb_like_box']   = CS_CONTROL_DIR .'/addons/facebook-like-box/language-*.php';
		return $lang_arr;
	}

	public function css($css_src){
		$css_src['cs_fb_like_box']    = CS_CONTROL_URL . '/addons/facebook-like-box/styles.css';
		return  $css_src;
	}
	
	public function script($script_src){		
		$script_src['cs_fb_like_box'] = CS_CONTROL_URL . '/addons/facebook-like-box/scripts.js';
		return  $script_src;
	}
	
}

$cs_fb_likebox_addon = new Cs_Fb_LikeBox_Addon;