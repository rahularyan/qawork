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
			$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT datetime,ipaddress,handle,event,params FROM ^eventlog WHERE event IN ("q_post", "a_post", "c_post", "a_select", "u_register", "q_edit", "c_edit", "a_edit") ORDER BY datetime DESC LIMIT #', $limit));

			
			if(empty($posts))return;
			$postids = '';
			$i = 1;
			foreach($posts as $post){
				$params = preg_replace('/\s+/','&',$post['params']);
				parse_str($params, $data); 
				$postids.= ($i != 1 ? ', ': '' ).$data['postid'];
				$i++;
			}
			
			$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT UNIX_TIMESTAMP(^posts.created) as unix_created, ^posts.* , ^users.* FROM ^posts, ^users WHERE (^posts.userid=^users.userid AND ^posts.postid IN ('.$postids.')) AND ^posts.type IN ("Q", "A", "C") ORDER BY ^posts.created DESC'));
			
			$o = '<ul class="ra-activity">';
			foreach($posts as $p){
				$event_name = '';
				$event_icon = '';
				if($p['type'] == 'Q' ) {
					$event_name = qa_lang('cleanstrap/asked');
					$event_icon = 'icon-question';
				}
				else if($p['type'] == 'A') {
					$event_name = qa_lang('cleanstrap/answered');
					$event_icon = 'icon-answer';
				}
				else {
					$event_name = qa_lang('cleanstrap/commented');
					$event_icon = 'icon-comment';					
				}
				
				$username = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : htmlspecialchars($p['handle']);
				$usernameLink = (is_null($p['handle'])) ? qa_lang('cleanstrap/anonymous') : '<a href="'.qa_path_html('user/'.$p['handle']).'">'.$p['handle'].'</a>';
				
				$timeCode = qa_when_to_html( $p['unix_created'] ,7);

				$time = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
				
				$o .= '<li class="activity-item">';
				$o .= '<div class="activity-inner">';	
				
				$o .= '<div class="activity-icon pull-left '.$event_icon.'"></div>';
					
				$o .= '<div class="activity-content">';			
				$o .= '<p class="title"><strong class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.@$usernameLink.'</strong> <span class="what">'.$event_name.'</span> <span class="time">'.$time.'</span></p>';
				
				$o .= '<div class="activity-detail">';
				
				$o .= '<div class="avatar" data-handle="'.$p['handle'].'" data-id="'. $p['userid'].'">'.cs_get_post_avatar($p, 20, false).'</div>';
				
				if ($p['type'] == 'Q') {
					$o .= '<a class="activity-title" href="' . qa_q_path_html($p['postid'], $p['title']) . '" title="' . $p['title'] . '">' . cs_truncate($p['title'],100) . '</a>';
				} elseif ($p['type'] == 'A') {
					$o .= '<a class="activity-title" href="' . cs_post_link($p['parentid']) . '#a' . $p['postid'] . '">' . cs_truncate(strip_tags($p['content']),100) . '</a>';
				} else {
					$o .= '<a class="activity-title" href="' . cs_post_link($p['parentid']) . '#c' . $p['postid'] . '">' . cs_truncate(strip_tags($p['content']),100) . '</a>';
				}
			
				$o .= '</div>';	
				
				$o .= '</div>';	
				$o .= '</div>';	
				$o .= '</li>';
			}
			$o .= '</ul>';
			
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