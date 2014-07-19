<?php

/*
	Name:Media
	Version:1.0
	Author: Rahul Aryan
	Description:For adding media in question and answer
*/	

if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


function qw_allow_upload(){
	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)
		return false;
		
	require_once QA_INCLUDE_DIR.'qa-app-limits.php';
	switch (qa_user_permit_error(null, QA_LIMIT_UPLOADS))
	{
		case 'limit':
			$result['error']=qa_lang('main/upload_limit');
			$result['status']=false;
			return $result;
			
		
		case false:
			qa_limits_increment(qa_get_logged_in_userid(), QA_LIMIT_UPLOADS);
			break;

		default:
			$result['error']=qa_lang('users/no_permission');
			$result['status']=false;
			return $result;
	}
	
	$minpoints = qa_opt('qw_qa_editor_min_user_points');

	if(!empty($minpoints) && 
		$minpoints < qa_get_logged_in_points()){
		return false;
	}else{
		$result = array();
		$result['error']=qa_lang('cleanstrap/no_enough_points');
		$result['status']=false;
		return $result;
	}
	
	$minlevel = qa_opt('qw_editor_min_user_level_to_upload');
	if(!empty($minlevel) && 
		$minlevel <= qa_get_logged_in_level()){
		return false;
	}else{
		$result = array();
		$result['error']=qa_lang('users/no_permission');
		$result['status']=false;
		return $result;
	}
}

function qw_upload_dir(){
	return defined('QA_BLOBS_DIRECTORY') ? QA_BLOBS_DIRECTORY : QA_BASE_DIR.'images';
}
function qw_upload_url(){
	return QW_BASE_URL.'/images';
}

function qw_image_size(){
	return qw_apply_filter('image_size', array());
}

function qw_upload_file($field, $postid){

	if($error = qw_allow_upload())
		return $error;
		
	if (isset($_FILES[$field]) && !empty($_FILES[$field])) {
		
		if($_FILES[$field]['type'] == 'image/jpeg' || $_FILES[$field]['type'] == 'image/jpg' || $_FILES[$field]['type'] == 'image/png' || $_FILES[$field]['type'] == 'image/gif' || $_FILES[$field]['type'] == 'image/x-icon'){
			
			return qw_upload_image($_FILES[$field], $postid);
			
		}else{
			require_once QW_CONTROL_DIR.'/inc/class_upload.php';
			
			$upload = Upload::factory( qw_upload_dir() );
			$upload->file($_FILES[$field]);
			
			$max_size = (int)qa_opt('qw_max_image_file');
	
			if(strlen($max_size) < 1 || $max_size == '0') $max_size = 2;
			//set max. file size (in mb)
			$upload->set_max_file_size($max_size);

			//set allowed mime types
			$upload->set_allowed_mime_types(array('application/pdf', 'application/zip'));
			$results = $upload->upload();
			
			if($results['status']){
				$results['name'] = pathinfo( $results['filename'], PATHINFO_FILENAME);
				$id = qw_insert_media($results['name'], $results['ext'], $postid );
				$results['url'] = qw_upload_url();
				$results['for'] =  qa_post_text('for_item');
				$results['id'] = $id;
			}
			return $results;
		}
	}
}

function qw_file_name($file){
	$ext = pathinfo( $file, PATHINFO_EXTENSION);
	$md5 = md5(time().uniqid());
	return array(
		'file' => $md5.'.'.$ext,
		'name' => $md5,
		'ext' => $ext
	);
}

