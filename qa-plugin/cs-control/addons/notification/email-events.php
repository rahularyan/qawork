<?php
/* don't allow this page to be requested directly from browser */ 
if (!defined('QA_VERSION')) {
            header('Location: /');
            exit;
}

//if this is set to true , the email will be written to the log file 
define('CS_SEND_EMAIL_DEBUG_MODE', true);

//define the event hook event handlers // remove  NULL, ok , and here 
cs_add_action('a_post','cs_notification_event');
cs_add_action('c_post','cs_notification_event');
cs_add_action('q_reshow','cs_notification_event');
cs_add_action('a_reshow','cs_notification_event');
cs_add_action('c_reshow','cs_notification_event');
cs_add_action('a_select','cs_notification_event');
cs_add_action('q_vote_up','cs_notification_event');
cs_add_action('a_vote_up','cs_notification_event');
cs_add_action('q_vote_down','cs_notification_event');
cs_add_action('a_vote_down','cs_notification_event');
cs_add_action('q_vote_nil','cs_notification_event');
cs_add_action('a_vote_nil','cs_notification_event');
cs_add_action('q_approve','cs_notification_event');
cs_add_action('a_approve','cs_notification_event');
cs_add_action('c_approve','cs_notification_event');
cs_add_action('q_reject','cs_notification_event');
cs_add_action('a_reject','cs_notification_event');
cs_add_action('c_reject','cs_notification_event');
cs_add_action('q_favorite','cs_notification_event');
cs_add_action('q_post','cs_notification_event');
cs_add_action('u_favorite','cs_notification_event');
cs_add_action('u_message','cs_notification_event');
cs_add_action('u_wall_post','cs_notification_event');
cs_add_action('u_level','cs_notification_event');
//added for related questions 
cs_add_action('related','cs_notification_event');
cs_add_action('q_post_user_fl','cs_notification_event');
cs_add_action('q_post_tag_fl','cs_notification_event');
cs_add_action('q_post_cat_fl','cs_notification_event');

function cs_notification_event($postid,$userid, $effecteduserid, $params, $event) {
      $loggeduserid   = isset($userid) ? $userid : qa_get_logged_in_userid();
      if (!!$effecteduserid) {
            cs_notify_users_by_email($event, $postid, $loggeduserid, $effecteduserid, $params);
      }
}

function cs_process_emails_from_db() {
      require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
      require_once QA_INCLUDE_DIR . 'qa-util-string.php';
      //here extract all the email contents from database and perform the email sending operation 
      $email_queue_data    = cs_get_email_queue();
      $email_list          = cs_get_email_list($email_queue_data);
      $subs                = array();
      $subs['^site_title'] = qa_opt('site_title');
      $greeting            = qa_lang("notification/greeting");
      $thank_you_message   = qa_lang("notification/thank_you_message");
      $subject             = strtr(qa_lang("notification/notification_email_subject"), $subs);
      $processed_queue_ids = array() ; 

      foreach ($email_list as $email_data) {
            $email              = $email_data['email'];
            $name               = $email_data['name'];
            $subs['^user_name'] = $name;
            $email_body         = cs_prepare_email_body($email_queue_data, $email);
            $email_body         = $greeting . $email_body . $thank_you_message;
            $email_body         = strtr($email_body, $subs);
            $notification_sent  = cs_send_email_notification(null, $email, $name, $subject, $email_body, $subs);
            if (!!$notification_sent) {
                  // if the notification is sent then 
                  $processed_queue_ids = array_merge($processed_queue_ids , cs_get_queue_ids_from_queue_data($email_queue_data, $email) ) ;
            }
            
      }
      if (!empty($processed_queue_ids)) {
            //update the queue status 
            $processed_queue_ids = array_unique($processed_queue_ids) ;
            cs_update_email_queue_status($processed_queue_ids);
      }

}

