<?php

/*
  Name:CS Social Login
  Version:1.0
  Author: Amiya Sahu
  Description:For enabling social logins
 */

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

require_once CS_CONTROL_DIR.'/addons/social-posting/cs-social-event-post.php';


class Cs_Social_Posting_Addon {

      function __construct() {
            cs_event_hook('doctype', NULL, array($this, 'navigation'));
            cs_event_hook('register_language', NULL, array($this, 'language'));
      }

      public function language($lang_arr) {
		    $lang_arr['cs_social_login'] = CS_CONTROL_DIR .'/addons/social-login/language.php';
		    return $lang_arr;
      }

}
