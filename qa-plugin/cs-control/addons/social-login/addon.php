<?php

/*
  Name:CS Social Login
  Version:1.0
  Author: Amiya Sahu
  Description:For enabling social logins
 */

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

qa_register_plugin_module('widget', 'addons/social-login/widget.php', 'cs_social_login_widget', 'CS Social Login');
qa_register_plugin_overrides('addons/social-login/cs-social-logins-overrides.php');
qa_register_plugin_module('page', 'addons/social-login/page.php', 'cs_social_login_page', 'CS Social Login Page');
qa_register_plugin_module('page', 'addons/social-login/invite-friends.php', 'cs_social_invite_friends_page', 'CS Social Invite Friends Page');

class Cs_Social_Login_Addon {

      function __construct() {
            cs_event_hook('doctype', NULL, array($this, 'navigation'));
            cs_event_hook('language', NULL, array($this, 'language'));
            cs_event_hook('enqueue_css', NULL, array($this, 'css'));
            cs_event_hook('enqueue_scripts', NULL, array($this, 'script'));
            cs_event_hook('init_queries', NULL, array($this, 'init_queries'));
      }

      public function init_queries($tableslc) {
            $queries = array();

            $columns = qa_db_read_all_values(qa_db_query_sub('describe ^userlogins'));
            if (!in_array('oemail', $columns)) {
                  $queries[] = 'ALTER TABLE ^userlogins ADD `oemail` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL';
            }

            $columns = qa_db_read_all_values(qa_db_query_sub('describe ^users'));
            if (!in_array('oemail', $columns)) {
                  $queries[] = 'ALTER TABLE ^users ADD `oemail` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL';
            }

            if (count($queries)) {
                  return $queries;
            }
      }

      public function navigation($themeclass) {
            return $themeclass;
      }

      public function language($lang_arr) {
            $site_lang = qa_opt('site_language');
            $lang_file = CS_CONTROL_DIR . '/addons/social-login/language-' . qa_opt('site_language') . '.php';

            if (!empty($site_lang) && file_exists($lang_file)) $lang_arr = require_once ($lang_file);
            else $lang_arr = require_once (CS_CONTROL_DIR . '/addons/social-login/language.php');

            return $lang_arr;
      }

      public function css($css_src) {
            $css_src['cs_social_login'] = CS_CONTROL_URL . '/addons/social-login/styles.css';
            return $css_src;
      }

      public function script($script_src) {
            $script_src['cs_social_login'] = CS_CONTROL_URL . '/addons/social-login/script.js';
            return $script_src;
      }

}

$cs_social_login_addon = new Cs_Social_Login_Addon;
// load the plugin modules for each provider listed in the file 
if (!QA_FINAL_EXTERNAL_USERS) { // login modules don't work with external user integration
      // since we're not allowed to access the database at this step, take the information from a local file
      // note: the file providers.php will be automatically generated when the configuration of the plugin
      // is updated on the Administration page
      $providers = @include_once CS_CONTROL_DIR . '/inc/hybridauth/providers.php';
      if ($providers) {
            // loop through all active providers and register them
            $providerList = explode(',', $providers);
            foreach ($providerList as $provider) {
                  qa_register_plugin_module('login', 'addons/social-login/cs-social-login-module.php', 'cs_open_login', $provider);
            }
      }
}