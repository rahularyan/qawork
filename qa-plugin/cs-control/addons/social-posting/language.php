<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

return array(
	"a_posted" => "I have answered this question on ^site_title " ,
	"q_asked" => "I have asked this question on ^site_title " ,
	"c_posted" => "I have commented this question on ^site_title " ,
);
