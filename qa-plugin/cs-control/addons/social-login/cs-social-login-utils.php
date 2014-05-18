<?php
/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

function cs_social_user_login_find_other($userid, $email) {
      // return all logins with the same email which are not associated with this user
      // super admins will not be included

      if (empty($email)) {
            return array();
      }
      return qa_db_read_all_assoc(qa_db_query_sub(
                      'SELECT us.*, ul.identifier, ul.source, ul.oemail as uloemail FROM ^users us 
			LEFT JOIN ^userlogins ul ON us.userid = ul.userid 
			WHERE (us.oemail=$ OR us.email=$) AND us.userid!=$ AND us.level<$', $email, $email, $userid, 100
      ));
}

function cs_social_user_login_find_mine($userid, $srcexcl) {
      // return all logins associated with this user, with a different session source than the one specified
      // if no source is specified, simply return all logins associated with this user

      if (empty($srcexcl)) {
            return qa_db_read_all_assoc(qa_db_query_sub(
                            'SELECT * FROM ^userlogins WHERE userid=$', $userid
            ));
      } else {

            // the source is in the format [provider]-[id]
            $parts = explode('-', $srcexcl, 2);
            $source = $parts[0]; $id = $parts[1] . '%';
            return qa_db_read_all_assoc(qa_db_query_sub(
                            'SELECT * FROM ^userlogins WHERE userid=$ AND ((source!=$) OR (source=$ AND MD5(identifier) NOT LIKE $))', $userid, $source, $source, $id
            ));
      }
}

function cs_social_user_login_set($source, $identifier, $field, $value) {
      // update an arbitrary field on userlogins table
      qa_db_query_sub(
              'UPDATE ^userlogins SET ' . qa_db_escape_string($field) . '=$ WHERE source=$ and identifier=$', $value, $source, $identifier
      );
}

function cs_social_user_login_delete($source, $identifier, $userid) {
      // delete an user login
      qa_db_query_sub(
              'DELETE FROM ^userlogins WHERE source=$ and identifier=$ and userid=$', $source, $identifier, $userid
      );
}

function cs_social_user_find_by_email_or_oemail($email) {
      // Return the ids of all users in the database which match $email (should be one or none)
      if (empty($email)) {
            return array();
      }

      return qa_db_read_all_values(qa_db_query_sub(
                      'SELECT userid FROM ^users WHERE email=$ or oemail=$', $email, $email
      ));
}

function cs_social_user_find_by_id($userid) {
      // Return the user with the specified userid (should return one user or null)
      $users = qa_db_read_all_assoc(qa_db_query_sub(
                      'SELECT * FROM ^users WHERE userid=$', $userid
      ));
      if (empty($users)) {
            return null;
      } else {
            return $users[0];
      }
}

function cs_social_login_get_new_source($source, $identifier) {
      // return a new session source containing the actual open id provider and the 
      // user identifier. This string represents a unique combination of userid and 
      // openid-provider, allowing for more than one account from the same openid
      // provider to be linked to an Q2A user (ie. a QA user can have 2 Facebook 
      // accounts linked to it)
      return substr($source, 0, 9) . '-' . substr(md5($identifier), 0, 6);
}

function cs_social_get_config_common($url, $provider) {
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

// set this global variable to ensure that facebook is initialized only once . 
if (!isset($facebook_js_api_initialized)) {
    $facebook_js_api_initialized = false ; 
}

function cs_init_facebook_js_api($app_id)
{
      if (!$app_id) {
            return "";
      }

      if (!$facebook_js_api_initialized) {
          $facebook_js_api_initialized = true ;
      } else {
          // if facebook api is already initialized then dont initialize it again . 
          return "" ; 
      }
      ob_start();
      ?>
      <script src=""></script>  
      <script>
            FB.init({
                  appId: '<?php echo $app_id; ?>', // or simply set your appid hard coded
                  cookie: true,
                  status: true,
                  xfbml: true
            });
      </script>
      <?php
      $output = ob_get_clean();
      return $output;
}

/**
 * Generates a dynamic script for users so that they can post to facebook with some ajax calls 
 * and no need of reloading web pages 
 * @param  string $app_id [facebook application id ]
 * @param  string $name   [name of the logged in user]
 * @param  string $url    [url to be used for message ]
 * @return string         [script to be printed below the button]
 */
function cs_generate_facebook_invite_script($app_id, $data , $no_script = true ) {
      if (!$app_id || !is_array($data)) {
            return "";
      }
      $name    = cs_extract_parameter_val($data , 'name') ;
      $url     = cs_extract_parameter_val($data , 'url') ;
      $message = strtr(qa_lang("cs_social_login/facebook_invite_msg") , array('^name'=> $name , '^site_url' => $url ));
      $object  = "message:'$message' ," ;
      ob_start();
      if (!$no_script) echo "<script>" ;
      ?>
     cs_invite_facebook_friends(<?php echo $app_id ?> , {<?php echo $object ?>})
      <?php
      if (!$no_script) echo "</script>" ;
      $output = ob_get_clean();
      return $output;
} //end of cs_generate_facebook_invite_script



/**
 * generate wall post for 
 * @return [type] [description]
 */
function cs_generate_facebook_wall_post_script($app_id , $data , $no_script = true ){
     if (!$app_id || !is_array($data)) {
            return "";
      }
      $link        = cs_extract_parameter_val($data , 'link');
      $picture     = cs_extract_parameter_val($data , 'picture');
      $name        = cs_extract_parameter_val($data , 'name');
      $caption     = cs_extract_parameter_val($data , 'caption');
      $description = cs_extract_parameter_val($data , 'description');
      $object      = "" ;

      if (!!$link) {
        $object .= "link: '" . $link . "' ," ;
      }
      if (!!$picture) {
        $object .= "picture: '" . $picture . "' ," ;
      }
      if (!!$name) {
        $object .= "name: '" . $name . "' ," ;
      }
      if (!!$caption) {
        $object .= "caption: '" . $caption . "' ," ;
      }
      if (!!$description) {
        $object .= "description: '" . $description . "' ," ;
      }
      ob_start();
      if (!$no_script) echo "<script>" ;
      ?>
            cs_post_to_facebook_wall(<?php echo $app_id ?> , {<?php echo $object ?>}); 
      <?php
      if (!$no_script) echo "</script>" ;
      $output = ob_get_clean();
      return $output;
}//cs_generate_facebook_wall_post_script

function cs_generate_facebook_link_share_script($app_id , $data , $no_script = true){
     if (!$app_id) {
            return "";
      }
      $name   = cs_extract_parameter_val($data , 'name');
      $link   = cs_extract_parameter_val($data , 'link');
      $object = "" ;

      if (!!$name) {
        $object .= "name: '" . $name . "' ," ;
      }
      if (!!$link) {
        $object .= "link: '" . $link . "' ," ;
      }
     
      ob_start();
      if (!$no_script) echo "<script>" ;
      ?>
            cs_share_link_to_facebook(<?php echo $app_id ?> ,{<?php echo $object ?>});
      <?php
      if (!$no_script) echo "</script>" ;
      $output = ob_get_clean();
      return $output;
}//cs_generate_facebook_link_share_script

function cs_extract_parameter_val($param , $name )
{
  return isset($param[$name]) ? $param[$name] : "" ;
}
/*
	Omit PHP closing tag to help avoid accidental output
*/