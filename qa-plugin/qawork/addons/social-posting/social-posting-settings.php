<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_social_posting_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{		
		if ($request=='social-posting')
			return true;

		return false;
	}
	
	function process_request($request)
	{
		$start=qa_get_start();
		$userid=qa_get_logged_in_userid();
		//	Prepare content for theme
		
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		require_once QW_CONTROL_DIR.'/addons/social-login/cs-social-login-utils.php';

		if (QA_FINAL_EXTERNAL_USERS)
			qa_fatal_error('User accounts are handled by external code');
		
		$userid=qa_get_logged_in_userid();
		
		if (!isset($userid))
			qa_redirect('login');
		if (!(qa_opt('qw_enable_fb_posting') || qa_opt('qw_enable_twitter_posting')))
			qa_redirect_raw(qa_opt('site_url'));

		
		$qa_content=qa_content_prepare();
		$qa_content['title']=qa_lang_html('qw_social_posting/social_posting_title');
		$qa_content['site_title']=qa_lang_html('qw_social_posting/social_posting_title');
    	
		if (qa_clicked('dosave_settings')) {
			$data_to_save = array(
					'qw_facebook_q_post' => !!qa_post_text('qw_facebook_q_post') ,
					'qw_facebook_a_post' => !!qa_post_text('qw_facebook_a_post') ,
					'qw_facebook_c_post' => !!qa_post_text('qw_facebook_c_post') ,
					'qw_twitter_q_post' => !!qa_post_text('qw_twitter_q_post') ,
					'qw_twitter_a_post' => !!qa_post_text('qw_twitter_a_post') ,
					'qw_twitter_c_post' => !!qa_post_text('qw_twitter_c_post') ,
				);
			qw_save_social_posting_settings($data_to_save , $userid);
			qa_redirect('social-posting', array('state' => 'settings-saved'));
		}

		$disp_conf = qa_get('confirm');
		$all_keys = array('qw_facebook_q_post','qw_facebook_a_post','qw_facebook_c_post','qw_twitter_q_post','qw_twitter_a_post','qw_twitter_c_post',);
		
		$preferences = qw_get_social_posting_settings($all_keys , $userid);

		if(!$disp_conf) {
			// display some summary about the user
			$qa_content['form_profile']=array(
				'title' => qa_lang_html('qw_social_posting/my_social_posting_settings'),
				'tags'  => 'METHOD="POST" ACTION="'.qa_self_html().'" CLASS="social-login-settings"',
				'style' => 'wide',
				'buttons' => array(
					'save' => array(
						'tags'  => 'onClick="qa_show_waiting_after(this, false);"',
						'label' => qa_lang_html('qw_social_posting/save_settings'),
					),
				),
				
				'hidden' => array(
					'dosave_settings' => '1'
				),

			);

			if (qa_opt('qw_enable_fb_posting')) {
				$qa_content['form_profile']['fields']['qw_facebook_q_post'] = array(
						'type'  => 'checkbox',
						'label' => qa_lang_html('qw_social_posting/qw_facebook_q_post_lable'),
						'tags'  => 'NAME="qw_facebook_q_post"',
						'value' => @$preferences['qw_facebook_q_post'] ? true : false,
				);
				$qa_content['form_profile']['fields']['qw_facebook_a_post'] = array(
					'type'  => 'checkbox',
					'label' => qa_lang_html('qw_social_posting/qw_facebook_a_post_lable'),
					'tags'  => 'NAME="qw_facebook_a_post"',
					'value' => @$preferences['qw_facebook_a_post'] ? true : false,
				);
				$qa_content['form_profile']['fields']['qw_facebook_c_post'] = array(
					'type'  => 'checkbox',
					'label' => qa_lang_html('qw_social_posting/qw_facebook_c_post_lable'),
					'tags'  => 'NAME="qw_facebook_c_post"',
					'value' => @$preferences['qw_facebook_c_post'] ? true : false,
				);
			}
			if (qa_opt('qw_enable_twitter_posting')) {
				$qa_content['form_profile']['fields']['qw_twitter_q_post'] = array(
						'type'  => 'checkbox',
						'label' => qa_lang_html('qw_social_posting/qw_twitter_q_post_lable'),
						'tags'  => 'NAME="qw_twitter_q_post"',
						'value' => @$preferences['qw_twitter_q_post'] ? true : false,
				);
				$qa_content['form_profile']['fields']['qw_twitter_a_post'] = array(
					'type'  => 'checkbox',
					'label' => qa_lang_html('qw_social_posting/qw_twitter_a_post_lable'),
					'tags'  => 'NAME="qw_twitter_a_post"',
					'value' => @$preferences['qw_twitter_a_post'] ? true : false,
				);
				$qa_content['form_profile']['fields']['qw_twitter_c_post'] = array(
					'type'  => 'checkbox',
					'label' => qa_lang_html('qw_social_posting/qw_twitter_c_post_lable'),
					'tags'  => 'NAME="qw_twitter_c_post"',
					'value' => @$preferences['qw_twitter_c_post'] ? true : false,
				);
			}

			if (qa_get_state()=='settings-saved') {
				$qa_content['form_profile']['ok']=qa_lang_html('qw_social_posting/settings_saved');
			}
			
		}
		
		$qa_content['navigation']['sub']=qa_account_sub_navigation();

		// set some extra subnavigations 
		$qa_content['navigation']['sub']['logins'] = array(
														'label' => qa_lang_html('qw_social_login/my_logins_title'),
														'url'   => './logins' ,
													);
		$qa_content['navigation']['sub']['social-posting'] = array(
														'label' => qa_lang_html('qw_social_posting/my_social_posting_nav'),
														'url'   => './social-posting' ,
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

