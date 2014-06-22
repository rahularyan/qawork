<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}

function qw_set_me_online(){
      if(qa_is_logged_in())
            qa_db_query_sub(
                  'REPLACE ^ra_who_online (userid, last_seen) VALUES (#, CURRENT_TIMESTAMP)',
                   qa_get_logged_in_userid()
            );
}

function qw_is_user_online($userid){
      qa_db_read_one_value(qa_db_query_sub(
            'SELECT count(*) FROM ^ra_who_online WHERE userid = # AND last_seen  < CURRENT_TIMESTAMP - 30',
            $userid
      ));
}

function qw_who_is_online(){
      qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT userid FROM ^ra_who_online last_seen  < CURRENT_TIMESTAMP - 30'
      ) , true );
}

function qw_check_user_last_seen($userid= null){
      if(!$userid)
            $userid= qa_get_logged_in_userid();
      qa_db_read_one_value(qa_db_query_sub(
            'SELECT last_seen FROM ^ra_who_online WHERE userid = # ',
            $userid
      ));
}

function qw_db_get_all_conversations($userid= null){
      if(!$userid)
            $userid= qa_get_logged_in_userid();
      return qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT messageid, type,fromuserid,touserid,content,format,UNIX_TIMESTAMP(created) as created,`read` FROM ^messages WHERE type = # AND (fromuserid = #  OR touserid=#) ORDER BY created ',
            'PRIVATE' , $userid , $userid
      ));
}

function qw_db_get_all_conversations_betw($fir_user , $sec_user){
      if(!$userid)
            $userid= qa_get_logged_in_userid();
      return qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT messageid, type,fromuserid,touserid,content,format,UNIX_TIMESTAMP(created) as created,`read` FROM ^messages WHERE type = # AND ((fromuserid = #  AND touserid = #) OR (fromuserid = #  AND touserid = #)) ORDER BY created ',
            'PRIVATE' , $fir_user , $sec_user, $sec_user , $fir_user
      ));
}

function qw_set_all_conversations_as_read($userid= null){
      if(!$userid)
            $userid= qa_get_logged_in_userid();
      qa_db_query_sub(
            'UPDATE ^messages SET `read` = 1 WHERE touserid=# ',
            $userid
      );
}

function qw_get_name_handle_of_users($userids) {
      return qa_db_read_all_assoc(
            qa_db_query_sub(
                  "SELECT ^users.handle as handle, ^users.userid as userid  
                  from  ^users 
                  WHERE ^users.userid in (#) ",
                   $userids));
}


