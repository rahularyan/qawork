<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
		"qw_twitter_id_label"      => "Twitter ID:",
		"qw_twitter_t_count_label" => "Number of latest tweets:",
		"qw_twitter_ck_label"      => "Consumer key:",
		"qw_twitter_qw_label"      => "Consumer secret:",
		"qw_twitter_at_label"      => "Access token:",
		"qw_twitter_ts_label"      => "Access Token Secret:",
	);