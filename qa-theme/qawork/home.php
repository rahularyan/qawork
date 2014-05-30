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
						<a href="#" class="btn register">Register</a>
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