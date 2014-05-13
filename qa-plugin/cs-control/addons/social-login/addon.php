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
            cs_event_hook('register_language', NULL, array($this, 'language'));
            cs_event_hook('enqueue_css', NULL, array($this, 'css'));
            cs_event_hook('enqueue_scripts', NULL, array($this, 'script'));
            cs_event_hook('init_queries', NULL, array($this, 'init_queries'));
            cs_event_hook('cs_theme_option_tab', NULL, array($this, 'option_tab'));
            cs_event_hook('cs_theme_option_tab_content', NULL, array($this, 'option_tab_content'));
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
		if(cs_is_user())	
			$themeclass['navigation']['sub']['logins'] = array(
				'label' => qa_lang_html('cs_social_login/my_logins_nav'),
				'url' => qa_path_html('logins')
			);
        return $themeclass;
      }

      public function language($lang_arr) {
		$lang_arr['cs_social_login'] = CS_CONTROL_DIR .'/addons/social-login/language.php';
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
	  
	  function option_tab(){
		$saved=false;
		if(qa_clicked('cs_save_button')){		
				
			// loop through all providers and see which one was enabled
			$allProviders = scandir( CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Providers' );
			
			$activeProviders = array();
			foreach($allProviders as $providerFile) {
				if(substr($providerFile,0,1) == '.') {
					continue;
				}

				$provider = str_ireplace('.php', '', $providerFile);
				$key = strtolower($provider);

				$enabled = qa_post_text("{$key}_app_enabled_field");
				$shortcut = qa_post_text("{$key}_app_shortcut_field");
				qa_opt("{$key}_app_enabled", empty($enabled) ? 0 : 1);
				qa_opt("{$key}_app_shortcut", empty($shortcut) ? 0 : 1);
				qa_opt("{$key}_app_id", qa_post_text("{$key}_app_id_field"));
				qa_opt("{$key}_app_secret", qa_post_text("{$key}_app_secret_field"));
				
				if(!empty($enabled)) {
					$activeProviders[] = $provider;
				}
			}
			
			// at the end save a list of all active providers
			file_put_contents( CS_CONTROL_DIR . '/inc/hybridauth/providers.php', 
				'<' . '?' . 'php return "' . implode(',', $activeProviders) . '" ?' . '>'
			);
			
			// also save the other configurations
			$hidecss = qa_post_text('open_login_css');
			qa_opt('open_login_css', empty($hidecss) ? 0 : 1);
			
			$zocial = qa_post_text('open_login_zocial');
			qa_opt('open_login_zocial', empty($zocial) ? 0 : 1);
			$saved=true;

		}
		return '<li>
				<a href="#" data-toggle=".qa-part-form-tc-hybrid">Hybrid Auth</a>
			</li>';
	  }
	  function option_tab_content(){
		$allProviders = scandir( CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Providers'  );
		$output = '<div class="qa-part-form-tc-hybrid">
			<h3>Social login and postings</h3>
			<table class="qa-form-tall-table options-table">';
		
			foreach($allProviders as $providerFile) {
				if(substr($providerFile,0,1) == '.' || $providerFile == 'OpenID.php') {
					continue;
				}
				
				$provider = str_ireplace('.php', '', $providerFile);
				$key = strtolower($provider);
				
				$output .= '
					<tbody>
					<tr>
						<th class="qa-form-tall-label">Enable ' . $provider.'</th>
						<td class="qa-form-tall-data">
							<input type="checkbox"' . (qa_opt($key.'_app_enabled') ? ' checked=""' : '') . ' id="cs_styling_rtl" name="'.$key.'_app_enabled_field" data-opts=".'.$key.'_fields">
						</td>
					</tr>
					<tr class="'.$key.'_fields' . (qa_opt($key.'_app_enabled') ? ' csshow' : ' cshide') . '">
						<th class="qa-form-tall-label">Show ' . $provider . ' button in the header</th>
						<td class="qa-form-tall-data">
							<input type="checkbox"' . (qa_opt($key.'_app_shortcut') ? ' checked=""' : '') . ' id="cs_styling_rtl" name="'.$key.'_app_shortcut_field">
						</td>
					</tr>
					<tr class="'.$key.'_fields' . (qa_opt($key.'_app_enabled') ? ' csshow' : ' cshide') . '">
						<th class="qa-form-tall-label">'.$provider . ' App ID:</th>
						<td class="qa-form-tall-data">
							<input type="text" value="' . qa_html(qa_opt("{$key}_app_id")) . '" id="cs_styling_rtl" name="'.$key.'_app_id_field">
						</td>
					</tr>
					<tr class="'.$key.'_fields ' . (qa_opt($key.'_app_enabled') ? ' csshow' : ' cshide') . '">
						<th class="qa-form-tall-label">'.$provider . ' App Secret:</th>
						<td class="qa-form-tall-data">
							<input type="text" value="' . qa_html(qa_opt("{$key}_app_secret")) . '" id="cs_styling_rtl" name="'.$key.'_app_secret_field">
						</td>
					</tr>
					</tbody>
				';

			}
				
			$output .= '</table></div>';
			return $output;
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