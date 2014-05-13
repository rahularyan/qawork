<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class cs_social_login_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{		
		if ($request=='logins')
			return true;

		return false;
	}
	
	function process_request($request)
	{
		$start=qa_get_start();
		$userid=qa_get_logged_in_userid();
		//	Prepare content for theme

		$qa_content=qa_content_prepare();
		
		$qa_content['site_title']=qa_lang_html('cs_social_login/my_logins_title');
		$qa_content['title']=qa_lang_html('cs_social_login/my_logins_title');
		
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		require_once CS_CONTROL_DIR.'/addons/social-login/cs-social-login-utils.php';

		if (QA_FINAL_EXTERNAL_USERS)
			qa_fatal_error('User accounts are handled by external code');
		
		$userid=qa_get_logged_in_userid();
		
		if (!isset($userid))
			qa_redirect('login');

		//	Get current information on user
		$useraccount = cs_social_user_find_by_id($userid);
		
		$findemail = $useraccount['oemail']; // considering this is an openid user, so use the openid email
		if(empty($findemail)) {
			$findemail = $useraccount['email']; // fallback
		}
		
		// find other un-linked accounts with the same email
		$otherlogins = cs_social_user_login_find_other($userid, $findemail);
			
		if (qa_clicked('dosaveprofile')) {
			qa_opt('open_login_remember', qa_post_text('remember') ? '1' : '0');
			qa_redirect('logins', array('state' => 'profile-saved'));
		}
		
		if (qa_clicked('docancel')) {
			$conf = qa_post_text('confirm');
			$tourl = qa_post_text('to');
			if(!empty($tourl)) {
				qa_redirect($tourl);
			} else {
				qa_redirect($conf ? '' : 'logins'); // redirect to homepage or logins page
			}
		}
		
		if (qa_clicked('domerge') && !empty($otherlogins)) {
			// a request to merge (link) multiple accounts was made
			require_once QA_INCLUDE_DIR.'qa-app-users-edit.php';
			$recompute = false;
			$email = null;
			
			// see which account was selected, if any
			foreach($otherlogins as $login) {
				// see if this openid login was checked for merge
				$key = "login_{$login['source']}_" . md5($login['identifier']);
				$value = qa_post_text($key);
				if(!empty($value)) {
					// ok, we need to merge this one and delete the old user
					$olduserid = $login['userid'];
					
					// update login
					qa_db_user_login_sync(true);
					cs_social_user_login_set($login['source'], $login['identifier'], 'userid', $userid);
					qa_db_user_login_sync(false);
					
					// delete old user if no other connections to it exist
					$other_logins_for_user = cs_social_user_login_find_mine($olduserid, cs_social_login_get_new_source($login['source'], $login['identifier']));
					if(empty($other_logins_for_user)) {
						// safe to delete user profile
						qa_delete_user($olduserid);
						$recompute = true;
						if(empty($email)) $email = $login['email'];
						if(empty($email)) $email = $login['oemail'];
						if(empty($email)) $email = $login['uloemail'];
					}
					
				} else {
					// see if a regular QA user was checked for merge
					$key = "user_{$login['userid']}_" . md5($login['userid']);
					$value = qa_post_text($key);
					if(!empty($value)) {
						// we'll simply delete the selected user
						qa_delete_user($login['userid']);
						$recompute = true;
						if(empty($email)) $email = $login['email'];
						if(empty($email)) $email = $login['oemail'];
						if(empty($email)) $email = $login['uloemail'];
					}
				}
			}
			
			// recompute the stats, if needed
			if($recompute) {
				require_once QA_INCLUDE_DIR.'qa-db-points.php';
				qa_db_userpointscount_update();
				
				// also check the email address on the remaining user account
				if(empty($useraccount['email']) && !empty($email)) {
					// update the account if the email address is not used anymore
					$emailusers=qa_db_user_find_by_email($email);
					if (count($emailusers) == 0) {
						qa_db_user_set($userid, 'email', $email);
						$useraccount['email'] = $email; // to show on the page
					}
				}
			}
			
			$conf = qa_post_text('confirm');
			$tourl = qa_post_text('to');
			if(!empty($tourl)) {
				qa_redirect($tourl);
			} else {
				qa_redirect($conf ? '' : 'logins'); // redirect to homepage or logins page
			}
			
			// update the array
			$otherlogins = cs_social_user_login_find_other($userid, $findemail);
			
		}
		//	Get more information on user, including accounts already linked 
		$mylogins = cs_social_user_login_find_mine($userid, $useraccount['sessionsource']);
		
		if (qa_clicked('dosplit') && !empty($mylogins)) {
			// a request to split (un-link) some accounts was made
			foreach($mylogins as $login) {
				// see which account was selected, if any
				$key = "login_{$login['source']}_" . md5($login['identifier']);
				$value = qa_post_text($key);
				if(!empty($value)) {
					// ok, we need to delete this one
					$olduserid = $login['userid'];
					
					// delete login
					qa_db_user_login_sync(true);
					cs_social_user_login_delete($login['source'], $login['identifier'], $userid);
					qa_db_user_login_sync(false);
				}
			}
			
			// update the array
			$mylogins = cs_social_user_login_find_mine($userid, $useraccount['sessionsource']);
		}

		

		//	Prepare content for theme
		$qa_content=qa_content_prepare();
		$qa_content['title']=qa_lang_html('cs_social_login/my_logins_title');
		
		$disp_conf = qa_get('confirm');
		if(!$disp_conf) {
			// display some summary about the user
			$qa_content['form_profile']=array(
				'title' => qa_lang_html('cs_social_login/my_current_user'),
				'tags'  => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="'.qa_self_html().'" CLASS="open-login-profile"',
				'style' => 'wide',
				'fields' => array(
					'handle' => array(
						'label' => qa_lang_html('users/handle_label'),
						'value' => qa_html($useraccount['handle']),
						'type'  => 'static',
					),
					
					'email' => array(
						'label' => qa_lang_html('users/email_label'),
						'value' => qa_html($useraccount['email']),
						'type'  => 'static',
					),
					
					'remember' => array(
						'type'  => 'checkbox',
						'label' => qa_lang_html('users/remember_label'),
						'note'  => qa_lang_html('cs_social_login/remember_me'),
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
					'dosaveprofile' => '1'
				),

			);
			
			if (qa_get_state()=='profile-saved') {
				$qa_content['form_profile']['ok']=qa_lang_html('users/profile_saved');
			}
			
			$has_content = false;
			if(!empty($mylogins)) {
				// display the logins already linked to this user account
				$qa_content['form_mylogins']=array(
					'title'   => qa_lang_html('cs_social_login/associated_logins'),
					'tags'    => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="'.qa_self_html().'" CLASS="open-login-accounts"',
					'style'   => 'wide',
					'fields'  => array(),
					'buttons' => array(
						'cancel' => array(
							'tags'  => 'onClick="qa_show_waiting_after(this, false);"',
							'label' => qa_lang_html('cs_social_login/split_accounts'),
							'note'  => '<small>' . qa_lang_html('cs_social_login/split_accounts_note') . '</small>',
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
			}
		}
		
		
		if(!empty($otherlogins)) {
			// display other logins which could be linked to this user account
			$qa_content['form_merge']=array(
				'title'   => $disp_conf ? qa_lang_html('cs_social_login/other_logins_conf_title') : qa_lang_html('cs_social_login/other_logins'),
				'tags'    => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="'.qa_self_html().'" CLASS="open-login-others"',
				'style'   => 'wide',
				'note'    => $disp_conf ? qa_lang_html('cs_social_login/other_logins_conf_text'): null,
				'fields'  => array(),
				'buttons' => array(
						'save' => array(
							'tags'  => 'onClick="qa_show_waiting_after(this, false);"',
							'label' => qa_lang_html('cs_social_login/merge_accounts'),
						),
				),
				'hidden' => array(
					'domerge' => '1',
					'confirm' => $disp_conf,
					'to'      => qa_get('to'),
				),
			);
			
			$data = array(); 
			foreach($otherlogins as $i => $login) {
				$type = 'login';
				$name = ucfirst($login['source']);
				$email = $login['uloemail'] ? '(' . qa_html($login['uloemail']) . ')' : '';
				
				if(!$login['source']) { // this is a regular site login, not an openid login
					$type  = 'user';
					$name  = qa_lang_html('cs_social_login/local_user');
					$email = '(' . $login['handle'] . ')';
					$login['source']     = $login['userid'];
					$login['identifier'] = $login['userid'];
				}
				
				$data["f$i"] = array(
					'label' => '<strong>' . $name . '</strong> ' . $email,
					'tags'  => 'NAME="' . $type . '_' . $login['source'] . '_' . md5($login['identifier']) . '"',
					'type'  => 'checkbox',
					'style' => 'tall'
				);
			}
			$qa_content['form_merge']['fields'] = $data;
			$has_content = true;
			
			// add a note to the Save button
			if($disp_conf) { 
				// confirmations are displayed only after logging in
				$qa_content['form_merge']['buttons']['cancel'] = array(
					'tags'  => 'NAME="docancel"',
					'label' => qa_lang_html('main/cancel_button'),
					'note'  => '<small>' . qa_lang_html('cs_social_login/merge_accounts_note') . '</small>',
				);
			} else {
				// when accessing the logins page, no confirmation is displayed
				$qa_content['form_merge']['buttons']['save']['note'] = 
					'<small>' . qa_lang_html('cs_social_login/merge_accounts_note') . '</small>';
			}
			
		} else if($disp_conf) {
			qa_redirect(qa_get('to'));
		}
		
		if(!$has_content) {
			// no linked logins
			$qa_content['form_nodata']=array(
				'title' => '<br>' . qa_lang_html('cs_social_login/no_logins_title'),
				'style' => 'light',
				'fields' => array(
					'note' => array(
						'note' => qa_lang_html('cs_social_login/no_logins_text'),
						'type' => 'static'
					)
				),
			);
		}
		
		$qa_content['navigation']['sub']=qa_account_sub_navigation();

		// set some extra subnavigations 
		$qa_content['navigation']['sub']['logins'] = array(
														'label' => qa_lang_html('cs_social_login/my_logins_title'),
														'url'   => './logins' ,
													);
		
		return $qa_content;	
	}
	
	function page_content($qa_content){
		ob_start();
		?>
			<div id="cs-my-logins">				
				
			</div>
		<?php
		$output = ob_get_clean();
		
		return $output;
	}

	
	
}

