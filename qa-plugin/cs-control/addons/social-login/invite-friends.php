<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

class cs_social_invite_friends_page {

      var $directory;
      var $urltoroot;

      function load_module($directory, $urltoroot) {
            $this->directory = $directory;
            $this->urltoroot = $urltoroot;
      }

      function match_request($request) {
            if ($request == 'invite-friends') return true;

            return false;
      }

      function process_request($request) {
            if (QA_FINAL_EXTERNAL_USERS) qa_fatal_error('User accounts are handled by external code');

            $userid = qa_get_logged_in_userid();

            if (!isset($userid))      //if not logged in then redirect to login page
                  qa_redirect('login');

            // get the provider information from the click event 
            if (qa_clicked('facebook_sts_updt')) {
                  $provider = "facebook";
            }else if (qa_clicked('twitter_sts_updt')) {
                  $provider = "twitter";
            }

            require_once QA_INCLUDE_DIR . 'qa-db-users.php';
            require_once QA_INCLUDE_DIR . 'qa-app-format.php';
            require_once QA_INCLUDE_DIR . 'qa-app-users.php';
            require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
            require_once CS_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';
            $start = qa_get_start();
            $userid = qa_get_logged_in_userid();
            $action = null;
            $key = null;
            $status_updated = false ;

            if (!empty($_GET['hauth_start'])) {
                  $key = trim(strip_tags($_GET['hauth_start']));
                  $action = 'process';
            } else if (!empty($_GET['hauth_done'])) {
                  $key = trim(strip_tags($_GET['hauth_done']));
                  $action = 'process';
            } else if (!empty($_GET['login'])) {
                  $key = trim(strip_tags($_GET['login']));
                  $action = 'login';
            } else if (isset($_GET['fb_source']) && $_GET['fb_source'] == 'appcenter' &&
                    isset($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'], 'www.facebook.com') !== false &&
                    isset($_GET['fb_appcenter']) && $_GET['fb_appcenter'] == '1' && isset($_GET['code'])) {
                  // allow AppCenter users to login directly
                  $key = 'facebook';
                  $action = 'login';
            }
           
            // Now process if the invite button is clicked 

            if (qa_clicked('doinvite') || $action == 'login') {
                  // $provider = qa_post_text('provider');
                  if (!$provider) {
                        $provider = 'facebook'; //the most papular one 
                  }
                  require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
                  require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Endpoint.php';
                  $loginCallback = qa_path('', array(), qa_self_html());
                  try{
                  	  // prepare the configuration of HybridAuth
	                  $config = cs_social_get_config_common($loginCallback, $provider);
	                  if (isset($config)) {
	                        // init hybridauth
	                        $hybridauth = new Hybrid_Auth($config);
	                        // try to authenticate with provider 
	                        $adapter = $hybridauth->authenticate($provider);
	                        //get user profile 
	                        $user_profile = $adapter->getUserProfile();
	                        // grab the user's friends list
	                        $user_contacts = $adapter->getUserContacts();
	                        if (!!$user_profile) {
	                              if (($provider === "twitter" || $provider === "facebook" )&&  !!$user_profile)  {
	                              	// update the user status for twitter account 
	                              	$adapter->setUserStatus( strtr(qa_lang_html("cs_social_login/invite_status") , array('site_url' => qa_opt('site_url'))));
	                              	$status_updated = true ;
	                              	$status_updated_message  = qa_lang_html_sub("cs_social_login/status_updated_message" , $provider );
	                              }
	                        }
	                  }
                  }  catch (Exception $e) {
                        if ($e->getCode() == 6 || $e->getCode() == 7) {
                              $adapter->logout();
                        }

                        $qry = 'provider=' . $this->provider . '&code=' . $e->getCode();
                        if (strstr($topath, '?') === false) {
                              $topath .= '?' . $qry;
                        } else {
                              $topath .= '&' . $qry;
                        }

                        // redirect
                        qa_redirect_raw(qa_opt('site_url') . $topath);
                  }
                  
            }
            if ($action == 'process') {
                  require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
                  require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Endpoint.php';
                  Hybrid_Endpoint::process();
            }


            //	Prepare content for theme

            $qa_content = qa_content_prepare();

            $qa_content['site_title'] = qa_lang_html('cs_social_login/invite_frnds');
            $qa_content['title'] = qa_lang_html('cs_social_login/invite_frnds');


            $qa_content['navigation']['sub'] = qa_account_sub_navigation();

            $disp_conf = qa_get('confirm');
            if (!$disp_conf) {
                  $name = qa_get_logged_in_user_field('name');
                  $name = (!!$name) ? $name : qa_get_logged_in_handle();
                  // display some summary about the user
                  $qa_content['form_facebook_invite'] = array(
                      'title' => qa_lang_html('cs_social_login/invite_frnds'),
                      'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '" CLASS="open-login-profile" onsubmit="return false ;"',
                      'style' => 'wide',
                      'buttons' => array(
                          'facebook_invite' => array(
                              'tags' => 'name="facebook_invite" onClick="invite_friends();"',
                              'label' => qa_lang_html('cs_social_login/send_facebook_invite'),
                              'note' => generate_facebook_invite_script(qa_opt("facebook_app_id"), $name, qa_opt("site_url"))
                          ),
                      ),
                  );

                  $qa_content['form_ststus_update'] = array(
                  	'ok' => ($status_updated) ? $status_updated_message : null,
                      'title' => qa_lang_html('cs_social_login/update_status'),
                      'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '" CLASS="open-login-profile"',
                      'style' => 'wide',
                      'buttons' => array(
                          'facebook_invite' => array(
                              'tags' => 'name="facebook_sts_updt" onClick="qa_show_waiting_after(this, false)"',
                              'label' => qa_lang_html('cs_social_login/update_facebook_status'),
                          ),
                          
                          'twitter_invite' => array(
                              'tags' => 'name="twitter_sts_updt" onClick="qa_show_waiting_after(this, false);"',
                              'label' => qa_lang_html('cs_social_login/update_twitter_status'),
                          ),
                      ),
                      'hidden' => array(
                          'doinvite' => '1',
                      ),
                  );
            }

            return $qa_content;
      }

      function page_content($qa_content) {
            
      }

}
