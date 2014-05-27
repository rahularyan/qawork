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

function cs_upload_dir(){
	return defined(QA_BLOBS_DIRECTORY) ? QA_BLOBS_DIRECTORY : QA_BASE_DIR.'images';
}
function cs_upload_url(){
	return qa_opt('site_url').'images';
}

function cs_image_size(){
	return cs_apply_filter('image_size', array());
}

function cs_upload_file($field, $postid){

	if (isset($_FILES[$field]) && !empty($_FILES[$field])) {
		
		if($_FILES[$field]['type'] == 'image/jpeg' || $_FILES[$field]['type'] == 'image/jpg' || $_FILES[$field]['type'] == 'image/png' || $_FILES[$field]['type'] == 'image/gif'){
			
			return cs_upload_image($_FILES[$field], $postid);
			
		}else{
			require_once CS_CONTROL_DIR.'/inc/class_upload.php';
			
			$upload = Upload::factory( cs_upload_dir() );
			$upload->file($_FILES[$field]);

			//set max. file size (in mb)
			$upload->set_max_file_size(1);

			//set allowed mime types
			$upload->set_allowed_mime_types(array('application/pdf', 'application/zip'));
			$results = $upload->upload();
			
			if($results['status']){
				$results['name'] = pathinfo( $results['filename'], PATHINFO_FILENAME);
				$id = cs_insert_media($results['name'], $results['ext'], $postid );
				$results['url'] = cs_upload_url();
				$results['id'] = $id;
			}
			return $results;
		}
	}
}

function cs_file_name($file){
	$ext = pathinfo( $file, PATHINFO_EXTENSION);
	$md5 = md5(time().uniqid());
	return array(
		'file' => $md5.'.'.$ext,
		'name' => $md5,
		'ext' => $ext
	);
}

function cs_upload_image($file, $postid = 0){
	include_once(CS_CONTROL_DIR.'/inc/class_images.php');
	
	$uploaddir = cs_upload_dir();
	$name = cs_file_name($file['name']);
	$temp_name = 'temp_image'.$name['ext'];
	move_uploaded_file($file['tmp_name'], $uploaddir.$temp_name);
	
	// get cropping position
	$crop_x = qa_opt('cs_crop_x');
	$crop_y = qa_opt('cs_crop_y');
	
	/// save original image first, and then assign id of original to other size
	$image = new Image($uploaddir.$temp_name);
	$image->save($name['name'], $uploaddir);	
	cs_add_action('after_uploading_original_image', $image);
	
	$sizes = cs_image_size();

	if(isset($sizes)){

		foreach($sizes as $k => $s){
			$image = new Image($uploaddir.$temp_name);
			
			if($k =='thumb')
				$image->resize($s[0], $s[1], 'crop', $crop_x, $crop_y, 90);
			else
				$image->resize($s[0], $s[1], 'fit', $crop_x, $crop_y, 90);
			
			$file_name = $name['name'].'_'.$s[0].'x'. $s[1];
			$image->save($file_name, $uploaddir);
			$name[$k] = $file_name;
			cs_add_action('after_creating_thumb', $image);
		}

	}
	
	// insert to DB
	$name['id'] = cs_insert_media($name['name'], $name['ext'], $postid );
	$name['url'] = cs_upload_url();
	$name['status'] = 'true';
	unlink ($uploaddir.$temp_name); 
	
	return $name;
}