function cs_get_queue_ids_from_queue_data($email_queue_data, $email) {
      $queue_ids = array();
      if (!!$email_queue_data && is_array($email_queue_data) && !!$email) {
            foreach ($email_queue_data as $email_queue) {
                  if (isset($email_queue['email'])) {
                        if ($email_queue['email'] === $email) {
                              $queue_ids[] = $email_queue['queue_id'];
                        }
                  }
            }
      }
      return $queue_ids;
}

function cs_prepare_email_body($email_queue_data, $email) {
      $email_body_arr = array();
      $summerized_email_body = array();
      $email_body = "";

      if (is_array($email_queue_data)) {
            foreach ($email_queue_data as $queue_data) {
                  if ($queue_data['email'] === $email) {
                        $event = $queue_data['event'];
                        $body = $queue_data['body'];
                        if (!!$body) {
                              $email_body_arr[$event] = (isset($email_body_arr[$event]) && !empty($email_body_arr[$event]) ) ? $email_body_arr[$event] . "\n\n" : "";
                              $email_body_arr[$event] .= $body;
                        }
                  } //outer if 
            } //foreach
            foreach ($email_body_arr as $event => $email_body_for_event) {
                  if (!isset($summerized_email_body[$event])) {
                        $summerized_email_body[$event] = cs_get_email_headers($event);
                  }
                  $summerized_email_body[$event] .= (!!$email_body_for_event) ? $email_body_for_event . "\n" : "";
            }//foreach 

            foreach ($summerized_email_body as $event => $email_body_chunk) {
                  if (!!$email_body_chunk) {
                        $email_body .= $email_body_chunk;
                  }
            }//foreach 
      } //if 
      return $email_body;
}

function cs_get_email_list($email_queue_data) {
      $email_list = array();
      $unique_email_list = array();
      if (is_array($email_queue_data)) {
            foreach ($email_queue_data as $queue_data) {
                  if (isset($queue_data['email']) && !empty($queue_data['email'])) {
                        $email = $queue_data['email'];
                        if (!in_array($email, $unique_email_list)) {
                              $unique_email_list[] = $email;
                              $data = array('email' => $email);
                              if (isset($queue_data['name']) && !empty($queue_data['name'])) {
                                    $data['name'] = $queue_data['name'];
                              }
                              $email_list[] = $data;
                        }
                  }
            }
      }
      return $email_list;
}

function cs_get_email_queue() {
      return qa_db_read_all_assoc(qa_db_query_sub("SELECT * from ^ra_email_queue queue join ^ra_email_queue_receiver rcv on queue.id = rcv.queue_id WHERE queue.status = 0 "));
}

function cs_get_name_from_userid($userid) {
      return qa_db_read_one_value(qa_db_query_sub("SELECT ^userprofile.content AS name from  ^userprofile WHERE ^userprofile.title = 'name' AND ^userprofile.userid =# ", $userid), true);
}

function cs_get_user_details_from_userid($userid) {

      return qa_db_read_one_assoc(qa_db_query_sub("SELECT ^users.email AS email , ^users.handle AS handle from ^users WHERE ^users.userid = #", $userid), true);
}

function cs_update_email_queue_status($queue_ids) {
      return qa_db_query_sub("UPDATE ^ra_email_queue SET status = '1', sent_on = CURRENT_TIME() WHERE ^ra_email_queue.id IN (#)", $queue_ids);
}

