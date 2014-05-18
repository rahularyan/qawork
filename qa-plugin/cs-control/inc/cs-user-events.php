<?php
	class cs_user_event_logger {


		function process_event($event, $userid, $handle, $cookieid, $params)
		{
 			// call_this_method();  //here we can call the scheuler 
			$loggeduserid = qa_get_logged_in_userid();
			$dolog=true;
			$postid = @$params['postid'];
			//qa_fatal_error(var_dump($params));
			switch($event){
				case 'a_post': // user's question had been answered
					
					if ($loggeduserid != $params['parent']['userid']){
						$effecteduserid   = $params['parent']['userid'];
						$question         = $this->GetQuestion($params);
						$params['qtitle'] = $question['title'];
						$params['qid']    = $question['postid'];
						$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
						cs_event_hook('a_post', array($postid,$userid, $effecteduserid, $params, $event));
					}
					break;
				case 'c_post': // user's answer had been commented
					
					$question         = $this->GetQuestion($params);
					$params['qtitle'] = $question['title'];
					$params['qid']    = $question['postid'];
					$thread           = $params['thread'];
					unset($params['thread']);
					if ($loggeduserid != $params['parent']['userid']){
						$effecteduserid = $params['parent']['userid'];
						$this->AddEvent($postid, $userid, $params['parent']['userid'], $params, $event);
						cs_event_hook('c_post', array($postid,$userid, $effecteduserid, $params, $event));
					}
					
					if(count($thread) > 0){
						$user_array = array();
						foreach ($thread as $t){
							if ($loggeduserid != $t['userid'])
								$user_array[] = $t['userid'];
						}
						$user_array = array_unique($user_array, SORT_REGULAR);
						foreach ($user_array as $user){		
							$this->AddEvent($postid, $userid, $user, $params, $event);	
							$effecteduserid = $user ; //for this scenario the $user_array contains all user ids in the current commented thread 
							cs_event_hook('c_post', array($postid,$userid, $effecteduserid, $params, $event));							
						}
					}			
					break;
				case 'q_reshow':				
					require_once QA_INCLUDE_DIR.'qa-app-posts.php';
					$post = qa_post_get_full($postid);
					if ($loggeduserid != $post['userid']){
						$effecteduserid   = $post['userid'];
						$question         = $this->GetQuestion($params);
						$params['qtitle'] = $question['title'];
						$params['qid']    = $question['postid'];
						$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
						cs_event_hook('q_reshow', array($postid,$userid, $effecteduserid, $params, $event));
					}
					break;
				case 'a_reshow':
					require_once QA_INCLUDE_DIR.'qa-app-posts.php';
					$post = qa_post_get_full($postid);
					if ($loggeduserid != $post['userid']){
						$effecteduserid   = $post['userid'];
						$question         = $this->GetQuestion($params);
						$params['qtitle'] = $question['title'];
						$params['qid']    = $question['postid'];
						unset($params['oldanswer']);
						unset($params['content']);
						unset($params['text']);
						$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
						cs_event_hook('a_reshow', array($postid,$userid, $effecteduserid, $params, $event));
					}
					break;
				case 'c_reshow':
					require_once QA_INCLUDE_DIR.'qa-app-posts.php';
					$post = qa_post_get_full($postid);
					if ($loggeduserid != $post['userid']){
						unset($params['oldcomment']);
						$effecteduserid   = $post['userid'];
						$question         = $this->GetQuestion($params);
						$params['qtitle'] = $question['title'];
						$params['qid']    = $question['postid'];
						$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
						cs_event_hook('c_reshow', array($postid,$userid, $effecteduserid, $params, $event));
					}
					break;
				/* case 'a_unselect':
					require_once QA_INCLUDE_DIR.'qa-app-posts.php';
					$post = qa_post_get_full($postid);
					$effecteduserid = $post['userid'];
					qa_db_query_sub(
						"DELETE FROM ^ra_userevent WHERE effecteduserid=$ AND event=$ AND postid=$",
						$effecteduserid, 'a_select', $postid
					);
					cs_event_hook('a_unselect', array($postid,$userid, $effecteduserid, $params, $event));
					
					break; */
				case 'a_select':
					require_once QA_INCLUDE_DIR.'qa-app-posts.php';
					$post = qa_post_get_full($postid);
					if ($loggeduserid != $post['userid']){
						$effecteduserid   = $post['userid'];
						$question         = $this->GetQuestion($params);
						$params['qtitle'] = $question['title'];
						$params['qid']    = $question['postid'];
						$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
						cs_event_hook('a_select', array($postid,$userid, $effecteduserid, $params, $event));
					}
					break;
				case 'q_vote_up':
					$this->UpdateVote('q_vote_up', $postid,$userid, $params, 'q_vote_up', 1);
					$dolog=false;
					break;
				case 'a_vote_up':
					$this->UpdateVote('a_vote_up', $postid,$userid, $params, 'a_vote_up', 1);
					$dolog=false;
					break;
				case 'q_vote_down':
					$this->UpdateVote('q_vote_down', $postid,$userid, $params, 'q_vote_down', -1);				
					$dolog=false;
					break;
				case 'a_vote_down':
					$this->UpdateVote('a_vote_down', $postid,$userid, $params, 'a_vote_down', -1);
					$dolog=false;
					break;
				case 'q_vote_nil':
					$this->UpdateVote('q_vote_nil', $postid,$userid, $params, 'q_vote_nil', 0);
					$dolog=false;
					break;
				case 'a_vote_nil':
					$this->UpdateVote('a_vote_nil', $postid,$userid, $params, 'a_vote_nil', 0);
					$dolog=false;
					break;
				case 'q_approve':
				case 'a_approve':
				case 'c_approve':
				case 'q_reject':
				case 'a_reject':
				case 'c_reject':
					require_once QA_INCLUDE_DIR.'qa-app-posts.php';
					$post = qa_post_get_full($postid);
					if ($loggeduserid != $post['userid']){
						$effecteduserid   = $post['userid'];
						$question         = $this->GetQuestion($params);
						$params['qtitle'] = $question['title'];
						$params['qid']    = $question['postid'];
						$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
						cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
					}
					break;					
				case 'q_favorite':
					$this->UpdateVote('q_favorite', $postid,$userid, $params, 'favorite', 1);
					cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
					$dolog=false;					
					break;
				/* case 'q_unfavorite':
					$this->UpdateVote('q_unfavorite', $postid,$userid, $params, 'unfavorite', -1);
					$dolog=false;					
					break; */
				case 'q_post':
					$already_notified = "" ;
					if ($params['parent']['type']=='A') // related question
					{
						$effecteduserid = $params['parent']['userid'];
						if ($loggeduserid != $effecteduserid){
							$event = 'related';
							$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
							cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
							$already_notified = $effecteduserid ;
						}
					}

					$categoryid = isset($params['categoryid']) ? $params['categoryid'] : '' ;
                    $tags = isset($params['tags']) ? $params['tags'] : '' ;
					$user_datas = $this->cs_get_users_details_notify_email($userid , $tags , $categoryid );
					foreach ($user_datas as $user_data  ) {
						$effecteduserid = $user_data['userid'] ;
						$event = $user_data['event']  ; 

						if ( $effecteduserid != $already_notified ) {
							// $this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
							cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
						}
					}

					break;
				case 'u_favorite':
					$this->UpdateUserFavorite($postid,$userid, $params, 'u_favorite', 1);
					cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
					$dolog=false;
					break;
				/* case 'u_unfavorite':
					$this->UpdateUserFavorite($postid,$userid, $params, 'u_unfavorite', -1);
					$dolog=false;
					break; */
				case 'u_message':
					$effecteduserid = $params['userid'];
					$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
					cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
					break;
				case 'u_wall_post':
					$effecteduserid    = $params['userid'];
					$params['message'] =$params['content'];
					$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
					cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
					break;
				case 'u_level':
					$effecteduserid = $params['userid'];
					$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);
					cs_event_hook($event, array($postid,$userid, $effecteduserid, $params, $event));
					break;
				default:
					$dolog=false;
			
			}
		}
		function AddEvent($postid,$userid, $effecteduserid, $params, $event){
			$paramstring = $this->ParamToString($params);
			qa_db_query_sub(
				'INSERT INTO ^ra_userevent (datetime, userid, effecteduserid, postid, event, params) '.
				'VALUES (NOW(), $, $, $, $, $)',
				$userid, $effecteduserid, $postid, $event, $paramstring
			);
			
		}
		
		function UpdateUserFavorite($postid,$userid, $params, $event, $value){
			$effecteduserid = $params['userid'];
			$posts = qa_db_read_all_values(qa_db_query_sub(
				'SELECT params FROM ^ra_userevent WHERE effecteduserid=$ AND event=$',
				$effecteduserid, 'u_favorite'
			));
			if (count($posts) == 0 ){
				if ($value==1){
					$params['favorited']=1;
					$this->AddEvent($postid,$userid, $effecteduserid, $params, $event);					
				}
			}else{
				$postparams=json_decode($posts[0],true);
				$params['favorited'] = (int)$postparams['favorited'] + $value;
				if ( ($params['favorited'])>=1 ){
					$paramstring = $this->ParamToString($params);
					qa_db_query_sub(
						"UPDATE ^ra_userevent SET datetime=NOW(), userid=$, effecteduserid=$, postid=NULL, event=$, params=$ WHERE effecteduserid=$ AND event=$",
						$userid, $effecteduserid, $event,$paramstring, $effecteduserid, $event
					);
				}else{
					qa_db_query_sub(
						"DELETE FROM ^ra_userevent WHERE effecteduserid=$ AND event=$",
						$effecteduserid, 'u_favorite'
					);
				}
			}
		}
		
		function UpdateVote($newevent, $postid, $userid, $params, $eventname, $value)
		{
			$effecteduserid = $this->GetUseridFromPost($postid);
			$posts = qa_db_read_all_values(qa_db_query_sub(
				'SELECT params FROM ^ra_userevent WHERE postid=$ AND event=$',
				$postid, $newevent
			));
			if (!isset($effecteduserid))
				return; // post from anonymous user
			
			if (count($posts) == 0 ){ // Add New Event
				
				if(($eventname!='q_vote_nil') && ($eventname!='a_vote_nil') && ($eventname!='unfavorite')){
					$question           = $this->GetQuestion($params);
					$params['qtitle']   = $question['title'];
					$params['qid']      = $question['postid'];
					$params['newvotes'] = $value;
					
					$params[$eventname] =1;
					
					$this->AddEvent($postid,$userid, $effecteduserid, $params, $newevent);
					cs_event_hook($newevent, array($postid,$userid, $effecteduserid, $params, $newevent));
				}
			}else{
				$postparams=json_decode($posts[0],true);
				
				if (($eventname=='q_vote_nil') || ($eventname=='a_vote_nil') ){
					$netvotes = $this->GetVotesFromPost($postid);
					$params['newvotes'] = $netvotes;
					$diffrence = (int)$postparams['newvotes'] - (int)$netvotes;
					//qa_fatal_error(var_dump($netvotes));
					switch($eventname){
					case 'q_vote_nil': 
						if ( $diffrence == 1 ) //upvote cancelled
							$params['q_vote_up']=(int)$postparams['q_vote_up']-1;
						elseif ( $diffrence == -1 ) //downvote cancelled
							$params['q_vote_down']=(int)$postparams['q_vote_down']-1;
						break;
					case 'a_vote_nil': 
						if ( $diffrence == 1 ) //upvote cancelled
							$params['a_vote_up']=(int)$postparams['a_vote_up']-1;
						elseif ( $diffrence == -1 ) //downvote cancelled
							$params['a_vote_down']=(int)$postparams['a_vote_down']-1;
						break;
					}
				}else{
					
					if (isset($postparams[$eventname]) && (($eventname == 'favorite') || ($eventname == 'unfavorite')))
						$params[$eventname]=(int)$postparams[$eventname]+$value;
					else{
						$params[$eventname]=(int)$postparams[$eventname]+1;
						$params['newvotes']=(int)$postparams['newvotes']+$value;
					}
				}
	
				foreach ($postparams as $key => $value)
					if (!isset($params[$key]))
						$params[$key] = $value;
				//qa_fatal_error(var_dump($postparams));
				$paramstring = $this->ParamToString($params);
				qa_db_query_sub(
					"UPDATE ^ra_userevent SET datetime=NOW(), userid=$, effecteduserid=$, postid=$, event=$, params=$ WHERE postid=$ AND event=$",
					$userid, $effecteduserid, $postid, $newevent,$paramstring, $postid, $newevent
				);
				cs_event_hook($newevent, array($postid,$userid, $effecteduserid, $params, $newevent));
			}
		}
		
		function ParamToString($params)
		{
			if (isset($params)){
				$params['parent_uid'] = $params['parent']['userid'];
				unset($params['content']);
				unset($params['question']);
				unset($params['answer']);
				unset($params['text']);
				unset($params['parent']);
				unset($params['question']['content']);
				unset($params['oldquestion']);
				//qa_fatal_error(var_dump($postid));
				$paramstring = json_encode( $params );
			}
			else
				$paramstring = '';
			return $paramstring;
		}
		function GetVotesFromPost($postid)
		{
			$netvotes = qa_db_read_one_value(
				qa_db_query_sub(
					'SELECT netvotes FROM ^posts WHERE postid=#',
					$postid
				),true
			);
			return $netvotes;
		}
		function GetQuestion($params){
			$question = array();
			//qa_fatal_error(var_dump($params));
			if (isset($params['question'])){
				$question = $params['question'];
			}elseif(isset($params['parent']['question'])){
				$question = $params['parent']['question'];
			}elseif(isset($params['parent'])){
				$question = $params['parent'];
			}else{
				$postid = @$params['postid'];
				$question = qa_db_read_all_assoc(
					qa_db_query_sub(
						"SELECT qa_posts.type,
						CASE 
							WHEN qa_posts.type='Q'
								THEN qa_posts.title 
							WHEN parent.type='Q'
								THEN parent.title 
							WHEN grandparent.type='Q'
								THEN grandparent.title 
						END AS title,
						CASE 
							WHEN qa_posts.type='Q'
								THEN qa_posts.postid 
							WHEN parent.type='Q'
								THEN parent.postid 
							WHEN grandparent.type='Q'
								THEN grandparent.postid 
						END AS postid
						FROM qa_posts LEFT JOIN qa_posts AS parent ON qa_posts.parentid=parent.postid LEFT JOIN qa_posts as grandparent ON parent.parentid=grandparent.postid
						WHERE qa_posts.postid=#",
						$postid
					)
				);
				//qa_fatal_error(var_dump($question[0]));
				return $question[0];
			}
			//qa_fatal_error(var_dump($question));
			return $question;
		}

		function GetUseridFromPost($postid)
		{
			
			return qa_db_read_one_value(qa_db_query_sub('SELECT userid FROM ^posts WHERE postid=#', $postid ),true);
			
		}

		function cs_get_users_details_notify_email( $userid, $tags , $categoryid )
		{
			$user_datas = qa_db_select_with_pending($this->cs_notify_emails_selectspec($userid, $tags , $categoryid));
        	$user_datas = $this->combine_users($user_datas);
            return $user_datas;
		}

		function cs_notify_emails_selectspec($userid, $tags, $categoryid) {
				require_once CS_CONTROL_DIR .'/addons/notification/functions.php';
	            if (notify_addon_enabled_from_admin_panel()) {  //proceed only if the plugin is enabled
		                  require_once QA_INCLUDE_DIR . 'qa-app-updates.php';
		                  $source = '';
		                  $arguments = array();
		                  if (!!qa_opt('cs_notify_user_followers')) {
		                        $source .= (!!$source) ? ' UNION ' : '';
		                        $source .= "( SELECT ^users.userid , 'q_post_user_fl' as event , ^userpoints.points from ^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^users.userid=^userfavorites.userid WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$  AND ^users.email !=$ )";
		                        $args = array($userid, QA_ENTITY_USER, qa_get_logged_in_user_field('email'));
		                        $arguments = array_merge($arguments, $args);
		                  }
		                  if (!!qa_opt('cs_notify_tag_followers') && !!$tags) {
		                        $source .= (!!$source) ? ' UNION ' : '';
		                        $source .= "( SELECT ^users.userid , 'q_post_tag_fl' as event , ^userpoints.points from ^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^userfavorites.userid=^users.userid WHERE ^userfavorites.entityid IN 
		                            ( SELECT wordid from ^words where ^words.word IN ($) ) AND ^userfavorites.entitytype=$ AND ^users.email !=$ )";
		                        $args = array(qa_tagstring_to_tags($tags), QA_ENTITY_TAG, qa_get_logged_in_user_field('email'));
		                        $arguments = array_merge($arguments, $args);
		                  }
		                  if (!!qa_opt('cs_notify_cat_followers') && !!$categoryid) {
		                        $source .= (!!$source) ? ' UNION ' : '';
		                        $source .= "( SELECT ^users.userid , 'q_post_cat_fl' as event , ^userpoints.points from ^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^userfavorites.userid=^users.userid "
		                                . " WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$ AND ^users.email !=$ )";
		                        $args = array($categoryid, QA_ENTITY_CATEGORY, qa_get_logged_in_user_field('email'));
		                        $arguments = array_merge($arguments, $args);
		                  }
		                  $where_clause = '';
		                  if (!!qa_opt('cs_notify_min_points_opt')) {
		                        //generate where clause 
		                        $min_user_points = qa_opt('cs_notify_min_points_val');
		                        $where_clause = ((!!$min_user_points && ( $min_user_points > 0) )) ? ' WHERE result.points > ' . $min_user_points : '';
		                  }
		                  return array(
		                      'columns' => array(' * '),
		                      'source' => ' ( ' . $source . ' ) as result ' . $where_clause,
		                      'arguments' => $arguments,
		                      'sortasc' => 'title',
		                  );
	            	}  //if plugin is enabled 
        	}

            function combine_users($user_datas) {

	            $unique_user_ids = array();
	            $return_user_datas = array();

	            foreach ($user_datas as $user_data) {
	                  $userid = $user_data['userid'];
	                  if (!!$userid && !in_array($userid, $unique_user_ids)) {
	                        $return_user_datas[] = $user_data;
	                        $unique_user_ids[] = $userid;
	                  }
	            }

	            return $return_user_datas;
      		}
      

	}
	
/**
 * Checks weather the tag / cat /user follower email notification is enabled or noe 
 * @return boolean true if it is enabled , false otherwise 
 */
function notify_addon_enabled_from_admin_panel() {
    return ( (!!qa_opt('cs_enable_email_notfn')) &&
            (
            (!!qa_opt('cs_notify_cat_followers')) ||
            (!!qa_opt('cs_notify_tag_followers')) ||
            (!!qa_opt('cs_notify_user_followers'))
            )
            );
 }

/*
	Omit PHP closing tag to help avoid accidental output
*/
