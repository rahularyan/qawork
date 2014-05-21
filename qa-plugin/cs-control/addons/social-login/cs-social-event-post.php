<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}

// to register the event 
cs_event_hook('a_post_social_post', NULL, 'cs_post_to_facebook');
cs_event_hook('q_post_social_post', NULL, 'cs_post_to_facebook');

function cs_post_to_facebook($data) {
    require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
    require_once CS_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';
    /*if (!is_array($data) || !isset($data[3]) || !is_array($data[3])) {
        cs_log("returning back from here ");
        return false;
    }*/
    // get the user details 
    /*$params = isset($data[3]) ? $data[3] : "";
    $postid = isset($params['postid']) ? $params['postid'] : "";
    $userid = isset($data[1]) ? $data[1] : qa_get_logged_in_userid();
    $logged_in_handle = qa_get_logged_in_handle();
    $logged_in_user_name = cs_get_name_from_userid($userid);
    $logged_in_user_name = (!!$logged_in_user_name) ? $logged_in_user_name : $logged_in_handle;*/
   /* // get the post details , title , text , link
    $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
    $title   = (isset($params['qtitle']) && !empty($params['qtitle'])) ? $params['qtitle'] : "";
    $url     = qa_q_path($params['qid'], $params['qtitle'], true);
    $content = cs_truncate($content , 200);
    // now get the hybridauth api and verything needed to update facebook status 
    // prepare the configuration of HybridAuth*/
    try {
        $loginCallback = qa_path_absolute(qa_opt('site_url'), array());

    $config = cs_social_get_config_common($loginCallback, 'Facebook');

    // try to login
    $hybridauth = new Hybrid_Auth($config);
        cs_log("got hybridauth");

    // check if the faecbook session is active or not
    $facebook_active = $hybridauth->isConnectedWith("Facebook");
        cs_log("got facebook status ");

    if (!$facebook_active) {
        cs_log("getting from profile session ");
        // if facebook is not active . try restoring the session 
        // first check if the user ever connected with facebook 
        $facebook_hauthSession = cs_social_get_saved_hauth_session("facebook_hauthSession");
        cs_log("The facebook session is "+print_r($facebook_hauthSession, true));

        if (!$facebook_hauthSession) {
            cs_log("false is returned ");
            return false;
        }
        // restore session 
        $hybridauth->restoreSessionData($facebook_hauthSession);
    }
    cs_log("Now posting to facebook ");
    // now post to facebook 
    $adapter = $hybridauth->getAdapter("Facebook");
    $parameter = array(
               'link' => 'http://amiyasahu.com' ,
               'picture' => 'http://amiyasahu.com/assets/img/amiya.jpg' ,
               'name' => 'Amiya Sahu' ,
               'caption' => 'Amiya Sahu' ,
               'message' => 'Web Application Developer and Designer (Updated from my application )' ,
        );
    $adapter->setUserStatus($parameter);
    cs_log("user status is set now ");
    } catch (Exception $e) {
        cs_log("Error while posting to facebook " . print_r($e , true ));
    }
    
}
