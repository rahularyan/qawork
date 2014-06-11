<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
	//adding email notification messages 
	'greeting'                   => "Dear ^user_name ,  ",
	'thank_you_message'          => "Thank you ,<br/>^site_title" ,
	'notification_email_subject' => "Updates from ^site_title" ,
	//databse snippets to be saved for async email 
	'a_post_body_email'      => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has answered your question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'c_post_body_email'      => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has commented on a post </p> <a class='item-title' href='^url'> ^q_title </a>",
	'q_reshow_body_email'    => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has reshown your question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'a_reshow_body_email'    => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has reshown your answer </p> <a class='item-title' href='^url'> ^q_title </a>",
	'c_reshow_body_email'    => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has reshown your comment </p> <a class='item-title' href='^url'> ^q_title </a>",
	'a_select_body_email'    => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has selected your answer as best answer </p> <a class='item-title' href='^url'> ^q_title </a>",
	'q_vote_up_body_email'   => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has voted up your question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'a_vote_up_body_email'   => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has voted up your answer </p> <a class='item-title' href='^url'> ^q_title </a>",
	'q_vote_down_body_email' => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has voted down your question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'a_vote_down_body_email' => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has voted down your answer </p> <a class='item-title' href='^url'> ^q_title </a>",
	
	'q_vote_nil_body_email' => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has removed his vote from your question </p> <a class='item-title' href='^url'> ",
	'a_vote_nil_body_email' => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has removed his vote from your answer </p> <a class='item-title' href='^url'> ",
	'q_approve_body_email'  => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has approved  your question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'a_approve_body_email'  => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has approved  your answer </p> <a class='item-title' href='^url'> ^q_title </a>",
	'c_approve_body_email'  => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has approved  your comment </p> <a class='item-title' href='^url'> ^q_title </a>",
	'q_reject_body_email'   => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has rejected  your question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'a_reject_body_email'   => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has rejected  your answer </p> <a class='item-title' href='^url'> ^q_title </a>",
	'c_reject_body_email'   => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has rejected  your comment </p> <a class='item-title' href='^url'> ^q_title </a>",
	'q_favorite_body_email' => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has favorited  your question</p><a class='item-title' href='^url'> ^q_title </a><a href='^url' class='event-btn'>View</a>",
	'q_post_body_email'     => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has posted a new question </p> <a class='item-title' href='^url'> ^q_title </a>",

	'u_favorite_body_email'  => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> is now following you ",
	'u_message_body_email'   => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has sent a private message - ^q_content. <a href='^url'>Click  here ",
	'u_wall_post_body_email' => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has posted on your wall - ^q_content. <a href='^url'>Click  here to view </a>",
	'u_level_body_email'     => "^q_content <a href='^url'>Click  here </a> to see your new profile ",
	'u_level_approved_body_email'     => "Congratulations . You profile has been approved",
	'u_level_improved_body_email'     => "Congratulations . ^done_by has selected you as ^new_designation ",
	'u_level_improved_notf'     => " has selected you as ^new_designation ",
	'u_level_approved_notf'     => " has approved your profile",
	'related_body_email'     => "<p class='event-title' style='margin-bottom:0;font-weight:bold'><a href='^author_link'>^done_by</a> has posted a related question question </p> <a class='item-title' href='^url'> ^q_title </a>",
	'u_message_reply_email' => " to reply  </a>  ",
	'u_message_info' => " to view the profile  </a>   ",

	//subject headers
	'a_post_email_header'      => "<h4>New answers on questions : </h4>",
	'c_post_email_header'      => "<h4>New comments :</h4>",
	'q_reshow_email_header'    => "<h4>Questions Reshows :</h4>",
	'a_reshow_email_header'    => "<h4>Answer Reshows :</h4>",
	'c_reshow_email_header'    => "<h4>Comment Reshows :</h4>",
	'a_select_email_header'    => "<h4>Answers selected :</h4>",
	'q_vote_up_email_header'   => "<h4>Question VoteUps :</h4>",
	'a_vote_up_email_header'   => "<h4>Answer VoteUps :</h4>",
	'q_vote_down_email_header' => "<h4>Question VoteDowns :</h4>",
	'a_vote_down_email_header' => "<h4>Answer VoteDowns :</h4>",
	'q_vote_nil_email_header'  => "<h4>Question VoteNills :</h4>",
	'a_vote_nil_email_header'  => "<h4>Answer VoteNills :</h4>",
	'q_approve_email_header'   => "<h4>Questions Approved :</h4>",
	'a_approve_email_header'   => "<h4>Answers Approved :</h4>",
	'c_approve_email_header'   => "<h4>Comments Approved :</h4>",
	'q_reject_email_header'    => "<h4>Questions Rejected :</h4>",
	'a_reject_email_header'    => "<h4>Answers Rejected :</h4>",
	'c_reject_email_header'    => "<h4>Comments Rejected :</h4>",
	'q_favorite_email_header'  => "<h4>Questions marked as favorite  :</h4>",
	'q_post_email_header'      => "<h4>New Question Posted :</h4>",
	'u_favorite_email_header'  => "<h4>You have got some new followers :</h4>",
	'u_message_email_header'   => "<h4>You have some new Messages :</h4>",
	'u_wall_post_email_header' => "<h4>You have new stuffs on your wall :</h4>",
	'u_level_email_header'     => "<h4>Level Improvements :</h4>",
	'related_email_header'     => "<h4>Related Questions :</h4>",
	'q_post_user_fl_email_header' => "<h4>Question from your favorite Users :</h4>",
	'q_post_cat_fl_email_header'  => "<h4>Question from your favorite Categories :</h4>",
	'q_post_tag_fl_email_header'  => "<h4>Question from your favorite Tags :</h4>",

	// option tab content 
	'qw_enable_email_notfn_lang'      => "Enable Email Notfication " ,
	'qw_notify_tag_followers_lang'    => "Send Email to Tag Followers " ,
	'qw_notify_cat_followers_lang'    => "Send Email to Category Followers " ,
	'qw_notify_user_followers_lang'   => "Send Email to User Followers " ,
	'qw_notify_min_points_opt_lang'   => "Enable minimum point to receive email   " ,
	'qw_notify_min_points_val_lang'   => "Minimum Points for users to receive email " ,
	"qw_notify_enable_async_lang"     => "Enable Asyncynchroneous Email (Please do not change it)" ,
	"qw_notify_enable_summerize_email_lang" => "Enable Summerizing Email Functionality (Please do not change it)" ,
	"choose_one_lang"                 => "Select a option " ,
	"once_a_day_lang"                 => "Once a Day",
	"twice_a_day_lang"                => "Twice a Day",
	"four_times_a_day_lang"           => "Four times a Day",
	"six_times_a_day_lang"            => "Six times a Day",
	"eight_times_a_day_lang"          => "Eight times a Day",
	"twelve_times_a_day_lang"         => "Twelve times a Day",
	"sixteen_times_a_day_lang"        => "Sixteen times a Day",
	"twenty_times_a_day_lang"         => "Twenty times a Day",
	"twenty_four_times_a_day_lang"    => "Twenty four times a Day",
	"qw_notify_freq_per_day_opt_lang" => "Choose the frequency of sending notification emails ",
	"qw_all_notification_page_size_lang" => "Number of notifications to be displayed on the notifications page (minimum 15 and maximum 200) ",
	// user designations
	"basic_desg"                      => "Basic User" ,
	"approved_desg"                   => "Approved User" ,
	"expert_desg"                     => "Expert" ,
	"editor_desg"                     => "Editor" ,
	"moderator_desg"                  => "Moderator" ,
	"admin_desg"                      => "Administrator" ,
	"super_admin_desg"                => "Super Administrator" ,
	"notf_settings_user_nav"                => "Notification settings" ,
	"my_notification_settings"                => "Notification settings" ,
	"settings_saved"                => "Your notification settings has been saved " ,
	"save_settings"                => "Save Changes" ,
	"mail_when_a_post_lable"         => "Email me when I get a new answer ",
	"mail_when_related_lable"        => "Email me when someone asks a related question to my answer",
	"mail_when_c_post_lable"         => "Email me when I receive a new comment ",
	"mail_when_q_reshow_lable"       => "Email me when my question is reshown ",
	"mail_when_c_reshow_lable"       => "Email me when my comment is reshown ",
	"mail_when_a_select_lable"       => "Email me when my answer is selected ",
	"mail_when_q_vote_up_lable"      => "Email me when my question gets a up vote ",
	"mail_when_q_vote_down_lable"    => "Email me when my question gets a down vote ",
	"mail_when_a_vote_up_lable"      => "Email me when my answer gets a up vote ",
	"mail_when_a_vote_down_lable"    => "Email me when my answer gets a down vote ",
	"mail_when_q_favorite_lable"     => "Email me when someone adds my question as favorite ",
	"mail_when_u_favorite_lable"     => "Email me when someone starts following me ",
	"mail_when_u_message_lable"      => "Email me when I receive a new private message ",
	"mail_when_u_wall_post_lable"    => "Email me when someone posts on my wall",
	"mail_when_u_level_lable"        => "Email me when admin change my role ",
	"mail_when_q_post_user_fl_lable" => "Email me when my favorite users asks a new question",
	"mail_when_q_post_tag_fl_lable"  => "Email me when my favorite tags have a new question",
	"mail_when_q_post_cat_fl_lable"  => "Email me when my favorite Categories have a new question",
	"mail_when_q_approve_lable"      => "Email me when my question is approved ",
	"mail_when_q_reject_lable"       => "Email me when my question is rejected ",
	"mail_when_a_approve_lable"      => "Email me when my answer is approved ",
	"mail_when_a_reject_lable"       => "Email me when my answer is rejected ",
	"mail_when_c_approve_lable"      => "Email me when my comment is approved ",
	"mail_when_c_reject_lable"       => "Email me when my comment is rejected ",
);