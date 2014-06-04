<?php


/*
	Plugin Name: CS Control
	Plugin URI: http://rahularyan.com/cleanstrap
	Plugin Description: This is the helper plugin for cleanstrap theme developed by rahularyan.com
	Plugin Version: 1.0
	Plugin Date: 2014-21-03
	Plugin Author: Rahularyan.com
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: 
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

//return; // use this if theme is disabled

define('CS_CONTROL_DIR', dirname( __FILE__ ));
define('CS_CONTROL_ADDON_DIR', CS_CONTROL_DIR.'/addons');
define('CS_VERSION', 2);

require_once CS_CONTROL_DIR. '/functions.php';

define('CS_BASE_URL', get_base_url());
define('CS_CONTROL_URL', CS_BASE_URL.'/qa-plugin/qawork');
define('Q_THEME_URL', CS_BASE_URL.'/qa-theme/qawork-base-theme');
define('Q_THEME_DIR', QA_THEME_DIR . '/qawork-base-theme');

$theme_function = Q_THEME_DIR.'/functions.php';

if(file_exists($theme_function))
	include_once $theme_function;
else
	return;

// register plugin language
qa_register_plugin_phrases('language/cs-lang-*.php', 'cleanstrap');


qa_register_plugin_overrides('overrides.php');

qa_register_plugin_module('event', 'inc/init.php', 'cs_init', 'CS Init');
qa_register_plugin_module('event', 'inc/cs-event-logger.php', 'cs_event_logger', 'CS Event Logger');
qa_register_plugin_module('event', 'inc/cs-user-events.php', 'cs_user_event_logger', 'CS User Event Logger');

qa_register_plugin_module('widget', 'widgets/widget_ticker.php', 'cs_ticker_widget', 'CS Ticker');
qa_register_plugin_module('widget', 'widgets/widget_activity.php', 'cs_activity_widget', 'CS Site Activity');
qa_register_plugin_module('widget', 'widgets/widget_ask.php', 'cs_ask_widget', 'CS Ajax Ask');
qa_register_plugin_module('widget', 'widgets/widget_ask_form.php', 'cs_ask_form_widget', 'CS Ask Form');
qa_register_plugin_module('widget', 'widgets/widget_categories.php', 'widget_categories', 'CS Categories');
qa_register_plugin_module('widget', 'widgets/widget_tags.php', 'cs_tags_widget', 'CS Tags');
qa_register_plugin_module('widget', 'widgets/widget_text.php', 'cs_widget_text', 'CS Text Widget');
qa_register_plugin_module('widget', 'widgets/widget_current_category.php', 'cs_current_category_widget', 'CS Current Cat');
qa_register_plugin_module('widget', 'widgets/widget_user_posts.php', 'cs_user_posts_widget', 'CS User Posts');
qa_register_plugin_module('widget', 'widgets/widget_featured_questions.php', 'cs_featured_questions_widget', 'CS Featured Questions');
qa_register_plugin_module('widget', 'widgets/widget_question_activity.php', 'cs_question_activity_widget', 'CS Question Activity');
qa_register_plugin_module('widget', 'widgets/widget_related_questions.php', 'cs_related_questions', 'CS Related Questions');
qa_register_plugin_module('widget', 'widgets/widget_users_list.php', 'cs_users_list_widget', 'CS Users List');

qa_register_plugin_module('widget', 'widgets/widget_posts.php', 'cs_widget_posts', 'CS Posts');
qa_register_plugin_module('widget', 'widgets/widget_user_activity.php', 'cs_user_activity_widget', 'CS User Activity');
qa_register_plugin_module('widget', 'widgets/widget_social.php', 'cs_social_widget', 'CS Social Widget');

qa_register_plugin_module('page', 'options.php', 'cs_theme_options', 'Theme Options');
qa_register_plugin_module('page', 'widgets.php', 'cs_theme_widgets', 'Theme Widgets');



qa_register_plugin_layer('cs-layer.php', 'CS Control Layer');


//load all addons
cs_load_addons();

//register addons language
if (cs_hook_exist('register_language')){
	$lang_file_array = cs_apply_filter('register_language', array());

	if(isset($lang_file_array) && is_array($lang_file_array)){
		foreach($lang_file_array as $key => $file){
			qa_register_phrases($file, $key);
		}
	}
}

cs_event_hook('enqueue_css', NULL, 'cs_admin_enqueue_css');
function cs_admin_enqueue_css($css_src){
	$css_src['cs_admin'] = CS_CONTROL_URL . '/css/admin.css';
	$css_src['bootstrap'] = CS_CONTROL_URL. '/css/bootstrap.css';
	
	if (qa_request() == 'themeoptions') {
		$css_src['cs_spectrum'] = CS_CONTROL_URL . '/css/spectrum.css';		
	}

	return  $css_src;
}
cs_event_hook('enqueue_scripts', NULL, 'cs_admin_enqueue_scripts');
function cs_admin_enqueue_scripts($src){
	if (qa_request() == 'themeoptions') {
		$src['cs_admin'] = CS_CONTROL_URL . '/js/admin.js';
		$src['spectrum'] = CS_CONTROL_URL . '/js/spectrum.js';
	}
	$src['jquery'] = CS_CONTROL_URL. '/js/jquery-1.11.0.min.js';			
	$src['bootstrap'] = CS_CONTROL_URL. '/js/bootstrap.js';	
	return  $src;
}

cs_event_hook('cs_ajax_save_widget_position', NULL, 'cs_ajax_save_widget_position');
function cs_ajax_save_widget_position()
{
	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
		$position     = strip_tags($_REQUEST['position']);
		$jsonstring = stripslashes2(str_replace('\"', '"', $_REQUEST['widget_names']));
		$widget_names = json_decode($jsonstring, true);
		$newid        = array();
		if (isset($widget_names) && is_array($widget_names))
			foreach ($widget_names as $k => $w) {
				$param = array(
					'locations' => $w['locations'],
					'options' => $w['options']
				);
				if (isset($w['id']) && $w['id'] > 0)
					$newid[] = widget_opt($w['name'], $position, $k, serialize($param), $w['id']);
				else
					$newid[] = widget_opt($w['name'], $position, $k, serialize($param));
			}
		
		echo json_encode($newid);
	}
	die();
}

cs_event_hook('cs_ajax_delete_widget', NULL, 'cs_ajax_delete_widget');
function cs_ajax_delete_widget()
{

	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
		$id = strip_tags($_REQUEST['id']);
		widget_opt_delete($id);
	}
	die();
}

cs_add_filter('init_queries', 'cs_create_widgets_table');
function cs_create_widgets_table($queries, $tableslc){

	$tablename=qa_db_add_table_prefix('ra_widgets');			
	if (!in_array($tablename, $tableslc)) {
		  $queries[] = 'CREATE TABLE IF NOT EXISTS ^ra_widgets ('.
			'id INT(10) NOT NULL AUTO_INCREMENT,'.				
			'name VARCHAR (64),'.				
			'position VARCHAR (64),'.				
			'widget_order INT(2) NOT NULL DEFAULT 0,'.				
			'param LONGTEXT,'.				
			'PRIMARY KEY (id),'.
			'UNIQUE KEY id (id)'.				
		') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
	}

	return $queries;

}

cs_event_hook('cs_ajax_upload_cover', NULL, 'cs_upload_cover_modal');
function cs_upload_cover_modal(){
	if (qa_is_logged_in()){

	?>
	<div class="modal fade" id="upload_cover_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel"><?php echo qa_lang_html('cs_media/add_media'); ?></h4>
		  </div>
		  <div class="modal-body">			
			<form id="file-upload" method="POST" enctype="multipart/form-data">
				<div class="file-input-wrapper">
					<button class="btn-file-input btn"><?php echo qa_lang_html('cs_media/select_a_file'); ?></button>
					<input id="file-upload-input" name="cover" type="file" />
				</div>
				<div id="file-preview" class="clearfix"></div>
				<button type="submit" class="btn btn-success"><?php echo qa_lang_html('cs_media/upload'); ?></button>
				<input type="hidden" name="type" value="cover">
				<input type="hidden" name="code" value="<?php echo qa_get_form_security_code('upload_cover' ); ?>">
			</form>
		  </div>
		  
		</div>
	  </div>
	</div>
	<?php
	}
	die(); 
}