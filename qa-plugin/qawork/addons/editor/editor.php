<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/

	
	File: qa-plugin/wysiwyg-editor/qa-wysiwyg-editor.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Editor module class for WYSIWYG editor plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/


	class qw_editor {
		
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->urltoroot=$urltoroot;
		}

		
		function option_default($option)
		{
			if ($option=='wysiwyg_editor_upload_max_size') {
				require_once QA_INCLUDE_DIR.'qa-app-upload.php';
				
				return min(qa_get_max_upload_size(), 1048576);
			}
		}
	
	
		function bytes_to_mega_html($bytes)
		{
			return qa_html(number_format($bytes/1048576, 1));
		}
	
		
	
		function calc_quality($content, $format)
		{
			if ($format=='html')
				return 1.0;
			elseif ($format=='')
				return 0.8;
			else
				return 0;
		}

		
		function get_field(&$qa_content, $content, $format, $fieldname, $rows /* $autofocus parameter deprecated */)
		{
			$scriptsrc=CS_CONTROL_URL.'/addons/editor/ckeditor.js?'.QA_VERSION;			
			$alreadyadded=false;

			if (isset($qa_content['script_src']))
				foreach ($qa_content['script_src'] as $testscriptsrc)
					if ($testscriptsrc==$scriptsrc)
						$alreadyadded=true;
					
			if (!$alreadyadded) {
					
				$qa_content['script_src'][]=$scriptsrc;
				
			}		
				
			if ($format=='html')
				$html=$content;
			else
				$html=qa_html($content, true);
			
			return array(
				'tags' => 'name="'.$fieldname.'"',
				'value' => qa_html($html),
				'rows' => $rows,
			);
		}
	
	
		function load_script($fieldname)
		{
			return "qa_ckeditor_".$fieldname."=CKEDITOR.replace(".qa_js($fieldname).", window.qa_wysiwyg_editor_config);";
		}

		
		function focus_script($fieldname)
		{
			return "qa_ckeditor_".$fieldname.".focus();";
		}

		
		function update_script($fieldname)
		{
			//return "qa_ckeditor_".$fieldname.".updateElement();";
		}

		
		function read_post($fieldname)
		{
			$html=qa_post_text($fieldname);
			
			$htmlformatting=preg_replace('/<\s*\/?\s*(br|p)\s*\/?\s*>/i', '', $html); // remove <p>, <br>, etc... since those are OK in text
			
			if (preg_match('/<.+>/', $htmlformatting)) // if still some other tags, it's worth keeping in HTML
				return array(
					'format' => 'html',
					'content' => qa_sanitize_html($html, false, true), // qa_sanitize_html() is ESSENTIAL for security
				);
			
			else { // convert to text
				$viewer=qa_load_module('viewer', '');

				return array(
					'format' => '',
					'content' => $viewer->get_text($html, 'html', array())
				);
			}
		}
	
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/