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
		cs_event_hook('language', NULL, array($this, 'language'));
		// cs_event_hook('doctype', NULL, array($this, 'doctype'));
	}
		
	public function language($lang_arr){
		$site_lang = qa_opt('site_language');
		$lang_file = CS_CONTROL_DIR. '/addons/breadcrumbs/language-'.qa_opt('site_language').'.php';
		
		if(!empty($site_lang) && file_exists($lang_file))
			$lang_arr = require_once ($lang_file);
		else
			$lang_arr = require_once (CS_CONTROL_DIR. '/addons/breadcrumbs/language.php');

		return $lang_arr;
	}
	
	public function doctype($themeclass){
		return $themeclass->content;
	}
}