function cs_notify_users_by_email($event, $postid, $userid, $effecteduserid, $params) {
      if (!!$effecteduserid) {
            //get the working user data  
            $logged_in_handle    = qa_get_logged_in_handle();
            $logged_in_user_name = cs_get_name_from_userid($userid);
            $logged_in_user_name = (!!$logged_in_user_name) ? $logged_in_user_name : $logged_in_handle;

            $name = cs_get_name_from_userid($effecteduserid);

            switch ($event) {

                  case 'a_post':
                  case 'related':
                        $parent = isset($params['parent']) ? $params['parent'] : "";
                        if (!!$parent) {
                              $name  = (!!$name) ? $name : $parent['handle'];
                              $email = $parent['email'];
                        } else {
                              //seems proper values are not available 
                              return;
                        }
                        break;
                  case 'c_post':
                  case 'q_reshow':
                  case 'a_reshow':
                  case 'c_reshow':
                  case 'a_select':
                  case 'q_vote_up':
                  case 'q_vote_down':
                  case 'a_vote_up':
                  case 'a_vote_down':
                  case 'q_favorite':
                  case 'u_favorite':
                  case 'u_message':
                  case 'u_wall_post':
                  case 'u_level':
                  case 'q_post_user_fl':
                  case 'q_post_tag_fl':
                  case 'q_post_cat_fl':
                        //this is because we wont have the $parent['email'] for each effected userids when a these selected events occurs 
                        $user_details = cs_get_user_details_from_userid($effecteduserid);
                        $name         = (!!$name) ? $name : $user_details['handle'];
                        $email        = $user_details['email'];
                        break;
                  case 'q_approve':
                  case 'q_reject':
                        $oldquestion = $params['oldquestion'];
                        $name        = (!!$name) ? $name : $oldquestion['handle'];
                        $email       = $oldquestion['email'];
                        break;
                  case 'a_approve':
                  case 'a_reject':
                        $oldanswer = $params['oldanswer'];
                        $name      = (!!$name) ? $name : $oldanswer['handle'];
                        $email     = $oldanswer['email'];
                        break;
                  case 'c_approve':
                  case 'c_reject':
                        $oldcomment = $params['oldcomment'];
                        $name       = (!!$name) ? $name : $oldcomment['handle'];
                        $email      = $oldcomment['email'];
                        break;
                  default:
                        break;
            }

            $notifying_user['userid'] = $effecteduserid;
            $notifying_user['name']   = $name;
            $notifying_user['email']  = $email;
            //consider only first 50 characters for saving notification 
            if ($event === 'u_message') {
                  $content  = (isset($params['message']) && !empty($params['message'])) ? $params['message'] : "";
                  $title    = "";
                  $canreply = !(qa_get_logged_in_flags() & QA_USER_FLAGS_NO_MESSAGES);
                  $url      = qa_path_absolute($canreply ? ('message/' . $logged_in_handle) : ('user/' . $logged_in_handle));
            } else if ($event === 'u_wall_post') {
                  $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
                  if (!!$content) {
                        $blockwordspreg = qa_get_block_words_preg();
                        $content        = qa_block_words_replace($content, $blockwordspreg);
                  }
                  $title = "";
                  $url   = qa_path_absolute('user/' . $params['handle'] . '/wall', null, null);
            } else if ($event === 'u_level') {
                  $title     = "";
                  $url       = qa_path_absolute('user/' . $params['handle']);
                  $old_level = $params['oldlevel'];
                  $new_level = $params['level'];
                  if (($new_level >= QA_USER_LEVEL_APPROVED) && ($old_level < QA_USER_LEVEL_APPROVED)) {
                        $approved_only = true;
                  } else if (($new_level >= QA_USER_LEVEL_APPROVED) && ($old_level < $new_level )) {
                        $approved_only = false;
                  } else {
                        //if the designation decreases no need to notify 
                        return;
                  }

                  if (!$approved_only) {
                        $new_designation = cs_get_user_desg($new_level);
                  }

                  $content = strtr(qa_lang($approved_only ? 'notification/u_level_approved_body_email' : 'notification/u_level_improved_body_email'), array(
                      '^f_handle'        => $fromhandle,
                      '^done_by'         => isset($logged_in_user_name) ? $logged_in_user_name : isset($logged_in_handle) ? $logged_in_handle : qa_lang('main/anonymous'),
                      '^url'             => $url,
                      '^new_designation' => $new_designation,
                  ));
            } else if($event === "q_post_user_fl " || $event === "q_post_tag_fl" || $event === "q_post_cat_fl" ){
                  $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
                  $title = (isset($params['title']) && !empty($params['title'])) ? $params['title'] : "";
                  $url = qa_q_path($params['postid'], $params['title'], true);
            } else {
                  $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
                  $title = (isset($params['qtitle']) && !empty($params['qtitle'])) ? $params['qtitle'] : "";
                  $url = qa_q_path($params['qid'], $params['qtitle'], true);
            }

            //shrink the email body content 
            if (!!$content && (strlen($content) > 50)) $content = cs_shrink_email_body($params['text'], 50);

            cs_save_email_notification(null, $notifying_user, $logged_in_handle, $event, array(
                '^q_handle'  => isset($logged_in_user_name) ? $logged_in_user_name : isset($logged_in_handle) ? $logged_in_handle : qa_lang('main/anonymous'),
                '^q_title'   => $title,
                '^q_content' => $content,
                '^url'       => (!!$url) ? $url : "",
                '^done_by'   => isset($logged_in_user_name) ? $logged_in_user_name : isset($logged_in_handle) ? $logged_in_handle : qa_lang('main/anonymous'),
                    )
            );
      }
}

