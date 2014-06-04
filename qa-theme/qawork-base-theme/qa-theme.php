<?php

	//error_reporting(0);
	//@ini_set('display_errors', 0);
		
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	$qw_error ='';
	
	
	//first check if cs-control in installed
	if (!defined('QW_CONTROL_DIR'))
		qa_fatal_error('Qawork plugin is not installed !  please make sure you have installed qawork plugin. Contact us from http://rahularyan.com/support');
	
	
	
	if(isset($_REQUEST['qw_ajax'])){	
		if(isset($_REQUEST['qw_ajax'])){
			$action = 'qw_ajax_'.$_REQUEST['action'];
			if(function_exists($action))
				$action();
		}
		
	}
