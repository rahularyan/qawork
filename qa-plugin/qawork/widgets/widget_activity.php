<?php
	class qw_activity_widget {

		function qw_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'qw_sa_count' => array(
						'label' => 'Numbers of Questions',
						'type' => 'number',
						'tags' => 'name="qw_sa_count" class="form-control"',
						'value' => '10',
					),
				),

			);
		}

		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
				case 'activity':
				case 'qa':
				case 'questions':
				case 'hot':
				case 'ask':
				case 'categories':
				case 'question':
				case 'tag':
				case 'tags':
				case 'unanswered':
				case 'user':
				case 'users':
				case 'search':
				case 'admin':
				case 'custom':
					$allow=true;
					break;
			}
			
			return $allow;
		}

		
		function allow_region($region)
		{
			$allow=false;
			
			switch ($region)
			{
				case 'main':
				case 'side':
				case 'full':
					$allow=true;
					break;
			}
			
			return $allow;
		}
	
		function qw_events($limit =10, $events_type = false){
			if(!$events_type)
				$events_type = array('q_post', 'a_post', 'c_post', 'a_select', 'badge_awarded');
			
			// query last 3 events
			$events = qa_db_read_all_assoc(qa_db_query_sub('SELECT UNIX_TIMESTAMP(datetime) as unix_time , datetime , ^eventlog.*, ^users.* FROM ^eventlog, ^users WHERE ^eventlog.handle=^users.handle AND ^eventlog.event IN ("q_post", "a_post", "c_post", "a_select", "u_register", "q_edit", "c_edit", "a_edit") ORDER BY ^eventlog.datetime DESC LIMIT #', $limit));
			
	
			if(empty($events))return;
			$postids = array();
			foreach($events as $post){
				$params = qw_event_log_row_parser($post['params']);
				
				if(isset($params['postid']))
					$postids[] = $params['postid'];
			}
			
			$postids = implode(',', $postids);
			
			if(!empty($postids))
				$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT UNIX_TIMESTAMP(^posts.created) as unix_created, ^posts.* FROM ^posts, ^users WHERE  ^posts.postid IN ('.$postids.') AND ^posts.type IN ("Q", "A", "C") ORDER BY ^posts.created DESC'), 'postid');
			
			$o ='';
			$user_handles = '' ; 
			$profile_pics = '' ;
			$new_registered_users = 0 ;
			$date_format = "Y-m-d H:i:s"; 
			$latest = date_create_from_format($date_format, "1800-01-01 01:00:00" ); /*very low value */
			if(isset($events)){
				$o .= '<ul class="ra-activity">';
				// first deal with all the newly joined users 
				$count = 0 ;
				foreach ($events as $p) {
					switch ($p['event']) {
						case 'u_register':
							
							$username      = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : htmlspecialchars($p['handle']);
							$usernameLink  = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : '<a href="'.qa_path_html('user/'.$p['handle']).'">'.$p['handle'].'</a>';
							$timeCode      = qa_when_to_html( $p['unix_time'] , 7);
							// $time          = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
							$user_handles .= ((!$user_handles) ? "" : ", " ).'<p class="title inline"><strong class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.@$usernameLink.'</strong>';
							$profile_pics .= '<div class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.qw_get_post_avatar($p, 20, false).'</div>';
							unset($events[$count]) ;
							$new_registered_users++ ;
							
							$current = date_create_from_format($date_format, $p['datetime'] );
							if ($current > $latest) {
								$time= @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
								$latest = $current ;
							}
							break;
					}
					$count++ ;
				}
				if ($new_registered_users > 0 && !!$user_handles && !!$profile_pics ) {
					$prefix =  ($new_registered_users > 1) ? qa_lang('cleanstrap/have') : qa_lang('cleanstrap/has') ;
					$event_name = $prefix . qa_lang('cleanstrap/joined_community');
					$event_icon = 'icon-user-add';					
					$registered_users  = '<li class="activity-item">';
					$registered_users .= '<div class="activity-inner">';	
					$registered_users .= '<div class="activity-icon pull-left '.$event_icon.'"></div>';
					$registered_users .= '<div class="activity-content">';			
					$registered_users .= $user_handles . ' <span class="what">'.$event_name.'</span> <span class="time">'.@$time.'</span></p>';
					$registered_users .= '<div class="activity-detail">';
					$registered_users .= $profile_pics;
					$registered_users .= '<span class="activity-title" href="#">'.'</span>';
					$registered_users .= '</div>';	
					$registered_users .= '</div>';	
					$registered_users .= '</div>';	
					$registered_users .= '</li>';
				}

				foreach($events as $p){
					$event_name = '';
					$event_icon = '';
					$title = '';
					
					$params = qw_event_log_row_parser($p['params']);
					
					if (!isset($params['postid']) || !isset($posts[$params['postid']]))  {
						// some times the posts get deleted , then it should not show an error 
						continue ;
					}

					switch ($p['event']) {
						case 'q_post':
							$event_name = qa_lang('cleanstrap/asked');
							$event_icon = 'icon-question';
							break;
						case 'a_post':
							$event_name = qa_lang('cleanstrap/answered');
							$event_icon = 'icon-answer';
							break;
						case 'c_post':
							$event_name = qa_lang('cleanstrap/commented');
							$event_icon = 'icon-comment';	
							break;
						case 'u_register':
							$event_name = qa_lang('cleanstrap/joined_community');
							$event_icon = 'icon-user-add';					
							$title = qa_lang_sub('cleanstrap/x_just_registered_in_our_community', $p['handle']);					
							break;
						case 'q_edit':
							$event_name = qa_lang('cleanstrap/edited_comment');
							$event_icon = 'icon-question';					
							break;
						case 'a_edit':
							$event_name = qa_lang('cleanstrap/edited_answer');
							$event_icon = 'icon-answer';						
							break;
						case 'c_edit':
							$event_name = qa_lang('cleanstrap/edited_comment');
							$event_icon = 'icon-comment';							
							break;
						case 'a_select':
							$event_name = qa_lang('cleanstrap/selected_an_answer');
							$event_icon = 'icon-tick';
							$title = qa_lang_sub('cleanstrap/x_awarded_an_answer', $p['handle']);						
							break;
						
						default:
							break;
					}

					$username = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : htmlspecialchars($p['handle']);
					
					$usernameLink = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : '<a href="'.qa_path_html('user/'.$p['handle']).'">'.$p['handle'].'</a>';
					
					$timeCode = qa_when_to_html( $p['unix_time'] , 7);

					$time = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
					$current = date_create_from_format($date_format, $p['datetime'] );
					if (isset($latest) && $current < $latest) {
						if (!!$registered_users) {
							$o .= $registered_users ;
						}
						unset($latest);
					}
					$o .= '<li class="activity-item">';
					$o .= '<div class="activity-inner">';	
					
					$o .= '<div class="activity-icon pull-left '.$event_icon.'"></div>';
						
					$o .= '<div class="activity-content">';			
					$o .= '<p class="title"><strong class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.@$usernameLink.'</strong> <span class="what">'.$event_name.'</span> <span class="time">'.$time.'</span></p>';
					
					$o .= '<div class="activity-detail">';
					
					$o .= '<div class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.qw_get_post_avatar($p, 20, false).'</div>';

					if($p['event'] == 'q_post' || $p['event'] == 'a_post' || $p['event'] == 'c_post'|| $p['event'] == 'q_edit' || $p['event'] == 'a_edit' || $p['event'] == 'c_edit'){
						$main_post = $posts[$params['postid']];
						
						if ($p['event'] == 'q_post' || $p['event'] == 'q_edit') {
							$o .= '<a class="activity-title" href="' . qa_q_path_html($main_post['postid'], $main_post['title']) . '" title="' . $main_post['title'] . '">' . qw_truncate($main_post['title'],100) . '</a>';
						} elseif ($p['event'] == 'a_post' || $p['event'] == 'a_edit') {
							$o .= '<a class="activity-title" href="' . qa_q_path($main_post['parentid'], $main_post['title'], false, 'C', $main_post['postid']). '">' . qw_truncate(strip_tags($main_post['content']),100) . '</a>';
						} elseif($p['event'] == 'c_post' || $p['event'] == 'c_edit') {
							$o .= '<a class="activity-title" href="' . qa_q_path($main_post['parentid'], $main_post['title'], false, 'C', $main_post['postid']) . '">' . qw_truncate(strip_tags($main_post['content']),100) . '</a>';
						}
					}else{
					
						$o .= '<span class="activity-title" href="#">' . $title . '</span>';
					}
				
					$o .= '</div>';	
					
					$o .= '</div>';	
					$o .= '</div>';	
					$o .= '</li>';
				}
				$o .= '</ul>';
			}
			
			return $o;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{

			if(!(qa_opt('event_logger_to_database'))){
				$themeobject->output('<p>Eventlogger is disabled</p>');
			}
			$widget_opt = @$themeobject->current_widget['param']['options'];

			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">Site Activity</h3>');
				
			$themeobject->output('<div class="ra-sa-widget">');
			$themeobject->output($this->qw_events((int)$widget_opt['qw_sa_count']));
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/