function cs_upload_cover($file){
	$file = $_FILES[$file];
	include_once(CS_CONTROL_DIR.'/inc/class_images.php');
	require_once QA_INCLUDE_DIR.'qa-db-users.php';
	
	$uploaddir = cs_upload_dir();
	$name = cs_file_name($file['name']);
	$temp_name = 'temp_image'.$name['ext'];
	move_uploaded_file($file['tmp_name'], $uploaddir.$temp_name);
	
	// get cropping position
	$crop_x = qa_opt('cs_crop_x');
	$crop_y = qa_opt('cs_crop_y');
	
	/// save original image first, and then assign id of original to other size
	$image = new Image($uploaddir.$temp_name);
	$image->resize(1140, 217, 'crop');
	$image->save($name['name'], $uploaddir);

	$image = new Image($uploaddir.$temp_name);
	$image->resize(300, 53, 'crop');
	$image->save($name['name'].'_s', $uploaddir);	
	
	cs_add_action('after_uploading_cover', $image);
	
	// insert to DB
	$name['id'] = cs_insert_media($name['name'], $name['ext'], 0 );
	$name['url'] = cs_upload_url();
	$name['status'] = 'true';
	$name['action'] = 'cover';
	unlink ($uploaddir.$temp_name); 
	
	$prev_file = cs_user_profile(qa_get_logged_in_handle(), 'cover');

	if (!empty($prev_file)){	
		$delete = $uploaddir.'/'.$prev_file;
		if (file_exists($delete)){			
			unlink ($delete);
			$small = explode('.', $prev_file);
			unlink ($uploaddir.'/'.$small[0].'_s'.$small[1]);
		}
	}
	
	qa_db_user_profile_set(qa_get_logged_in_userid(), 'cover', $name['file']);
	
	return $name;
}


function cs_insert_media($file_name, $type, $postid, $parent =0){
	$userid = qa_get_logged_in_userid();
	qa_db_query_sub(
		'INSERT ^ra_media (type, name, userid, parent, parent_post) VALUES ($, $, #, #, #)',
		$type, $file_name, $userid, $parent, $postid
	);
	return qa_db_last_insert_id();
}

function cs_update_media($id, $title, $description){
	$userid = qa_get_logged_in_userid();
	qa_db_query_sub(
		'UPDATE ^ra_media SET title = $, description = $ WHERE id=$',
		$title, $description, $id
	);
	return qa_db_last_insert_id();
}

/* for deleting media by id */
function cs_delete_media_by_id($id){
	// first delete all media files
	cs_delete_media_files_by_id($id);
	
	qa_db_query_sub(
		'DELETE FROM ^ra_media WHERE id= #',
		$id
	);
	
}

function cs_get_post_media($postid){
	$userid = qa_get_logged_in_userid();
	$media = qa_db_read_all_assoc(qa_db_query_sub(
		'SELECT * FROM ^ra_media WHERE parent_post = #',
		$postid
	));

	return $media;
}
function cs_get_media_by_id($id){
	$media = qa_db_read_one_assoc(qa_db_query_sub(
		'SELECT * FROM ^ra_media WHERE id = #',
		$id
	), true);

	return $media;
}

function cs_delete_media_files_by_id($id){
	$m = cs_get_media_by_id($id);
	
	if(count($m) > 0){
		$dir = cs_upload_dir();
		
		if($m['type'] == 'jpg' || $m['type'] == 'jpeg' || $m['type'] == 'png' || $m['type'] == 'gif'){
			// delete main image
			$original = $dir.'/'.$m['name'].'.'.$m['type'];
			if(file_exists($original))			
				unlink($original);
				
			$sizes = cs_image_size();
			if(isset($sizes)){
				foreach($sizes as $s){
					$file = $dir.'/'.$m['name'].'_'.$s[0].'x'.$s[1].'.'.$m['type'];
					if(file_exists($file))
						unlink($file);
				}
			}
			cs_add_action('after_deleting_media_images', $m);
		}else{
			unlink($dir.'/'.$m['name'].'.'.$m['type']);
			cs_add_action('after_deleting_media_files', $m);
		}

	}
}

function cs_post_medias($postid, $size = 'thumb'){
	$medias = cs_get_post_media($postid);
	
	if(count($medias) > 0){
		$dir = cs_upload_url();
		$output = '<ul class="post-attachments clearfix">';
		foreach ($medias as $m){
			if($m['type'] == 'jpg' || $m['type'] == 'jpeg' || $m['type'] == 'png' || $m['type'] == 'gif'){
					$output .= '<li class="attachments load-media-preview" data-id="'.$m['id'].'" data-toggle="modal" data-target="#show_file_preview"><img src="'.cs_media_filename($m, $size).'" /></li>';
			}else{
				$output .= '<li class="attachments load-media-preview" data-id="'.$m['id'].'" data-toggle="modal" data-target="#show_file_preview"><i class="file-icon icon-'.$m['type'].'"></i></li>';
			}
		}
		$output .= '</ul>';
		return $output;
	}	
}

