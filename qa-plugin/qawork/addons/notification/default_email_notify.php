<?php

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'qa-app-emails.php';
	require_once QA_INCLUDE_DIR.'qa-app-format.php';
	require_once QA_INCLUDE_DIR.'qa-util-string.php';

	class qw_default_notify {
		
		function __construct(){
		
		}
		
		function process_event($event, $userid, $handle, $cookieid, $params)
		{
			

				case 'q_queue':
				case 'q_requeue':
					if (qa_opt('moderate_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null,
							($event=='q_requeue') ? qa_lang('emails/remoderate_subject') : qa_lang('emails/moderate_subject'),
							($event=='q_requeue') ? qa_lang('emails/remoderate_body') : qa_lang('emails/moderate_body'),
							array(
								'^p_handle' => isset($handle) ? $handle : (strlen($params['name']) ? $params['name'] :
									(strlen(@$oldquestion['name']) ? $oldquestion['name'] : qa_lang('main/anonymous'))),
								'^p_context' => trim(@$params['title']."\n\n".$params['text']), // don't censor for admin
								'^url' => qa_q_path($params['postid'], $params['title'], true),
								'^a_url' => qa_path_absolute('admin/moderate'),
							)
						);
					break;
					

				case 'a_queue':
				case 'a_requeue':
					if (qa_opt('moderate_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null,
							($event=='a_requeue') ? qa_lang('emails/remoderate_subject') : qa_lang('emails/moderate_subject'),
							($event=='a_requeue') ? qa_lang('emails/remoderate_body') : qa_lang('emails/moderate_body'),
							array(
								'^p_handle' => isset($handle) ? $handle : (strlen($params['name']) ? $params['name'] :
									(strlen(@$oldanswer['name']) ? $oldanswer['name'] : qa_lang('main/anonymous'))),
								'^p_context' => $params['text'], // don't censor for admin
								'^url' => qa_q_path($params['parentid'], $params['parent']['title'], true, 'A', $params['postid']),
								'^a_url' => qa_path_absolute('admin/moderate'),
							)
						);
					break;
					

				case 'c_queue':
				case 'c_requeue':
					if (qa_opt('moderate_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null,
							($event=='c_requeue') ? qa_lang('emails/remoderate_subject') : qa_lang('emails/moderate_subject'),
							($event=='c_requeue') ? qa_lang('emails/remoderate_body') : qa_lang('emails/moderate_body'),
							array(
								'^p_handle' => isset($handle) ? $handle : (strlen($params['name']) ? $params['name'] :
									(strlen(@$oldcomment['name']) ? $oldcomment['name'] : // could also be after answer converted to comment
									(strlen(@$oldanswer['name']) ? $oldanswer['name'] : qa_lang('main/anonymous')))),
								'^p_context' => $params['text'], // don't censor for admin
								'^url' => qa_q_path($params['questionid'], $params['question']['title'], true, 'C', $params['postid']),
								'^a_url' => qa_path_absolute('admin/moderate'),
							)
						);
					break;

					
				case 'q_flag':
				case 'a_flag':
				case 'c_flag':
					$flagcount=$params['flagcount'];
					$oldpost=$params['oldpost'];
					$notifycount=$flagcount-qa_opt('flagging_notify_first');
					
					if ( ($notifycount>=0) && (($notifycount % qa_opt('flagging_notify_every'))==0) )
						qw_send_notification(null, qa_opt('feedback_email'), null, qa_lang('emails/flagged_subject'), qa_lang('emails/flagged_body'), array(
							'^p_handle' => isset($oldpost['handle']) ? $oldpost['handle'] :
								(strlen($oldpost['name']) ? $oldpost['name'] : qa_lang('main/anonymous')),
							'^flags' => ($flagcount==1) ? qa_lang_html_sub('main/1_flag', '1', '1') : qa_lang_html_sub('main/x_flags', $flagcount),
							'^p_context' => trim(@$oldpost['title']."\n\n".qa_viewer_text($oldpost['content'], $oldpost['format'])), // don't censor for admin
							'^url' => qa_q_path($params['questionid'], $params['question']['title'], true, $oldpost['basetype'], $oldpost['postid']),
							'^a_url' => qa_path_absolute('admin/flagged'),
						));
					break;
		
		
				case 'a_select':
					$answer=$params['answer'];
								
					if (isset($answer['notify']) && !qa_post_is_by_user($answer, $userid, $cookieid)) {
						$blockwordspreg=qa_get_block_words_preg();
						$sendcontent=qa_viewer_text($answer['content'], $answer['format'], array('blockwordspreg' => $blockwordspreg));
		
						qw_send_notification($answer['userid'], $answer['notify'], @$answer['handle'], qa_lang('emails/a_selected_subject'), qa_lang('emails/a_selected_body'), array(
							'^s_handle' => isset($handle) ? $handle : qa_lang('main/anonymous'),
							'^q_title' => qa_block_words_replace($params['parent']['title'], $blockwordspreg),
							'^a_content' => $sendcontent,
							'^url' => qa_q_path($params['parentid'], $params['parent']['title'], true, 'A', $params['postid']),
						));
					}
					break;
				
				case 'u_register':
					if (qa_opt('register_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null, qa_lang('emails/u_registered_subject'),
							qa_opt('moderate_users') ? qa_lang('emails/u_to_approve_body') : qa_lang('emails/u_registered_body'), array(
							'^u_handle' => $handle,
							'^url' => qa_path_absolute('user/'.$handle),
							'^a_url' => qa_path_absolute('admin/approve'),
						));
					break;
					
				case 'u_level':
					if ( ($params['level']>=QA_USER_LEVEL_APPROVED) && ($params['oldlevel']<QA_USER_LEVEL_APPROVED) )
						qw_send_notification($params['userid'], null, $params['handle'], qa_lang('emails/u_approved_subject'), qa_lang('emails/u_approved_body'), array(
							'^url' => qa_path_absolute('user/'.$params['handle']),
						));
					break;
				
				case 'u_wall_post':
					if ($userid!=$params['userid']) {
						$blockwordspreg=qa_get_block_words_preg();
						
						qw_send_notification($params['userid'], null, $params['handle'], qa_lang('emails/wall_post_subject'), qa_lang('emails/wall_post_body'), array(
							'^f_handle' => isset($handle) ? $handle : qa_lang('main/anonymous'),
							'^post' => qa_block_words_replace($params['text'], $blockwordspreg),
							'^url' => qa_path_absolute('user/'.$params['handle'], null, 'wall'),
						));
					}
					break;
			}
		}
	
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/