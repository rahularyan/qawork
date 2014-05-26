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
		if ($request=='notifications')
			return true;

		return false;
	}
	function process_request($request)
	{
		
		$qa_content=qa_content_prepare();		
		$qa_content['site_title']="Notifications";
		$qa_content['error']="";
		$qa_content['suggest_next']="";
		
		$qa_content['custom']= $this->opt_form();
		
		return $qa_content;	
	}
	
	function opt_form(){
		require_once CS_CONTROL_DIR .'/addons/notification/functions.php';
		ob_start();
		?>
			<div id="notifications-page" class="col-md-6">
				<a class="mark-activity icon-tick" href="#" data-id="<?php echo qa_get_logged_in_userid() ?> "> <?php echo qa_lang('cleanstrap/mark_all_as_read') ?> </a>
				<?php cs_activitylist(); ?>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
	function cs_install_nav(){
		?>
			<ul class="install-nav">
				<li><a href="#" class="icon-cog">Settings</a></li>
			</ul>
		<?php
	}
	
	
}

