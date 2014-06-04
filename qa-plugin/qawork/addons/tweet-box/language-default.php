<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
		"cs_twitter_id_label"      => "Twitter ID:",
		"cs_twitter_t_count_label" => "Number of latest tweets:",
		"cs_twitter_ck_label"      => "Consumer key:",
		"cs_twitter_cs_label"      => "Consumer secret:",
		"cs_twitter_at_label"      => "Access token:",
		"cs_twitter_ts_label"      => "Access Token Secret:",
	);