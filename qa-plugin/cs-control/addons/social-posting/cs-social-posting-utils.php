<?php
/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

function cs_social_get_saved_hauth_session($hauthSession , $userid)
{
   return qa_db_read_one_value(qa_db_query_sub("SELECT ^userprofile.content AS name from  ^userprofile WHERE ^userprofile.title =$ AND ^userprofile.userid = # " , $hauthSession , $userid ), true);
}

function cs_save_social_posting_settings($data , $userid)
{
	require_once QA_INCLUDE_DIR.'qa-db-users.php';
    foreach ($data as $key => $value) {
   		 qa_db_user_profile_set($userid, $key, $value);
    }
}

function cs_get_social_posting_settings($all_keys , $userid)
{
   $values = qa_db_read_all_assoc(qa_db_query_sub("SELECT ^userprofile.title , ^userprofile.content from  ^userprofile WHERE ^userprofile.title in (#) AND ^userprofile.userid = # " , $all_keys , $userid ));
   $result_arr = array();
   foreach ($values as $value) {
   		$result_arr[$value['title']] = $value['content'] ;
   }
   return $result_arr;
}

function cs_get_user_social_post_status_for_event($preferences , $event )
{
    $post_to = array() ; 

    switch ($event) {
        case 'q_post':
            if (isset($preferences['cs_facebook_q_post']) && !!$preferences['cs_facebook_q_post'] && !!qa_opt('cs_enable_fb_posting') ) {
               $post_to[] = "Facebook" ;
            }
            if (isset($preferences['cs_twitter_q_post']) && !!$preferences['cs_twitter_q_post'] && !!qa_opt('cs_enable_twitter_posting') ) {
               $post_to[] = "Twitter" ;
            }
            break;
        case 'a_post':
            if (isset($preferences['cs_facebook_a_post']) && !!$preferences['cs_facebook_a_post'] && !!qa_opt('cs_enable_fb_posting') ) {
               $post_to[] = "Facebook" ;
            }
            if (isset($preferences['cs_twitter_a_post']) && !!$preferences['cs_twitter_a_post'] && !!qa_opt('cs_enable_twitter_posting') ) {
               $post_to[] = "Twitter" ;
            }
            break;
        case 'c_post':
            if (isset($preferences['cs_facebook_c_post']) && !!$preferences['cs_facebook_c_post'] && !!qa_opt('cs_enable_fb_posting') ) {
               $post_to[] = "Facebook" ;
            }
            if (isset($preferences['cs_twitter_c_post']) && !!$preferences['cs_twitter_c_post'] && !!qa_opt('cs_enable_twitter_posting') ) {
               $post_to[] = "Twitter" ;
            }
            break;
        default:
            break;
    }
    return $post_to ;
}