<?php


/*
	Plugin Name: QW Control
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

define('QW_CONTROL_DIR', dirname( __FILE__ ));
define('QW_CONTROL_ADDON_DIR', QW_CONTROL_DIR.'/addons');
define('QW_VERSION', '1.0');

require_once QW_CONTROL_DIR. '/functions.php';

define('QW_BASE_URL', get_base_url());
define('QW_CONTROL_URL', QW_BASE_URL.'/qa-plugin/qawork');
define('Q_THEME_URL', QW_BASE_URL.'/qa-theme/qawork-base-theme');
define('Q_THEME_DIR', QA_THEME_DIR . '/qawork-base-theme');

$theme_function = Q_THEME_DIR.'/functions.php';

if(file_exists($theme_function))
	include_once $theme_function;
else
	return;

// register plugin language
qa_register_plugin_phrases('language/cs-lang-*.php', 'cleanstrap');


qa_register_plugin_overrides('overrides.php');

qa_register_plugin_module('event', 'inc/init.php', 'qw_init', 'QW Init');
qa_register_plugin_module('event', 'inc/cs-event-logger.php', 'qw_event_logger', 'QW Event Logger');
qa_register_plugin_module('event', 'inc/cs-user-events.php', 'qw_user_event_logger', 'QW User Event Logger');

qa_register_plugin_module('widget', 'widgets/widget_ticker.php', 'qw_ticker_widget', 'QW Ticker');
qa_register_plugin_module('widget', 'widgets/widget_activity.php', 'qw_activity_widget', 'QW Site Activity');
qa_register_plugin_module('widget', 'widgets/widget_ask.php', 'qw_ask_widget', 'QW Ajax Ask');
qa_register_plugin_module('widget', 'widgets/widget_ask_form.php', 'qw_ask_form_widget', 'QW Ask Form');
qa_register_plugin_module('widget', 'widgets/widget_categories.php', 'widget_categories', 'QW Categories');
qa_register_plugin_module('widget', 'widgets/widget_tags.php', 'qw_tags_widget', 'QW Tags');
qa_register_plugin_module('widget', 'widgets/widget_text.php', 'qw_widget_text', 'QW Text Widget');
qa_register_plugin_module('widget', 'widgets/widget_current_category.php', 'qw_current_category_widget', 'QW Current Cat');
qa_register_plugin_module('widget', 'widgets/widget_user_posts.php', 'qw_user_posts_widget', 'QW User Posts');
qa_register_plugin_module('widget', 'widgets/widget_featured_questions.php', 'qw_featured_questions_widget', 'QW Featured Questions');
qa_register_plugin_module('widget', 'widgets/widget_question_activity.php', 'qw_question_activity_widget', 'QW Question Activity');
qa_register_plugin_module('widget', 'widgets/widget_related_questions.php', 'qw_related_questions', 'QW Related Questions');
qa_register_plugin_module('widget', 'widgets/widget_users_list.php', 'qw_users_list_widget', 'QW Users List');

qa_register_plugin_module('widget', 'widgets/widget_posts.php', 'qw_widget_posts', 'QW Posts');
qa_register_plugin_module('widget', 'widgets/widget_user_activity.php', 'qw_user_activity_widget', 'QW User Activity');
qa_register_plugin_module('widget', 'widgets/widget_social.php', 'qw_social_widget', 'QW Social Widget');

qa_register_plugin_module('page', 'options.php', 'qw_theme_options', 'Theme Options');
qa_register_plugin_module('page', 'widgets.php', 'qw_theme_widgets', 'Theme Widgets');



qa_register_plugin_layer('cs-layer.php', 'QW Control Layer');


//load all addons
qw_load_addons();

//register addons language
if (qw_hook_exist('register_language')){
	$lang_file_array = qw_apply_filter('register_language', array());

	if(isset($lang_file_array) && is_array($lang_file_array)){
		foreach($lang_file_array as $key => $file){
			qa_register_phrases($file, $key);
		}
	}
}

qw_event_hook('enqueue_css', NULL, 'qw_admin_enqueue_css');
function qw_admin_enqueue_css($css_src){
	$css_src['qw_admin'] = QW_CONTROL_URL . '/css/admin.css';
	$css_src['bootstrap'] = QW_CONTROL_URL. '/css/bootstrap.css';
	
	if (qa_request() == 'themeoptions') {
		$css_src['qw_spectrum'] = QW_CONTROL_URL . '/css/spectrum.css';		
	}

	return  $css_src;
}
qw_event_hook('enqueue_scripts', NULL, 'qw_admin_enqueue_scripts');
function qw_admin_enqueue_scripts($src){
	if (qa_request() == 'themeoptions') {
		$src['qw_admin'] = QW_CONTROL_URL . '/js/admin.js';
		$src['spectrum'] = QW_CONTROL_URL . '/js/spectrum.js';
	}
	$src['jquery'] = QW_CONTROL_URL. '/js/jquery-1.11.0.min.js';			
	$src['bootstrap'] = QW_CONTROL_URL. '/js/bootstrap.js';	
	return  $src;
}

qw_event_hook('qw_ajax_save_widget_position', NULL, 'qw_ajax_save_widget_position');
function qw_ajax_save_widget_position()
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

qw_event_hook('qw_ajax_delete_widget', NULL, 'qw_ajax_delete_widget');
function qw_ajax_delete_widget()
{

	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
		$id = strip_tags($_REQUEST['id']);
		widget_opt_delete($id);
	}
	die();
}

qw_add_filter('init_queries', 'qw_create_widgets_table');
function qw_create_widgets_table($queries, $tableslc){

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

qw_event_hook('qw_ajax_upload_cover', NULL, 'qw_upload_cover_modal');
function qw_upload_cover_modal(){
	if (qa_is_logged_in()){

	?>
	<div class="modal fade" id="upload_cover_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel"><?php echo qa_lang_html('qw_media/add_media'); ?></h4>
		  </div>
		  <div class="modal-body">			
			<form id="file-upload" method="POST" enctype="multipart/form-data">
				<div class="file-input-wrapper">
					<button class="btn-file-input btn"><?php echo qa_lang_html('qw_media/select_a_file'); ?></button>
					<input id="file-upload-input" name="cover" type="file" />
				</div>
				<div id="file-preview" class="clearfix"></div>
				<button type="submit" class="btn btn-success"><?php echo qa_lang_html('qw_media/upload'); ?></button>
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
qw_event_hook('qw_ajax_get_popover_form_code', NULL, 'qw_popover_form_code');
function qw_popover_form_code(){
	//code to check if Ajax request is from this site
	echo '87533984574385';
	
	die();
}
