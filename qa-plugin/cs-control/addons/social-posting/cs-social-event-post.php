<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}

// to register the event 
cs_event_hook('a_post_social_post', NULL, 'cs_post_to_facebook');
cs_event_hook('q_post_social_post', NULL, 'cs_post_to_facebook');

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
                   cs_post_to_facebook($data) ;
                   break;
               default:
                   # code...
                   break;
           }
        }
    }
}

function cs_post_to_facebook($data) {
    require_once CS_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
    require_once CS_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';
    
    try {
        $loginCallback = qa_path_absolute(qa_opt('site_url'), array());
        $config = cs_social_get_config_common($loginCallback, 'Facebook');
        $hybridauth = new Hybrid_Auth($config);
        $facebook_active = $hybridauth->isConnectedWith("Facebook");
        if (!$facebook_active) {
            $facebook_hauthSession = cs_social_get_saved_hauth_session("facebook_hauthSession");
            if (!$facebook_hauthSession) {
                return false;
            }
            $hybridauth->restoreSessionData($facebook_hauthSession);
        }
        $adapter = $hybridauth->getAdapter("Facebook");
        $parameter = array();
        if (is_array($data)) {
            if (isset($data['link'])) {
                 $parameter['link'] = $data['link'] ;
            }
            if (isset($data['picture'])) {
                 $parameter['picture'] = $data['picture'] ;
            }
            if (isset($data['name'])) {
                 $parameter['name'] = $data['name'] ;
            }
            if (isset($data['caption'])) {
                 $parameter['caption'] = $data['caption'] ;
            }
            if (isset($data['message'])) {
                 $parameter['message'] = $data['message'] ;
            }
            if (isset($data['description'])) {
                 $parameter['description'] = $data['description'] ;
            }
        }else {
            $parameter = $data ; 
        }
        /*$parameter = array(
            'link' => 'http://amiyasahu.com',
            'picture' => 'http://amiyasahu.com/assets/img/amiya.jpg',
            'name' => 'Amiya Sahu',
            'caption' => 'Amiya Sahu',
            'message' => 'Web Application Developer and Designer (Updated from my application )',
        );*/
        $adapter->setUserStatus($parameter);
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
