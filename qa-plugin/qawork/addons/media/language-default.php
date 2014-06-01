<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
	//adding email notification messages 
	'title'                   => 'Title',
	'description'                   => 'Description',
	'media'                   => 'Media',
	'add_media'                   => 'Add Media',
	'upload'                   => 'Upload',
	'oembed'                   => 'oEmbed',
	'edit'                   => 'Edit',
	'select_a_file'           => 'Select a file',
	'select_a_file_to_edit'   => 'Select a file to edit',
);