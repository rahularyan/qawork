<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}

// to register the event 
cs_add_action('a_post',        'cs_social_post_event_handler');
cs_add_action('q_post_social', 'cs_social_post_event_handler');

$isPosted = false ;

function cs_social_post_event_handler($postid,$userid, $effecteduserid, $params, $event)
{
    global $isPosted ;

    if ($isPosted) {
        return ;
    }
    
    $id = isset($params['qid']) ? $params['qid'] : (isset($params['postid']) ? $params['postid'] : "") ;
    $title = isset($params['qtitle']) ? $params['qtitle'] : (isset($params['title']) ? $params['title'] : "") ;
    $post_to = array('Facebook' , 'Twitter');
    $data = array(
            'link' => qa_q_path( $id , $qtitle, true),
            'name' => qa_opt('site_title'),
            'caption' => $title,
            'message' => 'My Participation',
            'picture' => qa_opt('ra_logo'),
        );
    cs_social_post($post_to , $data );
    $isPosted = true ;
}

function cs_social_post($post_to , $data )
{
    if (is_array($post_to)) {
        foreach ($post_to as $provider) {
           switch ($provider) {
               case 'Facebook':
               case 'facebook':
                   cs_post_to_facebook($data) ;
                   break;
               case 'Twitter':
               case 'twitter':
                   cs_post_to_twitter($data['message']) ;
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
        // get the config for facebookand creaet a instance
        $config = cs_social_get_config_common($loginCallback, 'Facebook');
        $hybridauth = new Hybrid_Auth($config);
        // check if the fb is previously connected 
        $facebook_active = $hybridauth->isConnectedWith("Facebook");
        if (!$facebook_active) {
            $facebook_hauthSession = cs_social_get_saved_hauth_session("facebook_hauthSession");
            if (!$facebook_hauthSession) {
                return false;
            }
            $hybridauth->restoreSessionData($facebook_hauthSession);
        }
        $adapter = $hybridauth->getAdapter("Facebook");
        /*
        $data = array(
            'link' => 'http://amiyasahu.com',
            'picture' => 'http://demo.rahularyan.com/cleanstrap/qa-theme/cleanstrap/uploads/ba96c02d08acbfc29b7f5a2685f4f31f.png',
            'name' => 'CleanStrap',
            'caption' => 'Asking for testing this functionality',
            'message' => 'Web Application Developer and Designer (Updated from my application )',
        );*/
        $adapter->setUserStatus($data);
    } catch (Exception $e) {
        // cs_log("Error while posting to facebook " . print_r($e, true));
    }
}


function cs_post_to_twitter($message) {
    require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
    require_once CS_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';
    
    try {
        $loginCallback = qa_path_absolute(qa_opt('site_url'), array());
        $config = cs_social_get_config_common($loginCallback, 'Twitter');
        $hybridauth = new Hybrid_Auth($config);
        $twitter_active = $hybridauth->isConnectedWith("Twitter");
        if (!$twitter_active) {
            $twitter_hauthSession = cs_social_get_saved_hauth_session("twitter_hauthSession");
            if (!$twitter_hauthSession) {
                return false;
            }
            $hybridauth->restoreSessionData($twitter_hauthSession);
        }
        $adapter = $hybridauth->getAdapter("Twitter");
       
        $adapter->setUserStatus($message);
    } catch (Exception $e) {
        // cs_log("Error while posting to twitter " . print_r($e, true));
    }
}
