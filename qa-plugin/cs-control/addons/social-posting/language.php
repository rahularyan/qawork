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
	'cs_facebook_q_post_lable' => "Post to Facebook When I ask a new Question" ,
	'cs_facebook_a_post_lable' => "Post to Facebook When I answer a Question" ,
	'cs_facebook_c_post_lable' => "Post to Facebook When I post a new Comment" ,
	'cs_twitter_q_post_lable'  => "Post to Twitter When I ask a new Question" ,
	'cs_twitter_a_post_lable'  => "Post to Twitter When I answer a Question" ,
	'cs_twitter_c_post_lable'  => "Post to Twitter When I post a new Comment" ,
	'save_settings' => 'Save Settings' ,
	'settings_saved' => 'Settings has been successfully saved' ,
	'my_social_posting_settings' => 'Choose when to post to social media' ,
	'invite_frnds'           => 'Invite Your Friends ',
	'update_status'          => 'Update your status ',
	'invite_status'          => 'I am using a very helpful QuestionAnswer website ^site_url . Lets join togather and enjoy ',
	'facebook_invite_msg'    => '^name invited to join a very helpful QuestionAnswer Website here ^site_url ',
	'update_facebook_status' => 'Update Facebook Status',
	'send_facebook_invite'   => 'Invite Your Facebook Friends',
	'update_twitter_status'  => 'Tweet your followers',
	'status_updated_message' => 'your ^ status is updated successfully',
);
