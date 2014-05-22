<?php
/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

function cs_social_get_saved_hauth_session($hauthSession)
{
   return qa_db_read_one_value(qa_db_query_sub("SELECT ^userprofile.content AS name from  ^userprofile WHERE ^userprofile.title =$ AND ^userprofile.userid = # " , $hauthSession , qa_get_logged_in_userid()), true);
}