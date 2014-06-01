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

/**
 * Extends the logout functionality by performing some extra clean up for
 * the external login modules.
 *
 * Currently only the login modules using OAuth (like Facebook and Github)
 * need additional logout processing (defined in their do_logout methods). 
 * The OpenID-based login modules will simply use the default functionality.
 *
 * Only logout functionality is affected by this function. Login mechanism
 * is handled by #qa_log_in_external_user function.
 */
function qa_set_logged_in_user($userid, $handle='', $remember=false, $source=null)
{
	// if a logout was requested, do extra stuff
	if (!isset($userid)) {
		// get all modules which have a custom logout logic
		$loginmodules=qa_load_modules_with('login', 'do_logout');

		// do the work
		foreach ($loginmodules as $module) {
			$module->do_logout();
		}
	}
	
	// then just perform the default tasks
	qa_set_logged_in_user_base($userid, $handle, $remember, $source);
}



/**
 * Overrides the default mechanism of logging in from external sources.
 *
 * Adds a different way of tracking the sessions and performs some 
 * additional tasks when creating an user account (setting new fields,
 * extra checks, etc).
 */
function qa_log_in_external_user($source, $identifier, $fields)
{
	require_once QA_INCLUDE_DIR.'qa-db-users.php';
	$remember = qa_opt('open_login_remember') ? true : false;
	
	$users=qa_db_user_login_find($source, $identifier);
	$countusers=count($users);
	
	/*
	 * To allow for more than one account from the same openid/openauth provider to be 
	 * linked to an Q2A user, we need to override the way session source is stored
	 * Supposing userid 01 is linked to 2 yahoo accounts, the session source will be
	 * something like 'yahoo-xyz' when logging in with the first yahoo account and
	 * 'yahoo-xyt' when logging in with the other.
	 */
	
	$aggsource = cs_social_login_get_new_source($source, $identifier);
	
	if ($countusers>1)
		qa_fatal_error('External login mapped to more than one user'); // should never happen
	
	if ($countusers) // user exists so log them in
		qa_set_logged_in_user($users[0]['userid'], $users[0]['handle'], $remember, $aggsource);
	
	else { // create and log in user
		require_once QA_INCLUDE_DIR.'qa-app-users-edit.php';
		
		qa_db_user_login_sync(true);
		
		$users=qa_db_user_login_find($source, $identifier); // check again after table is locked
		
		if (count($users)==1) {
			qa_db_user_login_sync(false);
			qa_set_logged_in_user($users[0]['userid'], $users[0]['handle'], $remember, $aggsource);
		
		} else {
			$handle=qa_handle_make_valid(@$fields['handle']);
		
			// check if email address already exists
			$oemail = null;
			$emailusers = array();
			if (strlen(@$fields['email']) && $fields['confirmed']) { // only if email is confirmed
				$oemail = $fields['email'];
				$emailusers=cs_social_user_find_by_email_or_oemail($fields['email']);
				
				if (count($emailusers)) {
					// unset regular email to prevent duplicates
					unset($fields['email']); 
				}
			}
			
			$userid=qa_create_new_user((string)@$fields['email'], null /* no password */, $handle,
				isset($fields['level']) ? $fields['level'] : QA_USER_LEVEL_BASIC, @$fields['confirmed']);
			
			qa_db_user_set($userid, 'oemail', $oemail);
			qa_db_user_login_add($userid, $source, $identifier);
			cs_social_user_login_set($source, $identifier, 'oemail', $oemail);
			qa_db_user_login_sync(false);
			
			$profilefields=array('name', 'location', 'website', 'about');
			
			foreach ($profilefields as $fieldname)
				if (strlen(@$fields[$fieldname]))
					qa_db_user_profile_set($userid, $fieldname, $fields[$fieldname]);
					
			if (strlen(@$fields['avatar']))
				qa_set_user_avatar($userid, $fields['avatar']);
					
			qa_set_logged_in_user($userid, $handle, $remember, $aggsource);
			
			return count($emailusers);
		}
	}
	
	return 0;
}


/*
	Omit PHP closing tag to help avoid accidental output
*/