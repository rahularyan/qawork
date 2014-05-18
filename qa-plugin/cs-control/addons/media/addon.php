<?php

/*
	Name:Media
	Version:1.0
	Author: Rahul Aryan
	Description:For adding media in question and answer
*/	

if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class CS_Media_Addon{
	function __construct(){
		cs_add_filter('init_queries', array($this, 'init_queries'));
		
		// hook buttons into head_script
		cs_event_hook('enqueue_scripts', NULL, array($this, 'head_script'));
		
		// hook buttons into head_css
		cs_event_hook('enqueue_css', NULL, array($this, 'head_css'));
		
		// hook buttons in theme layer
		cs_event_hook('ra_post_buttons_hook', NULL, array($this, 'ra_post_buttons'));
		cs_event_hook('footer_bottom', NULL, array($this, 'footer'));
		cs_event_hook('cs_ajax_upload_file', NULL, array($this, 'upload_file'));
		
		
	}
	public function init_queries($queries, $tableslc){
		$tablename=qa_db_add_table_prefix('ra_media');			
		if (!in_array($tablename, $tableslc)) {

			$queries[] ='
				CREATE TABLE IF NOT EXISTS ^ra_media (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `userid` int(10) NOT NULL,
				  `type` varchar(40) NOT NULL,
				  `name` varchar(800) NOT NULL,
				  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `parent` bigint(20) NULL DEFAULT NULL,
				  `parent_post` int(10) NULL DEFAULT NULL,
				  `title` varchar(800) NULL DEFAULT NULL,
				  `decription` longtext NULL DEFAULT NULL,
				  PRIMARY KEY (id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
			';			
		}

		return $queries;
	}	

	public function ra_post_buttons($themeclass, $q_view){
		
		$postid = $q_view['raw']['postid'];

		if (($themeclass->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) && (!empty($q_view)) && $q_view['raw']['type'] == 'Q'){
		ob_start();
		?>
		<button type="button" class="icon-image btn btn-default" data-toggle="modal" data-target="#media-modal">
			Media
			</button>
		<?php
			$themeclass->output(ob_get_clean());
		}
	}
	
	function footer($themeclass){
		ob_start();
		?>
			<!-- Modal -->
		<div class="modal fade" id="media-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Add media</h4>
			  </div>
			  <div class="modal-body">
				<form id="file-upload" method="POST" enctype="multipart/form-data">
					<input id="file-upload-input" name="post_media" type="file" />
					<div id="file-preview" class="clearfix"></div>
					<button type="submit">Submit</button>
					<input type="hidden" name="action" value="upload_file">
				</form>				
				
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			  </div>
			</div>
		  </div>
		</div>
		<?php $themeclass->output(ob_get_clean());
	}
	
	public function head_script($script_src){		
		$script_src['media_script'] = CS_CONTROL_URL . '/addons/media/script.js';
		return $script_src;
		
	}
	
	public function head_css($css_src){
		$css_src['cs_media_css'] = CS_CONTROL_URL . '/addons/media/styles.css';
		return $css_src;
	}
	
	public function upload_file(){
		cs_upload_file('post_media');
		die();
	}

}


// init method
$cs_media_addon = new CS_Media_Addon; 
