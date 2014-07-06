<?php

/*
	Name:QW Breadcrumbs
	Version:1.0
	Author: Amiya Sahu
	Description:For showing breadcrumbs
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('widget', 'addons/breadcrumbs/widget.php', 'qw_breadcrumbs_widget', 'QW Breadcrumbs');

class Qw_Breadcrumb_Addon{
	function __construct(){
		qw_event_hook('register_language', NULL, array($this, 'language'));
		qw_event_hook('enqueue_css', NULL, array($this, 'css'));
		//qw_event_hook('enqueue_script', NULL, array($this, 'script'));
	}
		
	public function language($lang_arr){
		$lang_arr['qw_breadcrumbs'] = QW_CONTROL_DIR .'/addons/breadcrumbs/language-*.php';
		return $lang_arr;
	}

	public function css($css_src){
		
		$css_src['qw_breadcrumbs'] = array('file' => QW_CONTROL_URL . '/addons/breadcrumbs/styles.css');
		return  $css_src;
	}
	
	public function script($script_src){		
		$script_src['qw_breadcrumbs'] = array('file' => QW_CONTROL_URL . '/addons/breadcrumbs/scripts.js');
		return  $script_src;
	}
	
}

$qw_breadcrumbs_addon = new Qw_Breadcrumb_Addon;