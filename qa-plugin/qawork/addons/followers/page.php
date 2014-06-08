<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_followers_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		$parts=explode('/', $request);
		if ( $parts[0]=='followers')
			return true;
			

		return false;
	}
	function process_request($request)
	{
		$handle = qa_request_part(1);
		if(empty($handle))
			$handle = qa_get_logged_in_handle();
			
		$userid = qa_handle_to_userid($handle);
		$start=qa_get_start();
		$count=qw_count_followers($userid, true);
		$pagesize= 10; //qa_opt('page_size_tags');
			
		$qa_content=qa_content_prepare();		
		$qa_content['site_title']= qa_lang_html('cleanstrap/followers');
		$qa_content['error']="";
		$qa_content['title']= qa_lang_sub('cleanstrap/user_following_x', $handle);
		
		$followers = qa_db_read_all_assoc(qa_db_query_sub("SELECT ^userfavorites.*, ^users.*, ^userpoints.*, (SELECT CONCAT('{', GROUP_CONCAT( CONCAT( '\"', ^userprofile.title, '\" : \"', ^userprofile.content, '\"')), '}') FROM ^userprofile WHERE ^userprofile.userid = ^users.userid) as profile FROM ^userfavorites, ^users, ^userpoints, ^userprofile WHERE ^userfavorites.userid = ^users.userid and ^userfavorites.userid = ^userpoints.userid AND ^userfavorites.entityid = # and ^userfavorites.entitytype = 'U' LIMIT #,#", $userid, $start, $count));
		
		$qa_content['custom']= $this->followers($followers, $handle, $userid);
		
		$qa_content['page_links']=qa_html_page_links(qa_request(), $start, $pagesize, $count, qa_opt('pages_prev_next'));
		
		qa_set_template('followers');
		
		return $qa_content;	
	}
	
	function followers($followers, $handle, $userid){
		ob_start();
			var_dump($followers);
			echo '<div class="page-users-list clearfix ">';
			foreach($followers as $f){
				$avatar = qw_get_post_avatar($f, 100, false);
				$profile = json_decode($f['profile'], true);
				echo '<div class="user-box">
						<div class="user-box-inner">
							<div class="cover"'.qw_get_user_cover($profile, true, true).'>
								<div class="user-avatar">
									<a href="' . qa_path_html('user/' . $handle) . '" class="avatar">
										<img class="avatar" src="' . $avatar . '" />
									</a>
								</div>
							</div>
							
							<div class="box-container">
								<a class="user-name" href="' . qa_path_html('user/' . $handle) . '">' . $handle. '</a>
								<span class="user-level">'.qa_user_level_string($data['account']['level']).'</span>
								<div class="counts clearfix">
									<p class="score"><strong>'.$data['points']['points'].'</strong>'. qa_lang('cleanstrap/points') . ' </p>
									<p class="followers"><strong>'.$data['followers'].'</strong>' .  qa_lang('cleanstrap/followers') . ' </p>
								</div>
						</div>';
                    if (qa_opt('badge_active') && function_exists('qa_get_badge_list'))
                        echo '<div class="badge-list">' . qw_user_badge($handle) . '</div>';
                    
                    echo '</div>';
                    echo '</div>';
			
			}
			echo '</div>';
	
		return ob_get_clean();
	}

	
	
}

