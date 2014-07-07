<?php

/*
	Name:QW Facebook Like Box
	Version:1.0
	Author: Amiya Sahu
	Description:For showing latest tweets on your website  
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


if(function_exists('curl_version')){
	require_once QW_CONTROL_DIR.'/inc/TwitterAPI/TwitterAPIExchange.php';
	qa_register_plugin_module('widget', 'addons/tweet-box/widget.php', 'qw_tweet_box_widget', 'QW TweetBox');
}

class Qw_Tweet_Box_Addon{
	function __construct(){
		qw_event_hook('register_language', NULL, array($this, 'language'));
		qw_event_hook('enqueue_css', NULL, array($this, 'css'));
		//qw_event_hook('enqueue_scripts', NULL, array($this, 'script'));
	}
		
	public function language($lang_arr){
		$lang_arr['qw_tweet_box']   = QW_CONTROL_DIR .'/addons/tweet-box/language-*.php';
		return $lang_arr;
	}

	public function css($css_src){
		$css_src['qw_tweet_box']    = array('file' => QW_CONTROL_URL . '/addons/tweet-box/styles.css');
		return  $css_src;
	}
	
	public function script($script_src){	
		return  $script_src;
	}
	
}

$qw_tweet_box_addon = new Qw_Tweet_Box_Addon;