function cs_get_user_desg($level) {
      switch ($level) {
            case QA_USER_LEVEL_BASIC :
                  return qa_lang("notification/basic_desg");
                  break;
            case QA_USER_LEVEL_APPROVED :
                  return qa_lang("notification/approved_desg");
                  break;
            case QA_USER_LEVEL_EXPERT :
                  return qa_lang("notification/expert_desg");
                  break;
            case QA_USER_LEVEL_EDITOR :
                  return qa_lang("notification/editor_desg");
                  break;
            case QA_USER_LEVEL_MODERATOR :
                  return qa_lang("notification/moderator_desg");
                  break;
            case QA_USER_LEVEL_ADMIN :
                  return qa_lang("notification/admin_desg");
                  break;
            case QA_USER_LEVEL_SUPER :
                  return qa_lang("notification/super_admin_desg");
                  break;
            default:
                  break;
      }
}

function cs_get_email_headers($event = "") {
      if (!!$event) {
            switch ($event) {
                  case 'a_post':
                        return qa_lang("notification/a_post_email_header");
                        break;
                  case 'c_post':
                        return qa_lang("notification/c_post_email_header");
                        break;
                  case 'q_reshow':
                        return qa_lang("notification/q_reshow_email_header");
                        break;
                  case 'a_reshow':
                        return qa_lang("notification/a_reshow_email_header");
                        break;
                  case 'c_reshow':
                        return qa_lang("notification/c_reshow_email_header");
                        break;
                  case 'a_select':
                        return qa_lang("notification/a_select_email_header");
                        break;
                  case 'q_vote_up':
                        return qa_lang("notification/q_vote_up_email_header");
                        break;
                  case 'a_vote_up':
                        return qa_lang("notification/a_vote_up_email_header");
                        break;
                  case 'q_vote_down':
                        return qa_lang("notification/q_vote_down_email_header");
                        break;
                  case 'a_vote_down':
                        return qa_lang("notification/a_vote_down_email_header");
                        break;
                  case 'q_vote_nil':
                        return qa_lang("notification/q_vote_nil_email_header");
                        break;
                  case 'a_vote_nil':
                        return qa_lang("notification/a_vote_nil_email_header");
                        break;
                  case 'q_approve':
                        return qa_lang("notification/q_approve_email_header");
                        break;
                  case 'a_approve':
                        return qa_lang("notification/a_approve_email_header");
                        break;
                  case 'c_approve':
                        return qa_lang("notification/c_approve_email_header");
                        break;
                  case 'q_reject':
                        return qa_lang("notification/q_reject_email_header");
                        break;
                  case 'a_reject':
                        return qa_lang("notification/a_reject_email_header");
                        break;
                  case 'c_reject':
                        return qa_lang("notification/c_reject_email_header");
                        break;
                  case 'q_favorite':
                        return qa_lang("notification/q_favorite_email_header");
                        break;
                  case 'q_post':
                        return qa_lang("notification/q_post_email_header");
                        break;
                  case 'q_post_user_fl':
                        return qa_lang("notification/q_post_user_fl_email_header");
                        break;
                  case 'q_post_cat_fl':
                        return qa_lang("notification/q_post_cat_fl_email_header");
                        break;
                  case 'q_post_tag_fl':
                        return qa_lang("notification/q_post_tag_fl_email_header");
                        break;

                  case 'u_favorite':
                        return qa_lang("notification/u_favorite_email_header");
                        break;
                  case 'u_message':
                        return qa_lang("notification/u_message_email_header");
                        break;
                  case 'u_wall_post':
                        return qa_lang("notification/u_wall_post_email_header");
                        break;
                  case 'u_level':
                        return qa_lang("notification/u_level_email_header");
                        break;
                  case 'related':
                        return qa_lang("notification/related_email_header");
                        break;
                  default:
                        break;
            }
      }
}

