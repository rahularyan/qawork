<?php


//	Ensure no PHP errors are shown in the Ajax response
	//@ini_set('display_errors', 0);


//	Load the Q2A base file which sets up a bunch of crucial functions
	require_once '../../qa-include/qa-base.php';
	

//	Get general Ajax parameters from the POST payload, and clear $_GET
	qa_set_request(qa_post_text('qa_request'), qa_post_text('qa_root'));

	require_once QA_INCLUDE_DIR.'qa-app-options.php';
	require_once CS_CONTROL_DIR.'/inc/minify.php';
	
	$cs_minify = new Cs_Minify_class;
	
	if(cs_request_text('type') == 'css'){
		header("content-type: text/css; charset: utf-8");
		header("cache-control: must-revalidate");
		$offset = 365 * 24 * 60 * 60;
		$expire = "expires: ".gmdate("D, d M Y H:i:s", time() + $offset)." GMT";
		header($expire);
		
		if(!ob_start("ob_gzhandler")) ob_start();
		
		$hooked_css 	= cs_get_all_styles('gzip');
		
		if (isset($hooked_css))
		foreach ($hooked_css as $css_src){
			$base = preg_replace('/\?.*/', '', substr(strrchr($css_src, '.'), 1));
			if(cs_is_internal_link($css_src) && $base == 'css'){
				$path =parse_url($css_src, PHP_URL_PATH);
				if(file_exists($_SERVER['DOCUMENT_ROOT'].$path))
					echo $cs_minify->cs_compress_css(file_get_contents($_SERVER['DOCUMENT_ROOT'].$path), $css_src);
			}
		}
		ob_flush();
		
	}elseif(cs_request_text('type') == 'js'){
		header("content-type: text/javascript; charset: UTF-8");
		header("cache-control: must-revalidate");
		$offset = 365 * 24 * 60 * 60;
		$expire = "expires: ".gmdate("D, d M Y H:i:s", time() + $offset)." GMT";
		header($expire);
		if(!ob_start("ob_gzhandler")) ob_start();
		
		$hooked_script 	= cs_get_all_scripts('gzip');

		if (isset($hooked_script))
		foreach ($hooked_script as $src){
			$base = preg_replace('/\?.*/', '', substr(strrchr($src, '.'), 1));
			if(cs_is_internal_link($src) && $base == 'js'){
				$path =parse_url($src, PHP_URL_PATH);
				if(file_exists($_SERVER['DOCUMENT_ROOT'].$path))
					echo $cs_minify->cs_compress_js($_SERVER['DOCUMENT_ROOT'].$path);
			}
		}
		
		ob_flush();
	}else{
		echo 'trying to cheat ?';
	}

	?>

	

	



