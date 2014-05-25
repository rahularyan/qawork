<?php

/*
	Name:CS Breadcrumbs
	Version:1.0
	Author: Amiya Sahu
	Description:For showing breadcrumbs
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('widget', 'addons/breadcrumbs/widget.php', 'cs_breadcrumbs_widget', 'CS Breadcrumbs');

class Cs_Breadcrumb_Addon{
	function __construct(){
		cs_event_hook('register_language', NULL, array($this, 'language'));
		cs_event_hook('enqueue_css', NULL, array($this, 'css'));
		//cs_event_hook('enqueue_script', NULL, array($this, 'script'));
	}
		
	public function language($lang_arr){
		$lang_arr['cs_breadcrumbs'] = CS_CONTROL_DIR .'/addons/breadcrumbs/language-*.php';
		return $lang_arr;
	}

	public function css($css_src){
		
		$css_src['cs_breadcrumbs'] = CS_CONTROL_URL . '/addons/breadcrumbs/styles.css';
		return  $css_src;
	}
	
	public function script($script_src){		
		$script_src['cs_breadcrumbs'] = CS_CONTROL_URL . '/addons/breadcrumbs/scripts.js';
		return  $script_src;
	}
	
}

$cs_breadcrumbs_addon = new Cs_Breadcrumb_Addon;