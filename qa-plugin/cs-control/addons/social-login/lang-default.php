<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

return array(
	'my_logins_title'         				=> 'My logins',
	'my_logins_nav'           			=> 'My logins',
	'my_current_user'         			=> 'My current account',
	'associated_logins'       			=> 'Connected accounts',
	'other_logins'            			=> 'Other accounts matching this email address',
	'other_logins_conf_title' 			=> 'Confirm the connected accounts',
	'other_logins_conf_text'  			=> 'We have detected other accounts are using the same email address. If these accounts belong to you, you can link them to your current profile to avoid duplicates.',
	'merge_accounts'          			=> 'Connect selected accounts',
	'merge_accounts_note'     			=> 'Important! The selected logins will be associated with your current profile and their initial profiles will be permanently deleted. Reputation points belonging to these profiles will not be migrated, and previous activity will be marked as annonymous. If you want instead to keep one of the other accounts and remove the one you\'re currently using, log in with that other account and visit this page again.',
	'split_accounts'          			=> 'Disconnect selected accounts',
	'split_accounts_note'     			=> 'Note: once disconnected, your profile will not be associated with those accounts anymore. If you sign in again with those login IDs after disconnecting them, new user accounts will be created for them. You can, however, merge them again with this profile by visiting this page.',
	'no_logins_title'         			=> 'No other connected accounts for your profile',
	'no_logins_text'          			=> 'Log in using an OpenID provider to connect your current profile with other accounts.',
	'local_user'              			=> 'Local user account',
	'login_title'             			=> 'Login through an external provider',
	'login_description'       			=> 'Choose a service provider from the list below to login without creating an account on this site.',
	'remember_me'             			=> 'Keep me signed in when I log in using any of the linked accounts.',
	'login_using'             			=> 'Login using ^',
	'admin_options_heading'             => 'CleanStrap Social Login Preferences',
	'invite_frnds'             			=> 'Invite Friends',
	'update_status'             		=> 'Update status',
	'update_status'             		=> 'Update status',
	
);
