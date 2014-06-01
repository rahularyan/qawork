<?php
	class cs_activity_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_sa_count' => array(
						'label' => 'Numbers of Questions',
						'type' => 'number',
						'tags' => 'name="cs_sa_count" class="form-control"',
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
	
		function cs_events($limit =10, $events_type = false){
			if(!$events_type)
				$events_type = array('q_post', 'a_post', 'c_post', 'a_select', 'badge_awarded');
			
			// query last 3 events
			$events = qa_db_read_all_assoc(qa_db_query_sub('SELECT UNIX_TIMESTAMP(datetime) as unix_time, ^eventlog.*, ^users.* FROM ^eventlog, ^users WHERE ^eventlog.handle=^users.handle AND ^eventlog.event IN ("q_post", "a_post", "c_post", "a_select", "u_register", "q_edit", "c_edit", "a_edit") ORDER BY ^eventlog.datetime DESC LIMIT #', $limit));
			
	
			if(empty($events))return;
			$postids = array();
			foreach($events as $post){
				$params = cs_event_log_row_parser($post['params']);
				
				if(isset($params['postid']))
					$postids[] = $params['postid'];
			}
			
			$postids = implode(',', $postids);
			
			if(!empty($postids))
				$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT UNIX_TIMESTAMP(^posts.created) as unix_created, ^posts.* FROM ^posts, ^users WHERE  ^posts.postid IN ('.$postids.') AND ^posts.type IN ("Q", "A", "C") ORDER BY ^posts.created DESC'), 'postid');
			
			$o ='';
			
			if(isset($events)){
				$o .= '<ul class="ra-activity">';
				foreach($events as $p){
					$event_name = '';
					$event_icon = '';
					$title = '';
					
					$params = cs_event_log_row_parser($p['params']);
					
					if($p['event'] == 'q_post' ) {
						$event_name = qa_lang('cleanstrap/asked');
						$event_icon = 'icon-question';
					}
					elseif($p['event'] == 'a_post') {
						$event_name = qa_lang('cleanstrap/answered');
						$event_icon = 'icon-answer';
					}
					elseif($p['event'] == 'c_post') {
						$event_name = qa_lang('cleanstrap/commented');
						$event_icon = 'icon-comment';					
					}elseif($p['event'] == 'u_register') {
						$event_name = qa_lang('cleanstrap/joined_community');
						$event_icon = 'icon-user-add';					
						$title = qa_lang_sub('cleanstrap/x_just_registered_in_our_community', $p['handle']);					
					}elseif($p['event'] == 'q_edit') {
						$event_name = qa_lang('cleanstrap/edited_comment');
						$event_icon = 'icon-question';					
					}elseif($p['event'] == 'a_edit') {
						$event_name = qa_lang('cleanstrap/edited_answer');
						$event_icon = 'icon-answer';					
					}elseif($p['event'] == 'c_edit') {
						$event_name = qa_lang('cleanstrap/edited_comment');
						$event_icon = 'icon-comment';					
					}elseif($p['event'] == 'a_select') {
						$event_name = qa_lang('cleanstrap/selected_an_answer');
						$event_icon = 'icon-tick';
						$title = qa_lang_sub('cleanstrap/x_awarded_an_answer', $p['handle']);
					}
					
					$username = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : htmlspecialchars($p['handle']);
					
					$usernameLink = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : '<a href="'.qa_path_html('user/'.$p['handle']).'">'.$p['handle'].'</a>';
					
					$timeCode = qa_when_to_html( $p['unix_time'] , 7);

					$time = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
					
					$o .= '<li class="activity-item">';
					$o .= '<div class="activity-inner">';	
					
					$o .= '<div class="activity-icon pull-left '.$event_icon.'"></div>';
						
					$o .= '<div class="activity-content">';			
					$o .= '<p class="title"><strong class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.@$usernameLink.'</strong> <span class="what">'.$event_name.'</span> <span class="time">'.$time.'</span></p>';
					
					$o .= '<div class="activity-detail">';
					
					$o .= '<div class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.cs_get_post_avatar($p, 20, false).'</div>';

					if($p['event'] == 'q_post' || $p['event'] == 'a_post' || $p['event'] == 'c_post'|| $p['event'] == 'q_edit' || $p['event'] == 'a_edit' || $p['event'] == 'c_edit'){
						$main_post = $posts[$params['postid']];
						
						if ($p['event'] == 'q_post' || $p['event'] == 'q_edit') {
							$o .= '<a class="activity-title" href="' . qa_q_path_html($main_post['postid'], $main_post['title']) . '" title="' . $main_post['title'] . '">' . cs_truncate($main_post['title'],100) . '</a>';
						} elseif ($p['event'] == 'a_post' || $p['event'] == 'a_edit') {
							$o .= '<a class="activity-title" href="' . qa_q_path($main_post['parentid'], $main_post['title'], false, 'C', $main_post['postid']). '">' . cs_truncate(strip_tags($main_post['content']),100) . '</a>';
						} elseif($p['event'] == 'c_post' || $p['event'] == 'c_edit') {
							$o .= '<a class="activity-title" href="' . qa_q_path($main_post['parentid'], $main_post['title'], false, 'C', $main_post['postid']) . '">' . cs_truncate(strip_tags($main_post['content']),100) . '</a>';
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
			$themeobject->output($this->cs_events((int)$widget_opt['cs_sa_count']));
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/