function qw_upload_image($file, $postid = 0){
	if($error = qw_allow_upload())
		return $error;
		
	//return if not a valid image
	if(!qw_is_image($file['tmp_name'])){
		$name['status'] = false;
		$name['error'] = 'Not valid image';
		return $name;
	}
	$max_size = (int)qa_opt('qw_max_image_size');
	
	if(strlen($max_size) < 1 || $max_size == '0') $max_size = 1;

	if($file['size']> ($max_size*1024*1024)){
		$name['status'] = false;
		$name['error'] = 'File size is bigger then '. $max_size;
		return $name;
	}
	
	$uploaddir = qw_upload_dir().'/';
	$name = qw_file_name($file['name']);
	$temp_name = $name['name'].'.'.$name['ext'];
	move_uploaded_file($file['tmp_name'], $uploaddir.$temp_name);

	//qw_add_action('after_uploading_original_image', $image);
	
	$sizes = qw_image_size();

	if(isset($sizes)){

		foreach($sizes as $k => $s){
			
			$file_name = $name['name'].'_'.$s[0].'x'. $s[1].'.'.$name['ext'];
			$resize = qw_resize_image($uploaddir.$temp_name, $uploaddir.$file_name, $s[0], $s[1]);
			
			if($resize)
				$name[$k] = $name['name'].'_'.$s[0].'x'. $s[1];
				
			//qw_add_action('after_creating_thumb', $image);
		}

	}
	if(!isset($name['thumb'])) $name['thumb'] = $name['name'];
	// insert to DB
	$name['id'] = qw_insert_media($name['name'], $name['ext'], $postid );
	$name['url'] = qw_upload_url();
	$name['status'] = 'true';
	$name['for'] =  qa_post_text('for_item');
	///unlink ($uploaddir.$temp_name); 

	return $name;
}

function qw_upload_cover($file){
	$file = $_FILES[$file];
		//return if not a valid image
	if(!qw_is_image($file['tmp_name'])){
		$name['status'] = false;
		$name['error'] = 'Not valid image';
		return $name;
	}
	$max_size = (int)qa_opt('qw_max_image_size');
	
	if(strlen($max_size) < 1 || $max_size == '0') $max_size = 1;

	if($file['size']> ($max_size*1024*1024)){
		$name['status'] = false;
		$name['error'] = 'File size is bigger then '. $max_size;
		return $name;
	}
	require_once QA_INCLUDE_DIR.'qa-db-users.php';
	
	$uploaddir = qw_upload_dir().'/';
	$name = qw_file_name($file['name']);
	$temp_name = $name['name'].'.'.$name['ext'];
	move_uploaded_file($file['tmp_name'], $uploaddir.$temp_name);	
	qw_resize_image($uploaddir.$temp_name, $uploaddir.$name['name'].'_s.'.$name['ext'], 300, 70);
	qw_add_action('after_uploading_cover', $temp_name);
	
	// insert to DB
	$name['id'] = qw_insert_media($name['name'], $name['ext'], 0 );
	$name['url'] = qw_upload_url();
	$name['status'] = 'true';
	$name['action'] = 'cover';
	
	$prev_file = qw_user_profile(qa_get_logged_in_handle(), 'cover');

	if (!empty($prev_file)){	
		$delete = $uploaddir.'/'.$prev_file;
		if (file_exists($delete)){			
			unlink ($delete);
			$small = explode('.', $prev_file);
			unlink ($uploaddir.'/'.$small[0].'_s.'.$small[1]);
		}
	}
	
	qa_db_user_profile_set(qa_get_logged_in_userid(), 'cover', $name['file']);
	
	return $name;
}


function qw_insert_media($file_name, $type, $postid, $parent =0){
	$userid = qa_get_logged_in_userid();
	qa_db_query_sub(
		'INSERT ^ra_media (type, name, userid, parent, parent_post) VALUES ($, $, #, #, #)',
		$type, $file_name, $userid, $parent, $postid
	);
	return qa_db_last_insert_id();
}

function qw_update_media($id, $title, $description){
	$userid = qa_get_logged_in_userid();
	qa_db_query_sub(
		'UPDATE ^ra_media SET title = $, description = $ WHERE id=$',
		$title, $description, $id
	);
	return qa_db_last_insert_id();
}

/* for deleting media by id */
function qw_delete_media_by_id($id){
	// first delete all media files
	qw_delete_media_files_by_id($id);
	
	qa_db_query_sub(
		'DELETE FROM ^ra_media WHERE id= #',
		$id
	);
	
}

