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



define('CS_CONTROL_URL', get_base_url().'/qa-plugin/cs-control');
define('Q_THEME_URL', get_base_url().'/qa-theme/qawork');
define('Q_THEME_DIR', QA_THEME_DIR . '/qawork');



include_once Q_THEME_DIR.'/functions.php';

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
qa_register_plugin_module('widget', 'widgets/widget_new_users.php', 'cs_new_users_widget', 'CS New Users');
qa_register_plugin_module('widget', 'widgets/widget_site_status.php', 'cs_site_status_widget', 'CS Site Status');
qa_register_plugin_module('widget', 'widgets/widget_top_users.php', 'cs_top_users_widget', 'CS Top Contributors');
qa_register_plugin_module('widget', 'widgets/widget_posts.php', 'cs_widget_posts', 'CS Posts');
qa_register_plugin_module('widget', 'widgets/widget_user_activity.php', 'cs_user_activity_widget', 'CS User Activity');
qa_register_plugin_module('widget', 'widgets/widget_scroller.php', 'cs_widget_scroller', 'CS Scroller');

qa_register_plugin_module('page', 'options.php', 'cs_theme_options', 'Theme Options');
qa_register_plugin_module('page', 'widgets.php', 'cs_theme_widgets', 'Theme Widgets');
qa_register_plugin_module('page', 'install.php', 'cs_theme_install_page', 'Theme Install Page');


qa_register_plugin_layer('cs-layer.php', 'CS Control Layer');


//load all addons
cs_load_addons();


cs_event_hook('enqueue_css', NULL, 'cs_admin_enqueue_css');
function cs_admin_enqueue_css($css_src){
	if (qa_request() == 'themeoptions') {
		$css_src['cs_spectrum'] = Q_THEME_URL . '/css/spectrum.css';		
	}

	return  $css_src;
}
cs_event_hook('enqueue_scripts', NULL, 'cs_admin_enqueue_scripts');
function cs_admin_enqueue_scripts($src){
	if (qa_request() == 'themeoptions') {
		$src['cs_admin'] = Q_THEME_URL . '/js/admin.js';
		$src['spectrum'] = Q_THEME_URL . '/js/spectrum.js';
		$src['uploadfile'] = Q_THEME_URL . '/js/jquery.uploadfile.min.js';
	}
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