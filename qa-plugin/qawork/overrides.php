<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


function qa_set_request($request, $relativeroot, $usedformat=null)
{
	global $qa_request, $qa_root_url_relative, $qa_used_url_format;
	
	$qa_request=$request;
	$qa_root_url_relative=$relativeroot;
	$qa_used_url_format=$usedformat;
	
	qw_do_action('init');
}

function qa_get_request_content()
{
	qw_do_action('before_content');
	$requestlower=strtolower(qa_request());
	$requestparts=qa_request_parts();
	$firstlower=strtolower($requestparts[0]);
	$routing=qa_page_routing();
	
	if (isset($routing[$requestlower])) {
		qa_set_template($firstlower);
		$qa_content=require QA_INCLUDE_DIR.$routing[$requestlower];

	} elseif (isset($routing[$firstlower.'/'])) {
		qa_set_template($firstlower);
		$qa_content=require QA_INCLUDE_DIR.$routing[$firstlower.'/'];
		
	} elseif (is_numeric($requestparts[0])) {
		qa_set_template('question');
		$qa_content=require QA_INCLUDE_DIR.'qa-page-question.php';

	} else {
		qa_set_template(strlen($firstlower) ? $firstlower : 'qa'); // will be changed later
		$qa_content=require QA_INCLUDE_DIR.'qa-page-default.php'; // handles many other pages, including custom pages and page modules
	}

	if ($firstlower=='admin') {
		$_COOKIE['qa_admin_last']=$requestlower; // for navigation tab now...
		setcookie('qa_admin_last', $_COOKIE['qa_admin_last'], 0, '/', QA_COOKIE_DOMAIN); // ...and in future
	}
	$passing_params = array('content' => @$qa_content, 'qin' => @$qin, 'in' => @$in, 'questionid' => @$questionid);
	qw_do_action('after_content', $passing_params);
	qa_set_form_security_key();
	
	return $qa_content;
}

function qa_redirect_raw($url)
{
	if(qa_clicked('q_dosave')) return;
	if(qa_clicked('doask')) return;
	header('Location: '.$url);
	qa_exit('redirect');
}

function qa_set_user_avatar($userid, $imagedata, $oldblobid=null){

	if (!empty($_FILES['file'])) {
		//require_once QA_INCLUDE_DIR.'qa-util-image.php';
		require_once QW_CONTROL_DIR.'/inc/class_images.php';
		$thumb = new Image($_FILES['file']['tmp_name']);
		$thumb->resize(200, 200, 'crop', 'c', 'c', 100);
		$imagedata=$thumb->get_image_content();
	}

	if (isset($imagedata)) {
		require_once QA_INCLUDE_DIR.'qa-app-blobs.php';

		$newblobid=qa_create_blob($imagedata, 'jpeg', null, $userid, null, qa_remote_ip_address());

		if (isset($newblobid)) {
		qa_db_user_set($userid, 'avatarblobid', $newblobid);
		qa_db_user_set($userid, 'avatarwidth', $width);
		qa_db_user_set($userid, 'avatarheight', $height);
		qa_db_user_set_flag($userid, QA_USER_FLAGS_SHOW_AVATAR, true);
		qa_db_user_set_flag($userid, QA_USER_FLAGS_SHOW_GRAVATAR, false);

		if (isset($oldblobid))
			qa_delete_blob($oldblobid);

			return true;
		}
	}

	return false;
}