function qw_get_post_media($postid){
	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN){
		$media = qa_db_read_all_assoc(qa_db_query_sub(
			'SELECT * FROM ^ra_media WHERE parent_post = #',
			$postid
		));
	}else{
		$userid = qa_get_logged_in_userid();
		$media = qa_db_read_all_assoc(qa_db_query_sub(
			'SELECT * FROM ^ra_media WHERE parent_post = # and userid = #',
			$postid, $userid
		));
	}

	return $media;
}
function qw_get_media_by_id($id){
	if(is_array($id)){
		$ids = implode(',', $id);
		if(!empty($ids)){
			$media = qa_db_read_all_assoc(qa_db_query_sub(
				'SELECT * FROM ^ra_media WHERE id IN ('.$ids.')'			
			), 'id');

			return $media;
		}
	}else{
		$media = qa_db_read_one_assoc(qa_db_query_sub(
			'SELECT * FROM ^ra_media WHERE id = #',
			$id
		), true);

		return $media;
	}
}

function qw_delete_media_files_by_id($id){
	$m = qw_get_media_by_id($id);
	
	if(count($m) > 0){
		$dir = qw_upload_dir();
		
		if($m['type'] == 'jpg' || $m['type'] == 'jpeg' || $m['type'] == 'png' || $m['type'] == 'gif'){
			// delete main image
			$original = $dir.'/'.$m['name'].'.'.$m['type'];
			if(file_exists($original))			
				unlink($original);
				
			$sizes = qw_image_size();
			if(isset($sizes)){
				foreach($sizes as $s){
					$file = $dir.'/'.$m['name'].'_'.$s[0].'x'.$s[1].'.'.$m['type'];
					if(file_exists($file))
						unlink($file);
				}
			}
			qw_add_action('after_deleting_media_images', $m);
		}else{
			unlink($dir.'/'.$m['name'].'.'.$m['type']);
			qw_add_action('after_deleting_media_files', $m);
		}

	}
}

function qw_post_medias($postid, $size = 'thumb'){
	$medias = qw_get_post_media($postid);
	
	if(count($medias) > 0){
		$dir = qw_upload_url();
		$output = '<ul class="post-attachments clearfix">';
		foreach ($medias as $m){
			if($m['type'] == 'jpg' || $m['type'] == 'jpeg' || $m['type'] == 'png' || $m['type'] == 'gif'){
					$output .= '<li class="attachments load-media-preview" data-id="'.$m['id'].'" data-toggle="modal" data-target="#show_file_preview"><img src="'.qw_media_filename($m, $size).'" /></li>';
			}else{
				$output .= '<li class="attachments load-media-preview" data-id="'.$m['id'].'" data-toggle="modal" data-target="#show_file_preview"><i class="file-icon icon-'.$m['type'].'"></i></li>';
			}
		}
		$output .= '</ul>';
		return $output;
	}	
}

function qw_media_filename($m, $size = false){
	$url = qw_upload_url();
	$dir = qw_upload_dir();
	
	if(isset($m['name']) && isset($m['type'])){
		$file = $m['name'] .($size ? '_'.qw_get_image_size_string($size) : '').'.'. $m['type'];
		if(file_exists($dir.'/'.$file))
			return $url.'/'.$file;
		else
			return $url.'/'.$m['name'] .'.'. $m['type'];
	}
	
	return false;
}