function cs_media_filename($m, $size = false){
	$url = cs_upload_url();
	if(isset($m['name']) && isset($m['type']))
		return $url.'/'.$m['name'] .($size ? '_'.cs_get_image_size_string($size) : '').'.'. $m['type'];
	
	return false;
}


function cs_get_image_size_string($size){
	$sizes = cs_image_size();
	if(isset($sizes[$size]))
		return $sizes[$size][0].'x'.$sizes[$size][1];
	return false;
}
class CS_Media_Addon{
	function __construct(){
		cs_add_filter('init_queries', array($this, 'init_queries'));
		
		cs_event_hook('register_language', NULL, array($this, 'language'));
		
		// hook buttons into head_script
		cs_add_filter('enqueue_scripts', array($this, 'head_script'));
		
		// hook buttons into head_css
		cs_add_filter('enqueue_css', array($this, 'head_css'));
		
		// hook buttons in theme layer
		cs_add_action('doctype', array($this, 'ra_post_buttons'));
		cs_event_hook('cs_ajax_load_upload_modal', NULL, array($this, 'upload_modal'));
		cs_event_hook('cs_ajax_upload_file', NULL, array($this, 'upload_file'));
		cs_event_hook('cs_ajax_load_media_item_edit', NULL, array($this, 'load_media_item_edit'));
		cs_event_hook('cs_ajax_edit_media_item', NULL, array($this, 'edit_media_item'));
		cs_event_hook('cs_ajax_load_media_item', NULL, array($this, 'load_media_item'));
		
		cs_add_filter('image_size', array($this, 'image_size'));

		cs_add_action('footer_bottom', array($this, 'add_preview_modal'));
		
		cs_add_action('cs_theme_option_tab', array($this, 'cs_theme_option_tab'));
		cs_add_action('cs_theme_option_tab_content', array($this, 'cs_theme_option_tab_content'));
		
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
		$lang_arr['cs_media'] = CS_CONTROL_DIR .'/addons/media/language-*.php';
		return $lang_arr;
	}

