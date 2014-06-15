<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

return array(
	"a_posted" => "I have answered this question on ^site_title " ,
	"q_asked" => "I have asked this question on ^site_title " ,
	"c_posted" => "I have commented this question on ^site_title " ,
	// for social Posting 
	'my_social_posting_nav'    => "Social Posting" ,
	'social_posting_title'     => "Social Posting" ,
	'qw_facebook_q_post_lable' => "Post to Facebook When I ask a new Question" ,
	'qw_facebook_a_post_lable' => "Post to Facebook When I answer a Question" ,
	'qw_facebook_c_post_lable' => "Post to Facebook When I post a new Comment" ,
	'qw_twitter_q_post_lable'  => "Post to Twitter When I ask a new Question" ,
	'qw_twitter_a_post_lable'  => "Post to Twitter When I answer a Question" ,
	'qw_twitter_c_post_lable'  => "Post to Twitter When I post a new Comment" ,
	'save_settings' => 'Save Settings' ,
	'settings_saved' => 'Settings has been successfully saved' ,
	'my_social_posting_settings' => 'Choose when to post to social media' ,
	'invite_frnds'           => 'Invite Your Friends ',
	'update_status'          => 'Update your status ',
	'invite_status'          => 'I am using a very helpful QuestionAnswer website ^site_url . Lets join togather and enjoy ',
	'fb_invite_message_default'    => ' invited to join a very helpful QuestionAnswer Website here {site_url} ',
	'update_facebook_status' => 'Update Facebook Status',
	'send_facebook_invite'   => 'Invite Your Facebook Friends',
	'update_twitter_status'  => 'Tweet your followers',
	'status_updated_message' => 'your ^ status is updated successfully',
	'invite_friends' => 'Invite your friends',
	'ask_your_friends' => 'Ask your friends',
	'qw_fb_invite_message_label' => 'Your costum message while inviting friends (^name will be replaced by users name , and ^site_url will be replaced by website URL) ',
	'tell_your_friends' => 'Tell your friends about this site ',
	'message_your_friends' => 'Message your friend',
	'qw_invite_friends' => 'QW Invite Friends',
	'qw_invite_friends' => '"Please provide Facebook application Id to enable this option in Theme Options -> Social Login "',
);
