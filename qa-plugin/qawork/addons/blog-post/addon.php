<?php

/*
  Name:QW Blog Post
  Version:1.0
  Author: Amiya Sahu
  Description:For enabling sites to publish blogs
 */

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}
require_once QW_CONTROL_DIR .'/addons/blog-post/blog-post-utils.php';

qa_register_plugin_module('page', 'addons/blog-post/blog-new.php', 'qw_blog_post_new', 'QW New Blog');
qa_register_plugin_module('page', 'addons/blog-post/blogs.php', 'qw_blogs', 'QW Blogs');
qa_register_plugin_module('page', 'addons/blog-post/blog.php', 'qw_blog', 'QW Blog');

class Qw_Blog_Post_Addon {

      function __construct() {
            qw_event_hook('doctype', NULL, array($this, 'navigation'));
            qw_event_hook('register_language', NULL, array($this, 'language'));
            qw_event_hook('enqueue_css', NULL, array($this, 'css'));
            qw_event_hook('enqueue_scripts', NULL, array($this, 'script'));
            qw_add_action('qw_theme_option_tab', array($this, 'option_tab'));
            qw_add_action('qw_theme_option_tab_content', array($this, 'option_tab_content'));
            qw_add_action('qw_reset_theme_options', array($this, 'reset_theme_options'));
            
			qw_add_action('blog_post_form', array($this, 'blog_post_form'));
      }

      public function language($lang_arr) {
		    $lang_arr['qw_blog_post'] = QW_CONTROL_DIR .'/addons/blog-post/language-*.php';
		    return $lang_arr;
      }
      public function css($css_src) {
            $css_src['qw_blog_post'] = QW_CONTROL_URL . '/addons/blog-post/styles.css';
            return $css_src;
      }
      
      public function script($script_src) {
            $script_src['qw_blog_post'] = QW_CONTROL_URL . '/addons/blog-post/script.js';
            // $script_src['qw_blog_post_facebook'] = "http://connect.facebook.net/en_US/all.js";
            return $script_src;
      }
      public function navigation($themeclass) {
          $themeclass['navigation']['main']['blog']=array(
              'url' => qa_path_absolute("blogs") ,
              'label' => "Blogs" ,
          );
          if (qa_request_part(0)=='blogs' || qa_request_part(0)=='blog') {
             $themeclass['navigation']['main']['blog']['selected'] = true ;
          }
          return $themeclass;
      }

      public function reset_theme_options() {
            if (qa_clicked('qw_reset_button')) {
              
            }
      }

      function option_tab(){
          $saved=false;
          if(qa_clicked('qw_save_button')){   
             
              $saved=true;
            }
          
          return '<li>
              <a href="#" data-toggle=".qa-part-form-blog-post">Blog Post</a>
            </li>';
    }
    function option_tab_content(){
          $output = '<div class="qa-part-form-blog-post">
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
            return $output;
    }
	
	function blog_post_form(){
		return '
			<div class="form-group">
				<label for="title">Title</label>
				<input type="text" class="form-control" id="title" placeholder="Post title">
			</div>
			<div class="form-group">
				<label for="content">Content</label>
				<textarea type="text" class="form-control" id="content" placeholder="Post content" rows="10"></textarea>
			</div>		
		';
	}


} //class

$qw_blog_post_addon = new Qw_Blog_Post_Addon;
