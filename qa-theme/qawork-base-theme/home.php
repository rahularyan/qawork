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
				<?php $this->search(); ?>			
				<div class="big-btns">					
					<?php 
						echo cs_get_fb_invite_button();
					?>
				</div>
				<p class="total-site-count align-right">Tell your friends about this site</p>
			</div>
		</div>

		<div class="home-top-users">			
			<div class="container">
				<?php $this->cs_position('Home Top Users'); ?>
			</div>
		</div>
		<div class="home-happening">			
			<div class="container">
				<div class="row">					
					<div class="col-md-8">
						<?php $this->cs_position('Home Right'); ?>							
					</div>
					<div class="col-md-4">
						<?php $this->cs_position('Home Activity'); ?>
					</div>
				</div>
			</div>
		</div>
	<?php
	$this->output(ob_get_clean());