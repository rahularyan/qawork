<?php

/*
  Name:CS Social Posting
  Version:1.0
  Author: Amiya Sahu
  Description:For enabling social posting when some new Event happens 
 */

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

require_once CS_CONTROL_DIR.'/addons/social-posting/cs-social-event-post.php';
require_once CS_CONTROL_DIR .'/addons/social-posting/cs-social-posting-utils.php';

qa_register_plugin_module('page', 'addons/social-posting/social-posting-settings.php', 'cs_social_posting_page', 'CS Social Posting Page');
qa_register_plugin_module('page', 'addons/social-posting/invite-friends.php', 'cs_social_invite_friends_page', 'CS Social Invite Friends Page');
qa_register_plugin_module('widget', 'addons/social-posting/invite-friends-widget.php', 'cs_fb_invite_frnds_widget', 'CS Invite Facebook Friends');


class Cs_Social_Posting_Addon {

      function __construct() {
            cs_add_action('user_profile_btn', array($this, 'navigation'));
            cs_event_hook('register_language', NULL, array($this, 'language'));
            cs_event_hook('enqueue_css', NULL, array($this, 'css'));
            cs_event_hook('enqueue_scripts', NULL, array($this, 'script'));
            cs_add_action('cs_theme_option_tab', array($this, 'option_tab'));
            cs_add_action('cs_theme_option_tab_content', array($this, 'option_tab_content'));
            cs_add_action('cs_reset_theme_options', array($this, 'reset_theme_options'));
      }

      public function language($lang_arr) {
		    $lang_arr['cs_social_posting'] = CS_CONTROL_DIR .'/addons/social-posting/language-*.php';
		    return $lang_arr;
      }
      public function css($css_src) {
            $css_src['cs_social_posting'] = CS_CONTROL_URL . '/addons/social-posting/styles.css';
            return $css_src;
      }
      
      public function script($script_src) {
            $script_src['cs_social_posting'] = CS_CONTROL_URL . '/addons/social-posting/script.js';
            // $script_src['cs_social_posting_facebook'] = "http://connect.facebook.net/en_US/all.js";
            return $script_src;
      }
      public function navigation($themeclass) {
        echo '<a class="btn'.(qa_request() == 'social-posting' ? ' active' : ''.'" href="'.qa_path_html('social-posting')).'">'.qa_lang('cs_social_posting/my_social_posting_nav').'</a>';        
      }

      public function reset_theme_options() {
            if (qa_clicked('cs_reset_button')) {
              qa_opt("cs_enable_fb_posting", 0 );
              qa_opt("cs_enable_twitter_posting", 0 );
              $saved=true;
            }
      }

      function option_tab(){
          $saved=false;
          if(qa_clicked('cs_save_button')){   
              qa_opt("cs_enable_fb_posting", !!qa_post_text("cs_enable_fb_posting"));
              qa_opt("cs_enable_twitter_posting", !!qa_post_text("cs_enable_twitter_posting"));
              $saved=true;
            }
          
          echo '<li>
              <a href="#" data-toggle=".qa-part-form-social-posting">Social Posting</a>
            </li>';
    }
    function option_tab_content(){
          $output = '<div class="qa-part-form-social-posting">
            <h3>Choose Your social Sharing Options</h3>
            <table class="qa-form-tall-table options-table">';
              
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Enable Faebook Posting</th>
                  <td class="qa-form-tall-data">
                    <input type="checkbox"' . (qa_opt('cs_enable_fb_posting') ? ' checked=""' : '') . ' id="cs_styling_rtl" name="cs_enable_fb_posting" data-opts="cs_enable_fb_posting_fields">
                  </td>
                </tr>
                </tbody>
              ';
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Enable Twitter Posting</th>
                  <td class="qa-form-tall-data">
                    <input type="checkbox"' . (qa_opt('cs_enable_twitter_posting') ? ' checked=""' : '') . ' id="cs_styling_rtl" name="cs_enable_twitter_posting" data-opts="cs_enable_twitter_posting_fields">
                  </td>
                </tr>
                </tbody>
              ';

            $output .= '</table></div>';
            echo $output;
    }


} //class

$cs_social_posting_addon = new Cs_Social_Posting_Addon;