	public function ra_post_buttons($content){
		if(isset($content['q_view'])){
			$postid = $content['q_view']['raw']['postid'];

			if (isset($content['form_q_edit']) && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)){
				$cs_media=array(
					'label' => '<button type="button" class="icon-image btn btn-default open-media-modal" data-args="'.$postid.'">'.qa_lang_html('cs_media/media').'</button>',
					'type' => 'custom',
				);
				
				$content['form_q_edit']['fields'] = cs_array_insert_before('content', $content['form_q_edit']['fields'], 'cs_media', $cs_media );
			
				return $content;
				
			}
		}
	}
	
	function upload_modal(){
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN){
		$postid = (int)qa_post_text('args');
		?>
			<!-- Modal -->
		<div class="modal fade" id="media-modal-<?php echo $postid; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo qa_lang_html('cs_media/add_media'); ?></h4>
			  </div>
			  <div class="modal-body">
				<div class="row">
					<div class="col-sm-7 edit-files-list">
						<ul class="editable-media">
							<?php 
								$medias = cs_get_post_media($postid);
		
								if(count($medias) > 0){
									foreach ($medias as $m){
										if($m['type'] == 'jpg' || $m['type'] == 'jpeg' || $m['type'] == 'png' || $m['type'] == 'gif'){
											echo '<li class="attachments" data-id="'.$m['id'].'"><img src="'.cs_media_filename($m, 'thumb').'" /></li>';
										}else{
											echo '<li class="attachments" data-id="'.$m['id'].'" data-code="'.qa_get_form_security_code('media_'.$m['id']).'"><i class="file-icon icon-'.$m['type'].'"></i></li>';
										}
									}
								}
							?>
						</ul>
					</div>
					<div class="col-sm-5">
						<!-- Nav tabs -->
						<ul class="nav nav-tabs media-action-tab">
						  <li class="active"><a href="#upload-tab" data-toggle="tab"><?php echo qa_lang_html('cs_media/upload'); ?></a></li>
						  <li><a href="#editmedia-tab" data-toggle="tab"><?php echo qa_lang_html('cs_media/edit'); ?></a></li>
						</ul>

						<!-- Tab panes -->
						<div class="tab-content">
						  <div class="tab-pane active" id="upload-tab">
								<form id="file-upload" method="POST" enctype="multipart/form-data">
									<div class="file-input-wrapper">
										<button class="btn-file-input btn"><?php echo qa_lang_html('cs_media/select_a_file'); ?></button>
										<input id="file-upload-input" name="post_media" type="file" />
									</div>
									<div id="file-preview" class="clearfix"></div>
									<button type="submit" class="btn btn-success"><?php echo qa_lang_html('cs_media/upload'); ?></button>
									<input type="hidden" name="action" value="upload_file">
									<input type="hidden" name="postid" value="<?php echo $postid; ?>">
									<input type="hidden" name="code" value="<?php echo qa_get_form_security_code('media_'.$postid ); ?>">
								</form>	
						  </div>
						  <div class="tab-pane" id="editmedia-tab">
							<p><?php echo qa_lang_html('cs_media/select_a_file_to_edit'); ?></p>
						  </div>
						</div>
					</div>				
				</div>
			  </div>
			  
			</div>
		  </div>
		</div>
		<?php
		}
		die(); 
	}
	
	public function load_media_item_edit(){
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN){
		$id = (int)qa_post_text('args');
		$media = cs_get_media_by_id($id);

		ob_start();
		?>
			<form class="media-item-form" method="POST">
				<?php 
					if($media['type'] == 'jpg' || $media['type'] == 'jpeg' || $media['type'] == 'png' || $media['type'] == 'gif'){
						$media['url'] = cs_media_filename($media, 'large');
						echo '<img class="file-preview" src ="'.$media['url'].'" />';
					}else{
						$media['url'] = cs_media_filename($media);
						echo '<i class="file-preview file-icon icon-'.$media['type'].'"></i>';
					}
				
				?>
				
				<input class="form-control" type="text" value="<?php echo cs_upload_url().'/'.$media['name'].'.'.$media['type']; ?>" name="url">
				
				<input class="form-control" type="text" name="title" placeholder="<?php echo qa_lang_html('cs_media/title'); ?>" value="<?php echo isset($media['title']) ? $media['title'] : ''; ?>">
				<textarea class="form-control" name="description" placeholder="<?php echo qa_lang_html('cs_media/description'); ?>"><?php echo isset($media['description']) ? $media['description'] : ''; ?></textarea>

				<input type="submit" class="btn btn-success" name="do" value="save">
				<input type="submit" class="btn" name="do" value="delete">
				<input type="submit" class="btn insert-media-to-editor pull-right" value="insert" data-dismiss="modal">
				<input type="hidden" name="type" value="<?php echo $media['type']; ?>">
				<input type="hidden" name="action" value="edit_media_item">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<input type="hidden" name="code" value="<?php echo qa_get_form_security_code('media_edit_'.$id ); ?>">
			</form>
		<?php
		 $html = ob_get_clean();
		 
		 echo json_encode(array($media, $html));
		}
		die();
	}
	
	
	function edit_media_item(){
		$id = qa_post_text('id');
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN && qa_check_form_security_code('media_edit_'.$id, qa_post_text('code'))){
			if(qa_post_text('do') == 'delete'){
				cs_delete_media_by_id($id);
				echo json_encode(array('delete' , '<div class="alert alert-success alert-dismissable">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				  '.qa_lang_html('cleanstrap/media_successfully_deleted').'
				</div>'));

			}elseif(qa_post_text('do') == 'save'){
				cs_update_media($id, qa_post_text('title'), qa_post_text('description'));
				
				echo json_encode(array('save' , '<div class="alert alert-success alert-dismissable">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				  '.qa_lang_html('cleanstrap/media_is_updated').'
				</div>'));
			}
		}
		die();
	}
	
	public function head_script($script_src){		
		$script_src['media_script'] = CS_CONTROL_URL . '/addons/media/script.js';
		return $script_src;
		
	}
	
	public function head_css($css_src){
		$css_src['cs_media_css'] = CS_CONTROL_URL . '/addons/media/styles.css';
		return $css_src;
	}
	
	public function upload_file(){
		$postid = (int)qa_post_text('postid');
		$type = qa_post_text('type');
		
		if($type == 'cover' && qa_check_form_security_code('upload_cover', qa_post_text('code'))){
		
			echo json_encode(cs_upload_cover('cover'));
		
		}elseif(qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN && qa_check_form_security_code('media_'.$postid, qa_post_text('code'))){
			echo json_encode(cs_upload_file('post_media', $postid));
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
	}
	
	public function load_media_item(){
		$id = (int)qa_post_text('args');
		
		$media = cs_get_media_by_id($id);
		
		echo '<div class="media-popup">';
		
		if($media['type'] == 'jpeg' || $media['type'] == 'jpg' || $media['type'] == 'png' || $media['type'] == 'gif')
			echo '<img class="file-preview" src="'.cs_media_filename($media).'" />';
		else{
			echo '<i class="file-preview icon-'.$media['type'].'" ></i>';
			echo '<a href="'.cs_media_filename($media).'" class="btn">Download</a>';
		}
		
		if(!empty($media['title']))
			echo '<strong class="media-title">'.$media['title'].'</strong>';
		
		if(!empty($media['description']))
			echo '<p class="media-description">'.$media['description'].'</p>';
			
		echo '</div>';
		
		die();
	}
	
	public function cs_theme_option_tab(){
		?>
			<li>
				<a href="#" data-toggle=".qa-part-form-tc-media">Media</a>
			</li>
		<?php
	}
	
	public function cs_theme_option_tab_content(){
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
							<input type="input" name="cs_max_image_size" id="cs_max_image_size" value="1" class="form-control">
						</td>
					</tr>
					<tr>
						<th class="qa-form-tall-label">
							Max size of file (MB)
						</th>
						<td class="qa-form-tall-label">
							<input type="input" name="cs_max_image_file" id="cs_max_image_file" value="5" class="form-control">
						</td>
					</tr>
				</tbody>
				<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Image Cropping X
						<span class="description">Crop Featured image from Right/Left</span>
					</th>
					<td class="qa-form-tall-label">
						<select id="cs_crop_x" name="cs_crop_y" >
							<option <?php echo (qa_opt('cs_crop_x') == 'l') ? ' selected' : ''; ?> value="l">left</option>
							<option <?php echo (qa_opt('cs_crop_x') == 'c') ? ' selected' : ''; ?> value="c">Center</option>
							<option <?php echo (qa_opt('cs_crop_x') == 'r') ? ' selected' : ''; ?> value="r">right</option>
						</select>
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Image Cropping Y
						<span class="description">Crop Featured image from Top/Bottom</span>
					</th>
					<td class="qa-form-tall-label">
						<select id="cs_crop_y" name="cs_crop_y" >
							<option <?php echo (qa_opt('cs_crop_y') == 't') ? ' selected' : '' ?> value="t">Top</option>
							<option <?php echo (qa_opt('cs_crop_y') == 'c') ? ' selected' : ''; ?> value="c">Center</option>
							<option <?php echo (qa_opt('cs_crop_y') == 'b') ? ' selected' : ''; ?> value="b">Bottom</option>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
		</div>
		<?php
	}

}


// init method
$cs_media_addon = new CS_Media_Addon; 