function cs_get_email_body($event = "") {
      if (!!$event) {
            switch ($event) {
                  case 'a_post':
                        return qa_lang("notification/a_post_body_email");
                        break;
                  case 'c_post':
                        return qa_lang("notification/c_post_body_email");
                        break;
                  case 'q_reshow':
                        return qa_lang("notification/q_reshow_body_email");
                        break;
                  case 'a_reshow':
                        return qa_lang("notification/a_reshow_body_email");
                        break;
                  case 'c_reshow':
                        return qa_lang("notification/c_reshow_body_email");
                        break;
                  case 'a_select':
                        return qa_lang("notification/a_select_body_email");
                        break;
                  case 'q_vote_up':
                        return qa_lang("notification/q_vote_up_body_email");
                        break;
                  case 'a_vote_up':
                        return qa_lang("notification/a_vote_up_body_email");
                        break;
                  case 'q_vote_down':
                        return qa_lang("notification/q_vote_down_body_email");
                        break;
                  case 'a_vote_down':
                        return qa_lang("notification/a_vote_down_body_email");
                        break;
                  case 'q_vote_nil':
                        return qa_lang("notification/q_vote_nil_body_email");
                        break;
                  case 'a_vote_nil':
                        return qa_lang("notification/a_vote_nil_body_email");
                        break;
                  case 'q_approve':
                        return qa_lang("notification/q_approve_body_email");
                        break;
                  case 'a_approve':
                        return qa_lang("notification/a_approve_body_email");
                        break;
                  case 'c_approve':
                        return qa_lang("notification/c_approve_body_email");
                        break;
                  case 'q_reject':
                        return qa_lang("notification/q_reject_body_email");
                        break;
                  case 'a_reject':
                        return qa_lang("notification/a_reject_body_email");
                        break;
                  case 'c_reject':
                        return qa_lang("notification/c_reject_body_email");
                        break;
                  case 'q_favorite':
                        return qa_lang("notification/q_favorite_body_email");
                        break;
                  case 'q_post':
                  case 'q_post_user_fl':
                  case 'q_post_tag_fl':
                  case 'q_post_cat_fl':
                        return qa_lang("notification/q_post_body_email");
                        break;
                  case 'u_favorite':
                        return qa_lang("notification/u_favorite_body_email");
                        break;
                  case 'u_message':
                        $body = qa_lang("notification/u_message_body_email");
                        $canreply = !(qa_get_logged_in_flags() & QA_USER_FLAGS_NO_MESSAGES);
                        $more = qa_lang($canreply ? 'notification/u_message_reply_email' : 'notification/u_message_info');
                        return $body . $more;
                        break;
                  case 'u_wall_post':
                        return qa_lang("notification/u_wall_post_body_email");
                        break;
                  case 'u_level':
                        return qa_lang("notification/u_level_body_email");
                        break;
                  case 'related':
                        return qa_lang("notification/related_body_email");
                        break;
                  default:
                        break;
            }
      }
}

function cs_shrink_email_body($email_body, $max_body_length = 50) {
      if (!!$email_body) {
            $email_body = substr($email_body, 0, $max_body_length);
            $email_body .= "....";
      }
      return $email_body;
}

