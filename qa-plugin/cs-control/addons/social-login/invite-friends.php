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
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		if ($request=='invite-friends')
			return true;

		return false;
	}
	
	function process_request($request)	{
		if (QA_FINAL_EXTERNAL_USERS)
			qa_fatal_error('User accounts are handled by external code');
		
		$userid=qa_get_logged_in_userid();
		
		if (!isset($userid))      //if not logged in then redirect to login page
			qa_redirect('login');

		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		require_once CS_CONTROL_DIR.'/addons/social-login/cs-social-login-utils.php';
		$start=qa_get_start();
		$userid=qa_get_logged_in_userid();
		$action = null;
            $key = null;

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
$provider = 'facebook';
            /*if ($key == null || strcasecmp($key, $provider) != 0) {
                  return false;
            }*/
		// Now process if the invite button is clicked 

		if (qa_clicked('doinvite') || $action == 'login') {
			// $provider = qa_post_text('provider');
			
			require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
            require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Endpoint.php';
                  $loginCallback = qa_path('', array(), qa_opt('site_url')."invite-friends");
            // prepare the configuration of HybridAuth
                  $config = cs_social_get_config_common($loginCallback , 'facebook');

            	// init hybridauth
			  $hybridauth = new Hybrid_Auth( $config );
			 
			  // try to authenticate with twitter
			  $adapter = $hybridauth->authenticate( "Facebook" );
			 
			  // grab the user's friends list
			  $user_contacts = $adapter->getUserContacts();
			 
			  // iterate over the user friends list
			  foreach( $user_contacts as $contact ){
			     echo $contact->displayName . " " . $contact->profileURL . "<hr />";
			  }
		}
		if ($action == 'process') {
                  require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
                  require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Endpoint.php';
                  Hybrid_Endpoint::process();
            }
		//	Prepare content for theme

		$qa_content=qa_content_prepare();
		
		$qa_content['site_title']='Invite Your Friends ';
		$qa_content['title']='Invite Your Friends ';
		
		
		$qa_content['navigation']['sub']=qa_account_sub_navigation();

		$disp_conf = qa_get('confirm');
		if(!$disp_conf) {

			// display some summary about the user
			$qa_content['form_profile']=array(
				'title' => /*qa_lang_html('cleanstrap/my_current_user')*/ 'Invite Friends ',
				'tags'  => 'METHOD="POST" ACTION="'.qa_self_html().'" CLASS="open-login-profile"',
				'style' => 'wide',
				'fields' => array(
					
					'provider' => array(
						'type'  => 'checkbox',
						'label' => qa_lang_html('users/remember_label'),
						'note'  => qa_lang_html('cleanstrap/remember_me'),
						'tags'  => 'NAME="remember"',
						'value' => qa_opt('open_login_remember') ? true : false,
					),
				),
				
				'buttons' => array(
					'save' => array(
						'tags'  => 'onClick="qa_show_waiting_after(this, false);"',
						'label' => qa_lang_html('users/save_profile'),
					),
				),
				
				'hidden' => array(
					'doinvite' => '1'
				),

			);
			
			/*
			
			$has_content = false;
			if(!empty($mylogins)) {
				// display the logins already linked to this user account
				$qa_content['form_mylogins']=array(
					'title'   => qa_lang_html('cleanstrap/associated_logins'),
					'tags'    => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="'.qa_self_html().'" CLASS="open-login-accounts"',
					'style'   => 'wide',
					'fields'  => array(),
					'buttons' => array(
						'cancel' => array(
							'tags'  => 'onClick="qa_show_waiting_after(this, false);"',
							'label' => qa_lang_html('cleanstrap/split_accounts'),
							'note'  => '<small>' . qa_lang_html('cleanstrap/split_accounts_note') . '</small>',
						),
					),
					'hidden' => array(
						'dosplit' => '1',
					),
				);
				
				$data = array();
				foreach($mylogins as $i => $login) {
					$email = $login['oemail'] ? '(' . qa_html($login['oemail']) . ')' : '';
					$data["f$i"] = array(
						'label' => '<strong>' . ucfirst($login['source']) . '</strong> ' . $email,
						'tags'  => 'NAME="login_' . $login['source'] . '_' . md5($login['identifier']) . '"',
						'type'  => 'checkbox',
						'style' => 'tall'
					);
				}
				$qa_content['form_mylogins']['fields'] = $data;
				$has_content = true;
			}*/
		
		}

		return $qa_content ;
	}
	
	function page_content($qa_content){

	}
	
}

