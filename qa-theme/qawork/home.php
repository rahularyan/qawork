<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	ob_start();
	?>
		<div class="home-join">			
			<div class="container">
				<div class="row">
					<div class="col-md-8">
						<?php $this->cs_position('Home Count'); ?>
					</div>
					<div class="col-md-4">
						<a href="#" class="btn register">Register</a>
						<?php 
							if (!!qa_opt("facebook_app_id")) {
								  /* if (!isset($fb_root_initiallized) && !$fb_root_initiallized) {
										$themeobject->output('<div id="fb-root"></div>');
										$fb_root_initiallized = true ;
								  } */
								  $on_click_event = cs_generate_facebook_invite_script(qa_opt("facebook_app_id"), array('url' => qa_opt("site_url")))  ;
								  $button = '<button class="btn btn-block btn-facebook" onclick="'.$on_click_event.'">'.qa_lang_html('cs_social_posting/send_facebook_invite').'</button>' ;
								  $themeobject->output($button );
							}else {
								  $themeobject->output("Please provide Facebook application Id to enable this option in Theme Options -> Social Login ");
							}
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="home-featured">			
			<div class="container">
				<?php $this->cs_position('Home Featured'); ?>
			</div>
		</div>
	<?php
	$this->output(ob_get_clean());