<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

return array(
	'qw_blog_widget' => 'QW Blog Post' ,
);
