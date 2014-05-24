<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}

// to register the event 
cs_add_action('a_post',        'cs_social_post_event_handler');
cs_add_action('c_post',        'cs_social_post_event_handler');
cs_add_action('q_post_social', 'cs_social_post_event_handler');

$isPosted = false ;

function cs_social_post_event_handler($postid,$userid, $effecteduserid, $params, $event)
{
    require_once CS_CONTROL_DIR . '/addons/social-posting/cs-social-posting-utils.php';
    global $isPosted ;

    if ($isPosted) {
        return ;
    }
    
    // check if the feature is enabled or not -- If not enabled then go back from here 
    if (!(qa_opt('cs_enable_fb_posting') || qa_opt('cs_enable_twitter_posting'))){
        return ;
    }

    $id = isset($params['qid']) ? $params['qid'] : (isset($params['postid']) ? $params['postid'] : "") ;
    $title = isset($params['qtitle']) ? $params['qtitle'] : (isset($params['title']) ? $params['title'] : "") ;
    $message = cs_get_message_using_event($event);

    $all_keys = array('cs_facebook_q_post','cs_facebook_a_post','cs_facebook_c_post','cs_twitter_q_post','cs_twitter_a_post','cs_twitter_c_post',);
    $preferences = cs_get_social_posting_settings($all_keys , $userid);

    $post_to = cs_get_user_social_post_status_for_event( $preferences , $event );
    $data = array(
            'link' => qa_q_path( $id , $title, true),
            'name' => qa_opt('site_title'),
            'caption' => $title,
            'message' => $message ,
            'picture' => qa_opt('ra_logo'),
        );
    cs_social_post($post_to , $data );
    $isPosted = true ;
}

function cs_social_post($post_to , $data )
{
    if (!is_array($post_to) || empty($post_to)) {
        return false;
    }
    if (is_array($post_to)) {
        foreach ($post_to as $provider) {
           switch ($provider) {
               case 'Facebook':
               case 'facebook':
                   cs_post_to_facebook($data) ;
                   break;
               case 'Twitter':
               case 'twitter':
                   cs_post_to_twitter($data) ;
                   break;
               default:
                   break;
           }
        }
    }
}

function cs_post_to_facebook($data) {
    require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
    require_once CS_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';
    require_once CS_CONTROL_DIR . '/addons/social-posting/cs-social-posting-utils.php';

    try {
        $loginCallback = qa_path_absolute(qa_opt('site_url'), array());
        // get the config for facebook and creaet a instance
        $config = cs_social_get_config_common($loginCallback, 'Facebook');
        $hybridauth = new Hybrid_Auth($config);
        // check if the fb is previously connected 
        $facebook_active = $hybridauth->isConnectedWith("Facebook");

        if (!$facebook_active) {
            // if not connected with facebook then restore the session from database 
            $facebook_hauthSession = cs_social_get_saved_hauth_session("facebook_hauthSession" , qa_get_logged_in_userid());
            if (!$facebook_hauthSession) {
                // if the session is not set in the db then return 
                return false;
            }
            // then restore the facebook session 
            $hybridauth->restoreSessionData($facebook_hauthSession);
        }
        // get the Facebook adaptor 
        $adapter = $hybridauth->getAdapter("Facebook");
        /*
        $data = array(
            'link' => 'http://amiyasahu.com',
            'picture' => 'http://demo.rahularyan.com/cleanstrap/qa-theme/cleanstrap/uploads/ba96c02d08acbfc29b7f5a2685f4f31f.png',
            'name' => 'CleanStrap',
            'caption' => 'Asking for testing this functionality',
            'message' => 'Web Application Developer and Designer (Updated from my application )',
        );*/
        // Now update the Facebook Status 
        $adapter->setUserStatus($data);
    } catch (Exception $e) {
        // cs_log("Error while posting to facebook " . print_r($e, true));
    }
}


function cs_post_to_twitter($data) {
    require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
    require_once CS_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';
    require_once CS_CONTROL_DIR . '/addons/social-posting/cs-social-posting-utils.php';
    // build the message for twitter with only message and link
    
    $message  = $data['message'] ;
    $message .= " " ;
    $message .= $data['link'] ;

    try {
        $loginCallback = qa_path_absolute(qa_opt('site_url'), array());
        // get the config for twitter and creaet a instance
        $config = cs_social_get_config_common($loginCallback, 'Twitter');
        $hybridauth = new Hybrid_Auth($config);
        // check if the twitter is previously connected 
        $twitter_active = $hybridauth->isConnectedWith("Twitter");
        if (!$twitter_active) {
            // if not connected with twitter then restore the session from database 
            $twitter_hauthSession = cs_social_get_saved_hauth_session("twitter_hauthSession" , qa_get_logged_in_userid());
            if (!$twitter_hauthSession) {
                // if the session is not set in the db then return 
                return false;
            }
            // then restore the twitter session 
            $hybridauth->restoreSessionData($twitter_hauthSession);
        }
        // get the Facebook adaptor 
        $adapter = $hybridauth->getAdapter("Twitter");
        // Now update the Facebook Status 
        $adapter->setUserStatus($message);
    } catch (Exception $e) {
        // cs_log("Error while posting to twitter " . print_r($e, true));
    }
}

function cs_get_message_using_event($event)
{
    if (!$event) {
        return "" ;
    }
    $message = "" ;
    $subs = array( 
                '^site_title' => qa_opt('site_title') , 
                );   
    switch ($event) {
        case 'q_post':
            $message = strtr(qa_lang("cs_social_posting/q_asked") , $subs );
            break;
        case 'a_post':
            $message = strtr(qa_lang("cs_social_posting/a_posted") , $subs );
            break;
        case 'c_post':
            $message = strtr(qa_lang("cs_social_posting/c_posted") , $subs );
            break;
        default:
            break;
    }

    return $message ;

}