function cs_save_email_notification($bcclist, $notifying_user, $handle, $event, $subs) {
      require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
      require_once QA_INCLUDE_DIR . 'qa-util-string.php';

      $subs['^site_title'] = qa_opt('site_title');
      $subs['^handle']     = $handle;
      $subs['^open']       = "\n";
      $subs['^close']      = "\n";
      $body                = cs_get_email_body($event);
      $id = cs_dump_email_content_to_db(array(
          'event' => $event,
          'body'  => strtr($body, $subs),
          'by'    => $handle,
      ));
      cs_dump_email_to_db($notifying_user, $id);
}

function cs_dump_email_content_to_db($param) {
      qa_db_query_sub(
              'INSERT INTO ^ra_email_queue (event, body , created_by ) ' .
              'VALUES ($, $ , $ )', $param['event'], $param['body'], $param['by']
      );

      return qa_db_last_insert_id();
}

function cs_dump_email_to_db($notifying_user, $queue_id) {
      qa_db_query_sub(
              'INSERT INTO ^ra_email_queue_receiver (userid, email , name , queue_id ) ' .
              'VALUES (#, $ , $ , # )', $notifying_user['userid'], $notifying_user['email'], $notifying_user['name'], $queue_id
      );

      return qa_db_last_insert_id();
}

function cs_send_email_notification($bcclist, $email, $handle, $subject, $body, $subs) {

      global $qa_notifications_suspended;

      if ($qa_notifications_suspended > 0) return false;

      require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
      require_once QA_INCLUDE_DIR . 'qa-util-string.php';

      $subs['^site_title'] = qa_opt('site_title');
      $subs['^handle']     = $handle;
      $subs['^open']       = "\n";
      $subs['^close']      = "\n";

      $email_param = array(
          'fromemail' => qa_opt('from_email'),
          'fromname' => qa_opt('site_title'),
          'mail_list' => $email,
          'toname' => $handle,
          'bcclist' => $bcclist,
          'subject' => strtr($subject, $subs),
          'body' => strtr($body, $subs),
          'html' => true ,
      );
      if (CS_SEND_EMAIL_DEBUG_MODE) {
            //this will write to the log file 
            return cs_send_email_fake($email_param);
      }
      return cs_send_email($email_param);
}

function cs_send_email($params) {
      require_once QA_INCLUDE_DIR . 'qa-class.phpmailer.php';
      $mailer           = new PHPMailer();
      $mailer->CharSet  = 'utf-8';
      $mailer->From     = $params['fromemail'];
      $mailer->Sender   = $params['fromemail'];
      $mailer->FromName = $params['fromname'];
      if (isset($params['mail_list'])) {
            if (is_array($params['mail_list'])) {
                  foreach ($params['mail_list'] as $email) {
                        $mailer->AddAddress($email['toemail'], $email['toname']);
                  }
            } else {
                  $mailer->AddAddress($params['mail_list'], $params['toname']);
            }
      }
      $mailer->Subject = $params['subject'];
      $mailer->Body    = $params['body'];
      if (isset($params['bcclist'])) {
            foreach ($params['bcclist'] as $email) {
                  $mailer->AddBCC($email);
            }
      }

      if ($params['html']) $mailer->IsHTML(true);

      if (qa_opt('smtp_active')) {
            $mailer->IsSMTP();
            $mailer->Host = qa_opt('smtp_address');
            $mailer->Port = qa_opt('smtp_port');

            if (qa_opt('smtp_secure')) $mailer->SMTPSecure = qa_opt('smtp_secure');

            if (qa_opt('smtp_authenticate')) {
                  $mailer->SMTPAuth = true;
                  $mailer->Username = qa_opt('smtp_username');
                  $mailer->Password = qa_opt('smtp_password');
            }
      } else {
            //smtp is not active 
      }
      return $mailer->Send();
}

function cs_send_email_fake($email_param) {
      // cs_log("Fake Email Sending to log the entire email message ");
      // cs_log(print_r($email_param, true));
      //fake email should never fail 
      return true;
}

