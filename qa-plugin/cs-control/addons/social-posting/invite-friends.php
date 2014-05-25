<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

class cs_social_invite_friends_page {

      var $directory;
      var $urltoroot;
      var $page_url = 'invite-friends' ;

      function load_module($directory, $urltoroot) {
            $this->directory = $directory;
            $this->urltoroot = $urltoroot;
      }

      function match_request($request) {
            if ($request == $this->page_url) return true;

            return false;
      }

      function process_request($request) {
            if (QA_FINAL_EXTERNAL_USERS) qa_fatal_error('User accounts are handled by external code');

            $userid = qa_get_logged_in_userid();

            if (!isset($userid))      //if not logged in then redirect to login page
                  qa_redirect('login');

            require_once CS_CONTROL_DIR . '/addons/social-posting/cs-social-posting-utils.php';
            $start = qa_get_start();
            $userid = qa_get_logged_in_userid();
            $action = null;
            $key = null;

            //	Prepare content for theme

            $qa_content = qa_content_prepare();

            $qa_content['site_title'] = qa_lang_html('cs_social_posting/invite_frnds');
            $qa_content['title'] = qa_lang_html('cs_social_posting/invite_frnds');

            $qa_content['navigation']['sub'] = qa_account_sub_navigation();

            $disp_conf = qa_get('confirm');
            if (!$disp_conf) {
                  $name = qa_get_logged_in_user_field('name');
                  $name = (!!$name) ? $name : qa_get_logged_in_handle();
                  // display some summary about the user
                  $qa_content['form_facebook_invite'] = array(
                      'title' => qa_lang_html('cs_social_posting/invite_frnds'),
                      'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '" CLASS="open-login-profile" onsubmit="return false ;"',
                      'style' => 'wide',
                      'buttons' => array(
                          'facebook_invite' => array(
                              'tags' => 'name="facebook_invite" onClick="'.cs_generate_facebook_invite_script(qa_opt("facebook_app_id"), array('name' => $name, 'url' => qa_opt("site_url"))).'"',
                              'label' => qa_lang_html('cs_social_posting/send_facebook_invite'),
                              // 'note' => cs_generate_facebook_invite_script(qa_opt("facebook_app_id"), $name, qa_opt("site_url"))
                          ),

                          'facebook_status_update' => array(
                              'tags' => 'name="facebook_sts_updt" onClick="'.cs_generate_facebook_wall_post_script(qa_opt("facebook_app_id"), array('name' => $name, 'picture' => "http://amiyasahu.com/assets/img/amiya.jpg" , 'link' => "http://amiyasahu.com" , 'caption'=>"Amiya Sahu" , 'description' => "Web Application Developer and Designer (Updated from my application )")).'"',
                              'label' => qa_lang_html('cs_social_posting/update_facebook_status'),
                          ),
                          
                          'facebook_link_share' => array(
                              'tags' => 'name="facebook_link_share" onClick="'.cs_generate_facebook_link_share_script(qa_opt("facebook_app_id"), array('to' => "sachi059",'message' =>"please check out this link" , 'link' => "http://stackoverflow.com/questions/10415884/fb-init-has-already-been-called")).'"',
                              'label' => "Facebook link share ",
                          ),
                          
                          'facebook_login' => array(
                              'tags' => 'name="facebook_login" onClick="'.cs_generate_facebook_login_script(qa_opt("facebook_app_id")).'"',
                              'label' => "Facebook Login ",
                          ),

                      ),
                  );

            }

            return $qa_content;
      }

      function page_content($qa_content) {
            
      }

}
