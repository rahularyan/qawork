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
	
	include_once Q_THEME_DIR.'/inc/blocks.php';
	
	
	
	if(isset($_REQUEST['cs_ajax'])){	
		if(isset($_REQUEST['cs_ajax'])){
			$action = 'cs_ajax_'.$_REQUEST['action'];
			if(function_exists($action))
				$action();
		}
		
	}else{
		global $qa_request;
		
		if (qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN){
			if(!(bool)qa_opt('cs_init')){ // theme init 
				// cs_register_widget_position(
					
				// );
				reset_theme_options();
				qa_opt('cs_init',true);
			}

			if(!qa_opt('cs_installed')){
			/* add some option when theme init first time */

				//create table for builder
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS ^ra_widgets ('.
						'id INT(10) NOT NULL AUTO_INCREMENT,'.				
						'name VARCHAR (64),'.				
						'position VARCHAR (64),'.				
						'widget_order INT(2) NOT NULL DEFAULT 0,'.				
						'param LONGTEXT,'.				
						'PRIMARY KEY (id),'.
						'UNIQUE KEY id (id)'.				
					') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
				);
				qa_opt('cs_installed', true); // update db, so that this code should not execute every time

			}

		}		
		
	
		
		
		
	}
