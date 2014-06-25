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
				<?php if ( qa_opt('qw_enable_search')) $this->search(); ?>			
					<div class="big-btns">					
						<?php 
						if (function_exists('qw_get_fb_invite_button')) {
							echo qw_get_fb_invite_button();
						}
							echo $this->fb_ask_your_friend(qw_current_url(), qa_lang('qw_social_posting/message_your_friends'));
						?>
					</div>
					<p class="total-site-count align-right"><?php echo qa_lang('qw_social_posting/tell_your_friends'); ?></p>
			</div>
		</div>

		<div class="home-top-users">			
			<div class="container">
				<?php $this->qw_position('Home Top Users'); ?>
			</div>
		</div>
		<div class="home-happening">			
			<div class="container">
				<div class="row">					
					<div class="col-md-8">
						<?php $this->qw_position('Home Right'); ?>							
					</div>
					<div class="col-md-4">
						<?php $this->qw_position('Home Activity'); ?>
					</div>
				</div>
				<?php $this->qw_position('Home Bottom'); ?>	
			</div>
		</div>
	<?php
	$this->output(ob_get_clean());