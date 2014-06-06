<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}

/*
  Some part of this code was taken from qa-openlogin plugin by alixandru
  https://github.com/alixandru/q2a-open-login
 */

function qw_db_user_login_find_other($userid, $email, $additional = 0) {
    // return all logins with the same email OR which are associated with the specified user ids
    // super admins will not be included

    /* create a hierarchical structure like this: 
      [id] {
      details: [data from user table]
      logins: [multiple records from userlogins table]
      }
     */
    if (!empty($email)) $logins = qa_db_read_all_assoc(qa_db_query_sub(
                        'SELECT us.*, up.points, ul.identifier, ul.source, ul.oemail as uloemail FROM ^users us 
        LEFT JOIN ^userpoints up ON us.userid = up.userid 
        LEFT JOIN ^userlogins ulf ON us.userid = ulf.userid 
        LEFT JOIN ^userlogins ul ON us.userid = ul.userid 
        WHERE (us.oemail=$ OR us.email=$ OR ulf.oemail=$) AND us.level<=$', $email, $email, $email, 100
        ));
    else if (!empty($userid)) $logins = qa_db_read_all_assoc(qa_db_query_sub(
                        'SELECT us.*, up.points, ul.identifier, ul.source, ul.oemail as uloemail FROM ^users us 
        LEFT JOIN ^userlogins ul ON us.userid = ul.userid 
        LEFT JOIN ^userpoints up ON us.userid = up.userid 
        WHERE (us.userid=$ OR us.userid=$) AND us.level<=$', $userid, $additional, 100
        ));
    else return array();

    $ret = array();
    foreach ($logins as $l) {
        $id = $l['userid'];

        if (isset($ret[$id])) {
            $structure = $ret[$id];
        } else {
            $structure = array();
            $structure['logins'] = array();
            $structure['details'] = array(
                'userid' => $id,
                'handle' => $l['handle'],
                'points' => $l['points'],
                'email' => $l['email'],
                'oemail' => $l['uloemail'],
            );
        }

        if (!empty($l['identifier'])) {
            $structure['logins'][] = ucfirst($l['source']); // push this new login
        }

        $ret[$id] = $structure;
    }

    return $ret;
}

function qw_db_user_login_find_duplicate($source, $id) {
    // return the login with the specified source and id
    $duplicates = qa_db_read_all_assoc(qa_db_query_sub(
                    'SELECT * FROM ^userlogins WHERE source=$ and identifier=$', $source, $id
    ));
    if (empty($duplicates)) {
        return null;
    } else {
        return $duplicates[0];
    }
}

function qw_db_user_login_find_mine($userid) {
    // return all logins associated with this user
    return qa_db_read_all_assoc(qa_db_query_sub(
                    'SELECT * FROM ^userlogins WHERE userid=$', $userid
    ));
}

function qw_db_user_login_set($source, $identifier, $field, $value) {
    // update an arbitrary field on userlogins table
    qa_db_query_sub(
            'UPDATE ^userlogins SET ' . qa_db_escape_string($field) . '=$ WHERE source=$ and identifier=$', $value, $source, $identifier
    );
}

function qw_db_user_login_replace_userid($olduserid, $newuserid) {
    // replace the userid in userlogins table
    qa_db_query_sub(
            'UPDATE ^userlogins SET userid=$ WHERE userid=$', $newuserid, $olduserid
    );
}

function qw_db_user_login_delete($source, $identifier, $userid) {
    // delete an user login
    qa_db_query_sub(
            'DELETE FROM ^userlogins WHERE source=$ and identifier=$ and userid=$', $source, $identifier, $userid
    );
}

function qw_db_user_find_by_email_or_oemail($email) {
    // Return the ids of all users in the database which match $email (should be one or none)
    if (empty($email)) {
        return array();
    }

    return qa_db_read_all_values(qa_db_query_sub(
                    'SELECT userid FROM ^users WHERE email=$ or oemail=$', $email, $email
    ));
}

function qw_db_user_find_by_id($userid) {
    // Return the user with the specified userid (should return one user or null)
    $users = qa_db_read_all_assoc(qa_db_query_sub(
                    'SELECT us.*, up.points FROM ^users us 
      LEFT JOIN ^userpoints up ON us.userid = up.userid 
      WHERE us.userid=$', $userid
    ));
    if (empty($users)) {
        return null;
    } else {
        return $users[0];
    }
}

function qw_open_login_get_new_source($source, $identifier) {
    // return a new session source containing the actual open id provider and the 
    // user identifier. This string represents a unique combination of userid and 
    // openid-provider, allowing for more than one account from the same openid
    // provider to be linked to an Q2A user (ie. a QA user can have 2 Facebook 
    // accounts linked to it)
    return substr($source, 0, 9) . '-' . substr(md5($identifier), 0, 6);
}

function qw_save_user_hauth_session($userid , $provider , $session )
{
     if (!!$provider && strlen($session)) {
        require_once QA_INCLUDE_DIR . 'qa-db-users.php';
        $profile_field = strtolower($provider) . "_hauthSession";
        qa_db_user_profile_set($userid, $profile_field, $session);
    }
}

function qw_social_get_config_common($url, $provider) {
    $key = strtolower($provider);
    $app_id = qa_opt("{$key}_app_id");
    $app_secret = qa_opt("{$key}_app_secret");

    if (!$app_secret || !$app_id) {
        return null;
    }

    return array(
        'base_url' => $url,
        'providers' => array(
            $provider => array(
                'enabled' => true,
                'keys' => array(
                    'id' => $app_id,
                    'key' => $app_id,
                    'secret' => $app_secret
                ),
                'scope' => $provider == 'Facebook' ? 'email,user_about_me,user_location,user_website' : null,
            )
        ),
        'debug_mode' => false,
        'debug_file' => ''
    );
}

/*
	Omit PHP closing tag to help avoid accidental output
*/