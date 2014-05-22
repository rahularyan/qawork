<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
	"opt_yes"         => "Yes" ,
	"opt_no"          => "No" ,
	"opt_truncate"    => "Truncate title in breadcrumb if No category exists" ,
	"not_found"       => "Page Not Found" ,
	"recent_que"      => "Recent Questions" ,
	"home"             => "Home" ,
	"hot"             => "Hot!" ,
	"most_votes"      => "Most Votes" ,
	"most_answers"    => "Most Answers" ,
	"most_views"      => "Most Views" ,
	"no_ans"          => "No Answer" ,
	"no_selected_ans" => "No Selected Answer" ,
	"no_upvoted_ans" => "No Upvoted Answer" ,
	"questions"      => "Questions" ,
	"unanswered"     => "Unanswered" ,
	"tags"           => "Tags" ,
	"tag"            => "Tag" ,
	"users"          => "Users" ,
	"user"           => "User" ,
	);