function qw_get_image_size_string($size){
	$sizes = qw_image_size();
	if(isset($sizes[$size]))
		return $sizes[$size][0].'x'.$sizes[$size][1];
	return false;
}
class QW_Media_Addon{
	function __construct(){
		qw_add_filter('init_queries', array($this, 'init_queries'));
		
		qw_event_hook('register_language', NULL, array($this, 'language'));
		
		// hook buttons into head_script
		qw_add_filter('enqueue_scripts', array($this, 'head_script'));
		
		// hook buttons into head_css
		qw_add_filter('enqueue_css', array($this, 'head_css'));
		
		// hook buttons in theme layer
		qw_add_action('doctype', array($this, 'ra_post_buttons'));
		qw_event_hook('qw_ajax_load_upload_modal', NULL, array($this, 'upload_modal'));
		qw_event_hook('qw_ajax_upload_file', NULL, array($this, 'upload_file'));
		qw_event_hook('qw_ajax_load_media_item_edit', NULL, array($this, 'load_media_item_edit'));
		qw_event_hook('qw_ajax_edit_media_item', NULL, array($this, 'edit_media_item'));
		qw_event_hook('qw_ajax_load_media_item', NULL, array($this, 'load_media_item'));
		
		qw_add_filter('image_size', array($this, 'image_size'));

		qw_add_action('footer_bottom', array($this, 'add_preview_modal'));
		
		qw_add_action('qw_theme_option_tab', array($this, 'qw_theme_option_tab'));
		qw_add_action('qw_theme_option_tab_content', array($this, 'qw_theme_option_tab_content'));
		qw_add_action('qw_reset_theme_options', array($this, 'reset_theme_options'));

	}
	public function init_queries($queries, $tableslc){
		$tablename=qa_db_add_table_prefix('ra_media');			
		if (!in_array($tablename, $tableslc)) {

			$queries[] ='
				CREATE TABLE IF NOT EXISTS ^ra_media (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `userid` int(10) NOT NULL,
				  `type` varchar(40) NOT NULL,
				  `name` varchar(128) NOT NULL,
				  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `parent` bigint(20) NULL DEFAULT 0,
				  `parent_post` int(10) NULL DEFAULT NULL,
				  `title` varchar(800) NULL DEFAULT NULL,
				  `description` longtext NULL DEFAULT NULL,
				  PRIMARY KEY (id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
			';			
		}

		return $queries;
	}	
	public function language($lang_arr){
		$lang_arr['qw_media'] = QW_CONTROL_DIR .'/addons/media/language-*.php';
		return $lang_arr;
	}

	public function ra_post_buttons($content){
		$postid = @$content['q_view']['raw']['postid'];
		$postid = isset($postid) ? $postid : 0;
		if ((isset($content['form_q_edit']) || qa_request_part(0) == 'ask')){
			$form = isset($content['form_q_edit']) ? @$content['form_q_edit'] : @$content['form'];
			if(isset($form)){
				$qw_media=array(
					'label' => '<button type="button" class="icon-image btn btn-default open-media-modal" data-args="'.$postid.'" data-for="editor">'.qa_lang_html('qw_media/media').'</button>',
					'type' => 'custom',
				);
				if(isset($content['form_q_edit']))
					$content['form_q_edit']['fields'] = qw_array_insert_before('content', $form['fields'], 'qw_media', $qw_media );
				
				if(qa_request_part(0) == 'ask')
					$content['form']['fields'] = qw_array_insert_before('content', $form['fields'], 'qw_media', $qw_media );
			
				return $content;
			}			
		}
	}
	
	function upload_modal(){

		//if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN){
		$postid = (int)qa_post_text('args');
		$for_item = qa_post_text('for_item');
		?>
			<!-- Modal -->
		<div class="modal fade" id="media-modal-<?php echo $postid; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo qa_lang_html('qw_media/add_media'); ?></h4>
			  </div>
			  <div class="modal-body">
				<div class="row">
					<div class="col-sm-7 edit-files-list">
						<ul class="editable-media clearfix">
							<?php 
								$medias = qw_get_post_media($postid);
		
								if(count($medias) > 0){
									foreach ($medias as $m){
										if($m['type'] == 'jpg' || $m['type'] == 'jpeg' || $m['type'] == 'png' || $m['type'] == 'gif'){
											echo '<li class="attachments" data-id="'.$m['id'].'" data-for="'.$for_item.'"><img src="'.qw_media_filename($m, 'thumb').'" /></li>';
										}elseif($m['type'] == 'ico'){
											echo '<li class="attachments" data-id="'.$m['id'].'" data-for="'.$for_item.'"><img src="'.qw_media_filename($m).'" /></li>';
										}else{
											echo '<li class="attachments" data-id="'.$m['id'].'" data-code="'.qa_get_form_security_code('media_'.$m['id']).'" data-for="'.$for_item.'"><i class="file-icon icon-'.$m['type'].'"></i></li>';
										}
									}
								}
							?>
						</ul>
					</div>
					<div class="col-sm-5">
						<!-- Nav tabs -->
						<ul class="nav nav-tabs media-action-tab">
						  <li class="active"><a href="#upload-tab" data-toggle="tab"><?php echo qa_lang_html('qw_media/upload'); ?></a></li>
						  <li><a href="#editmedia-tab" data-toggle="tab"><?php echo qa_lang_html('qw_media/edit'); ?></a></li>
						</ul>

						<!-- Tab panes -->
						<div class="tab-content">
						  <div class="tab-pane active" id="upload-tab">
								<form id="file-upload" method="POST" enctype="multipart/form-data">
									<div class="file-input-wrapper">
										<button class="btn-file-input btn"><?php echo qa_lang_html('qw_media/select_a_file'); ?></button>
										<input id="file-upload-input" name="post_media" type="file" />
									</div>
									<div id="file-preview" class="clearfix"></div>
									<button type="submit" class="btn btn-success"><?php echo qa_lang_html('qw_media/upload'); ?></button>
									<input type="hidden" name="action" value="upload_file">
									<input type="hidden" name="postid" value="<?php echo $postid; ?>">
									<input type="hidden" name="for_item" value="<?php echo $for_item; ?>">
									<input type="hidden" name="code" value="<?php echo qa_get_form_security_code('media_'.$postid ); ?>">
								</form>	
						  </div>
						  <div class="tab-pane" id="editmedia-tab">
							<p><?php echo qa_lang_html('qw_media/select_a_file_to_edit'); ?></p>
						  </div>
						</div>
					</div>				
				</div>
			  </div>
			  
			</div>
		  </div>
		</div>
		<?php
		//}
		die(); 
	}
	
	public function load_media_item_edit(){
		if($error = qw_allow_upload())
			return $error;
		
		$id = (int)qa_post_text('args');
		$for_item = qa_post_text('for_item');
		$media = qw_get_media_by_id($id);
		$media['large'] = qw_media_filename( $media , 'large');

		ob_start();
		?>
			<form class="media-item-form" method="POST">
				<?php 
					if($media['type'] == 'jpg' || $media['type'] == 'jpeg' || $media['type'] == 'png' || $media['type'] == 'gif'){
						$media['url'] = qw_media_filename($media, 'large');
						echo '<img class="file-preview" src ="'.$media['url'].'" />';
					}else{
						$media['url'] = qw_media_filename($media);
						echo '<i class="file-preview file-icon icon-'.$media['type'].'"></i>';
					}
				
				?>
				
				<input class="form-control" type="text" value="<?php echo qw_upload_url().'/'.$media['name'].'.'.$media['type']; ?>" name="url">
				
				<input class="form-control" type="text" name="title" placeholder="<?php echo qa_lang_html('qw_media/title'); ?>" value="<?php echo isset($media['title']) ? $media['title'] : ''; ?>">
				<textarea class="form-control" name="description" placeholder="<?php echo qa_lang_html('qw_media/description'); ?>"><?php echo isset($media['description']) ? $media['description'] : ''; ?></textarea>

				<input type="submit" class="btn btn-success" name="do" value="save">
				<input type="submit" class="btn" name="do" value="delete">
				<input type="submit" class="btn insert-media-to-editor pull-right" value="insert" data-dismiss="modal">
				<input type="hidden" name="type" value="<?php echo $media['type']; ?>">
				<input type="hidden" name="action" value="edit_media_item">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<input type="hidden" name="for" value="<?php echo $for_item; ?>">
				<input type="hidden" name="large" value="<?php echo $media['large']; ?>">
				<input type="hidden" name="thumb" value="<?php echo qw_media_filename( $media , 'thumb'); ?>">
				<input type="hidden" name="code" value="<?php echo qa_get_form_security_code('media_edit_'.$id ); ?>">
			</form>
		<?php
		 $html = ob_get_clean();
		 
		 echo json_encode(array($media, $html));
		
		die();
	}
	
	
	function edit_media_item(){
		$id = qa_post_text('id');
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN && qa_check_form_security_code('media_edit_'.$id, qa_post_text('code'))){
			if(qa_post_text('do') == 'delete'){
				qw_delete_media_by_id($id);
				echo json_encode(array('delete' , '<div class="alert alert-success alert-dismissable">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				  '.qa_lang_html('cleanstrap/media_successfully_deleted').'
				</div>'));

			}elseif(qa_post_text('do') == 'save'){
				qw_update_media($id, qa_post_text('title'), qa_post_text('description'));
				
				echo json_encode(array('save' , '<div class="alert alert-success alert-dismissable">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				  '.qa_lang_html('cleanstrap/media_is_updated').'
				</div>'));
			}
		}
		die();
	}
	
	public function head_script($script_src){		
		$script_src['media_script'] = array('file' => QW_CONTROL_URL . '/addons/media/script.js', 'footer' => true);
		return $script_src;
		
	}
	
	public function head_css($css_src){
		$css_src['qw_media_css'] = array('file' => QW_CONTROL_URL . '/addons/media/styles.css');
		return $css_src;
	}
	
	public function upload_file(){
		if($error = qw_allow_upload()){
			echo json_encode($error);
			die();
		}
		
		$postid = (int)qa_post_text('postid');
		$type = qa_post_text('type');
		
		if($type == 'cover' && qa_check_form_security_code('upload_cover', qa_post_text('code'))){
		
			echo json_encode(qw_upload_cover('cover'));
		
		}elseif(qa_check_form_security_code('media_'.$postid, qa_post_text('code'))){
			$result = qw_upload_file('post_media', $postid);
			echo json_encode($result);
		}else{
			echo '0';
		}
		die();
	}
	
	public function image_size($sizes){
		return array(
			'thumb' => array('80', '80'),
			'large' => array('686', '400'),
		);
	}
	
	public function add_preview_modal(){
		ob_start();
		?>
		<!-- Modal -->
		<div class="modal fade" id="show_file_preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Media</h4>
			  </div>
			  <div class="modal-body"></div>
			</div>
		  </div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function load_media_item(){
		$id = (int)qa_post_text('args');
		
		$media = qw_get_media_by_id($id);
		
		echo '<div class="media-popup">';
		
		if($media['type'] == 'jpeg' || $media['type'] == 'jpg' || $media['type'] == 'png' || $media['type'] == 'gif')
			echo '<img class="file-preview" src="'.qw_media_filename($media).'" />';
		else{
			echo '<i class="file-preview icon-'.$media['type'].'" ></i>';
			echo '<a href="'.qw_media_filename($media).'" class="btn">Download</a>';
		}
		
		if(!empty($media['title']))
			echo '<strong class="media-title">'.$media['title'].'</strong>';
		
		if(!empty($media['description']))
			echo '<p class="media-description">'.$media['description'].'</p>';
			
		echo '</div>';
		
		die();
	}
	public function reset_theme_options() {
	      if (qa_clicked('qw_reset_button')) {
	        qa_opt("qw_max_image_size", 1 );
	        qa_opt("qw_max_image_file", 2 );
	        $saved=true;
	      }
	}
	public function qw_theme_option_tab(){
		 $saved=false;
         if(qa_clicked('qw_save_button')){   
             qa_opt("qw_max_image_size", (int)qa_post_text("qw_max_image_size"));
             qa_opt("qw_max_image_file", (int)qa_post_text("qw_max_image_file"));
             $saved=true;
         }

		return '<li>
				<a href="#" data-toggle=".qa-part-form-tc-media">Media</a>
			</li>';
		
	}
	
	public function qw_theme_option_tab_content(){
		ob_start();
		?>
		<div class="qa-part-form-tc-media">
			<h3>Media manager options</h3>
			<table class="qa-form-tall-table options-table">
				<tbody>
					<tr>
						<th class="qa-form-tall-label">
							Max size of image (MB)
						</th>
						<td class="qa-form-tall-label">
							<input type="input" name="qw_max_image_size" id="qw_max_image_size" value="<?php echo qa_opt('qw_max_image_size') ?>" class="form-control">
						</td>
					</tr>
					<tr>
						<th class="qa-form-tall-label">
							Max size of file (MB)
						</th>
						<td class="qa-form-tall-label">
							<input type="input" name="qw_max_image_file" id="qw_max_image_file" value="<?php echo qa_opt('qw_max_image_file') ?>" class="form-control">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}

}


// init method
$qw_media_addon = new QW_Media_Addon; 
