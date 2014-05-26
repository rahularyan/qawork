<?php

/*
	Name:Featured
	Version:1.0
	Author: Rahul Aryan
	Description:Widget for showing users followers list
*/	

if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


qa_register_plugin_module('widget', 'addons/following-widget/widget-following.php', 'cs_following_widget', 'CS Following');