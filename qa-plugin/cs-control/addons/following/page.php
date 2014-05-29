<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class cs_following_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		$parts=explode('/', $request);
		if (qa_is_logged_in() && $parts[0]=='following')
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
		$count=cs_count_following($userid, true);
		$pagesize= 10; //qa_opt('page_size_tags');
			
		$qa_content=qa_content_prepare();		
		$qa_content['site_title']= qa_lang_sub('cleanstrap/users_being_followed_by_x', $handle);
		$qa_content['error']="";
		$qa_content['title']= qa_lang_sub('cleanstrap/users_being_followed_by_x', $handle);
		
		$followers = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^userfavorites, ^users, ^userpoints  WHERE ^userfavorites.entityid = ^users.userid and ^userfavorites.entityid = ^userpoints.userid and ^userfavorites.userid = # and ^userfavorites.entitytype = "U" LIMIT #,#', $userid, $start, $count));
		
		$qa_content['custom']= $this->followers($followers, $handle, $userid);
		
		$qa_content['page_links']=qa_html_page_links(qa_request(), $start, $pagesize, $count, qa_opt('pages_prev_next'));
		
		qa_set_template('followers');
		
		return $qa_content;	
	}
	
	function followers($followers, $handle, $userid){
		ob_start();
			
			echo '<div class="page-users-list clearfix ">';
			foreach($followers as $f){
				$avatar = cs_get_post_avatar($f, 100, false);

				echo '
					<div class="user-box">
					<div class="user-box-inner">	
						<div class="box-container">
							<div class="user-avatar">
								'.$avatar.'
							</div>
							
							<a class="user-name" href="' . qa_path_html('user/' . $f['handle']) . '">' . $f['handle']. '</a>								
							<span class="score">' .  qa_lang_sub('cleanstrap/x_points', $f['points']) . ' </span>
					</div>';
					if (qa_opt('badge_active') && function_exists('qa_get_badge_list'))
						echo '<div class="badge-list">' . cs_user_badge($handle) . '</div>';
					
				echo '</div>';
				echo '</div>';
			
			}
			echo '</div>';
	
		return ob_get_clean();
	}

	
	
}

