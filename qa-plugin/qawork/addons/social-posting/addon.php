<?php

/*
  Name:QW Social Posting
  Version:1.0
  Author: Amiya Sahu
  Description:For enabling social posting when some new Event happens 
 */

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

require_once QW_CONTROL_DIR.'/addons/social-posting/cs-social-event-post.php';
require_once QW_CONTROL_DIR .'/addons/social-posting/cs-social-posting-utils.php';

qa_register_plugin_module('page', 'addons/social-posting/social-posting-settings.php', 'qw_social_posting_page', 'QW Social Posting Page');
qa_register_plugin_module('page', 'addons/social-posting/invite-friends.php', 'qw_social_invite_friends_page', 'QW Social Invite Friends Page');
qa_register_plugin_module('widget', 'addons/social-posting/invite-friends-widget.php', 'qw_fb_invite_frnds_widget', 'QW Invite Facebook Friends');


class Qw_Social_Posting_Addon {

      function __construct() {
            qw_add_action('user_profile_btn', array($this, 'navigation'));
            qw_event_hook('register_language', NULL, array($this, 'language'));
            qw_event_hook('enqueue_css', NULL, array($this, 'css'));
            qw_event_hook('enqueue_scripts', NULL, array($this, 'script'));
            qw_add_action('qw_theme_option_tab', array($this, 'option_tab'));
            qw_add_action('qw_theme_option_tab_content', array($this, 'option_tab_content'));
            qw_add_action('qw_reset_theme_options', array($this, 'reset_theme_options'));
      }

      public function language($lang_arr) {
		    $lang_arr['qw_social_posting'] = QW_CONTROL_DIR .'/addons/social-posting/language-*.php';
		    return $lang_arr;
      }
      public function css($css_src) {
            $css_src['qw_social_posting'] = QW_CONTROL_URL . '/addons/social-posting/styles.css';
            return $css_src;
      }
      
      public function script($script_src) {
            $script_src['qw_social_posting'] = QW_CONTROL_URL . '/addons/social-posting/script.js';
            // $script_src['qw_social_posting_facebook'] = "http://connect.facebook.net/en_US/all.js";
            return $script_src;
      }
      public function navigation($themeclass) {
        echo '<a class="btn'.(qa_request() == 'social-posting' ? ' active' : ''.'" href="'.qa_path_html('social-posting')).'">'.qa_lang('qw_social_posting/my_social_posting_nav').'</a>';        
      }

      public function reset_theme_options() {
            if (qa_clicked('qw_reset_button')) {
              qa_opt("qw_enable_fb_posting", 0 );
              qa_opt("qw_enable_twitter_posting", 0 );
              $saved=true;
            }
      }

      function option_tab(){
          $saved=false;
          if(qa_clicked('qw_save_button')){   
              qa_opt("qw_enable_fb_posting", !!qa_post_text("qw_enable_fb_posting"));
              qa_opt("qw_enable_twitter_posting", !!qa_post_text("qw_enable_twitter_posting"));
              qa_opt("qw_fb_invite_message", qa_post_text("qw_fb_invite_message_field"));
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
                    <input type="checkbox"' . (qa_opt('qw_enable_fb_posting') ? ' checked=""' : '') . ' id="qw_styling_rtl" name="qw_enable_fb_posting" data-opts="qw_enable_fb_posting_fields">
                  </td>
                </tr>
                </tbody>
              ';
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Enable Twitter Posting</th>
                  <td class="qa-form-tall-data">
                    <input type="checkbox"' . (qa_opt('qw_enable_twitter_posting') ? ' checked=""' : '') . ' id="qw_styling_rtl" name="qw_enable_twitter_posting" data-opts="qw_enable_twitter_posting_fields">
                  </td>
                </tr>
                </tbody>
              ';
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Facebook Invite template 
                      <span class="description">Set the template for facebook invite message ({site_url} will be replaced by your website url )</span>
                  </th>
                  <td class="qa-form-tall-data">
                  <textarea id="qw_styling_rtl" rows=5 name="qw_fb_invite_message_field" data-opts="qw_enable_twitter_posting_fields">'.qa_opt('qw_fb_invite_message').'</textarea>
                  </td>
                </tr>
                </tbody>
              ';

            $output .= '</table></div>';
            echo $output;
    }


} //class

$qw_social_posting_addon = new Qw_Social_Posting_Addon;
