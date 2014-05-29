<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class cs_notification_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		if (qa_is_logged_in() && $request=='notifications')
			return true;

		return false;
	}
	function process_request($request)
	{
		
		$qa_content=qa_content_prepare();		
		$qa_content['site_title']="Notifications";
		$qa_content['error']="";
		$qa_content['suggest_next']="";
		$qa_content['template']="notifications";
		
		$qa_content['custom']= $this->opt_form();
		
		return $qa_content;	
	}
	
	function opt_form(){
		require_once CS_CONTROL_DIR .'/addons/notification/functions.php';
		ob_start();
		?>
			<div id="notifications-page" class="clearfix">
				<a class="mark-activity icon-tick" href="#" data-id="<?php echo qa_get_logged_in_userid() ?> "> <?php echo qa_lang('cleanstrap/mark_all_as_read') ?> </a>
				<?php cs_activitylist(); ?>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

	
	
}

