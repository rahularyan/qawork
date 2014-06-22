<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_blog_post_settings {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{		
		if ($request=='blog-post-settings')
			return true;

		return false;
	}
	
	function process_request($request){

	}
	
	function page_content($qa_content){
		
	}
	
}

