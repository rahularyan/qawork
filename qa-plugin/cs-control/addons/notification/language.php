<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
	//adding email notification messages 
	 'greeting' => "Dear ^user_name , \n ",
	'thank_you_message' => "\n\nThank you,\n^site_title" ,
	'notification_email_subject' => "Updates from ^site_title" ,
	//databse snippets to be saved for async email 
	'a_post_body_email' => "^open ^done_by has answered this question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'c_post_body_email' => "^open ^done_by has commented on a post <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_reshow_body_email' => "^open ^done_by has reshown your question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_reshow_body_email' => "^open ^done_by has reshown your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'c_reshow_body_email' => "^open ^done_by has reshown your comment <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_select_body_email' => "^open ^done_by has selected your answer as best answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_vote_up_body_email' => "^open ^done_by has voted up your question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_vote_up_body_email' => "^open ^done_by has voted up your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_vote_down_body_email' => "^open ^done_by has voted down your question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_vote_down_body_email' => "^open ^done_by has voted down your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	//I am not sure about this message 
	'q_vote_nil_body_email' => "^open ^done_by has voted nill your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_vote_nil_body_email' => "^open ^done_by has voted nill your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_approve_body_email' => "^open ^done_by has approved  your question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_approve_body_email' => "^open ^done_by has approved  your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'c_approve_body_email' => "^open ^done_by has approved  your comment <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_reject_body_email' => "^open ^done_by has rejected  your question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'a_reject_body_email' => "^open ^done_by has rejected  your answer <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'c_reject_body_email' => "^open ^done_by has rejected  your comment <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_favorite_body_email' => "^open ^done_by has favorited  your question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	'q_post_body_email' => "^open ^done_by has posted a new question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",
	// these four things have to be tested properly
	'u_favorite_body_email' => "^open ^done_by is now following you \n",
	'u_message_body_email' => "^open ^done_by has sent a private message ^q_content^close\n\n<a href='^url'>Click  here </a> to reply \n",
	'u_wall_post_body_email' => "^open ^done_by has posted on your wall ^q_content^close\n\n<a href='^url'>Click  here </a> to view \n",
	'u_level_body_email' => "^open ^done_by has posted on your wall ^q_content^close\n\n<a href='^url'>Click  here </a> to view \n",
	'related_body_email' => "^open ^done_by has posted a related question question <a href='^url'> ^q_title </a> \n\n^q_content^close\n\n<a href='^url'>Click  here </a> to see the question\n",

	//subject headers
	'a_post_email_header' => "New answers on questions : \n\n",
	'c_post_email_header' => "New comments :\n\n",
	'q_reshow_email_header' => "Questions Reshows  :\n\n",
	'a_reshow_email_header' => "Answer Reshows  :\n\n",
	'c_reshow_email_header' => "Comment Reshows  :\n\n",
	'a_select_email_header' => "Answers selected :\n\n",
	'q_vote_up_email_header' => "Question VoteUps:\n\n",
	'a_vote_up_email_header' => "Answer VoteUps:\n\n",
	'q_vote_down_email_header' => "Question VoteDowns:\n\n",
	'a_vote_down_email_header' => "Answer VoteDowns:\n\n",
	'q_vote_nil_email_header' => "Question VoteNills:\n\n",
	'a_vote_nil_email_header' => "Answer VoteNills:\n\n",
	'q_approve_email_header' => "Questions Approved :\n\n",
	'a_approve_email_header' => "Answers Approved :\n\n",
	'c_approve_email_header' => "Comments Approved :\n\n",
	'q_reject_email_header' => "Questions Rejected :\n\n",
	'a_reject_email_header' => "Answers Rejected :\n\n",
	'c_reject_email_header' => "Comments Rejected :\n\n",
	'q_favorite_email_header' => "Questions marked as favorite  :\n\n",
	'q_post_email_header' => "New Question Posted :\n\n",
	'u_favorite_email_header' => "You have new followers :\n\n",
	'u_message_email_header' => "You have new Messages :\n\n",
	'u_wall_post_email_header' => "You have new stuffs on your wall :\n\n",
	'u_level_email_header' => "Level Improvements :\n\n",
	'related_email_header' => "Related Questions :\n\n",
);