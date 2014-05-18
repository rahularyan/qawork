<?php

	//error_reporting(0);
	//@ini_set('display_errors', 0);
		
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	$cs_error ='';
	
	
	//first check if cs-control in installed
	if (!defined('CS_CONTROL_DIR'))
		qa_fatal_error('CS Control plugin is not installed !  please make sure you have installed CS Control plugin. Contact us from http://rahularyan.com/support');
	
	
	
	if(isset($_REQUEST['cs_ajax'])){	
		if(isset($_REQUEST['cs_ajax'])){
			$action = 'cs_ajax_'.$_REQUEST['action'];
			if(function_exists($action))
				$action();
		}
		
	}
