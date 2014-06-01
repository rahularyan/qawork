<?php
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('editor', '/addons/editor/editor.php', 'qw_editor', 'QW Editor');

define('QW_EDITOR_STYLE_SHEET_PATH', CS_CONTROL_DIR . '/addons/editor/plugins/codesnippet/lib/highlight/styles' );
define('QW_EDITOR_STYLE_SHEET_LINK', CS_CONTROL_URL . '/addons/editor/plugins/codesnippet/lib/highlight/styles' );

class QW_Editor_Addon{
	function __construct(){
    cs_event_hook('doctype', NULL, array($this, 'navigation'));
		cs_event_hook('register_language', NULL, array($this, 'language'));
		cs_add_filter('enqueue_css', array($this, 'css'));
		cs_event_hook('enqueue_script', NULL, array($this, 'script'));
		cs_add_action('cs_theme_option_tab', array($this, 'option_tab'));
    cs_add_action('cs_theme_option_tab_content', array($this, 'option_tab_content'));
	}
		
	public function language($lang_arr){
		// $lang_arr['qw_editor'] = CS_CONTROL_DIR .'/addons/breadcrumbs/language-*.php';
		return $lang_arr;
	}

	public function css($css_src , $template ){
		if ($template == 'question') {
			$selected_theme  = qa_opt('cs_qa_editor_code_theme');
	    	if (!$selected_theme ) {
	    		$selected_theme  = "github.css" ;
	    	}
	    	$suffix = "/" ;
	    	$minify_opt  = qa_opt('cs_qa_editor_theme_use_minified');
	    	if (!!$minify_opt) {
	    		$suffix = ".min/" ;
	    	}
	    	$root_theme_url = QW_EDITOR_STYLE_SHEET_LINK.$suffix ;
	    	$theme_url = $root_theme_url . $selected_theme ;
			// $css_src['qw_editor'] = "http://yandex.st/highlightjs/8.0/styles/monokai.min.css";
			$css_src['qw_editor'] = $theme_url ;
		}
		return  $css_src;
	}
	
	public function script($script_src){		
		// $script_src['qw_editor'] = "http://yandex.st/highlightjs/8.0/highlight.min.js";
		return  $script_src;
	}

	public function navigation($themeclass) {
        // cs_log(print_r($themeclass , true));
        return $themeclass ; 
    }

    public function option_tab(){
          $saved=false;
          if(qa_clicked('cs_save_button')){   
              qa_opt("cs_qa_editor_code_theme", qa_post_text("cs_qa_editor_code_theme"));
              qa_opt("cs_qa_editor_theme_use_minified", qa_post_text("cs_qa_editor_theme_use_minified"));
              $saved=true;
            }
          
          echo '<li>
              <a href="#" data-toggle=".qa-part-form-qa-editor-settings">QA Editor Settings</a>
            </li>';
    }

    public function option_tab_content(){
    	$selected_theme  = qa_opt('cs_qa_editor_code_theme');
    	if (!$selected_theme ) {
    		$selected_theme  = "github.css" ;
    	}

    	$all_themes = scandir( QW_EDITOR_STYLE_SHEET_PATH );
        $select_options = "" ;

        foreach ($all_themes as $theme ) {
        	if ($theme == "." || $theme == ".." || !ends_with($theme , ".css")) {
        		continue; 
        	}
			$selected = ($theme == $selected_theme ) ? 'selected="selected"' : '' ;        	
        	$theme_name = preg_replace("/\\.[^.\\s]{3}$/", "", $theme);    /*remove the css extension */
        	$theme_name = preg_replace('/[^a-zA-Z0-9]+/', ' ', $theme_name) ; /*remove the special chars */
        	$theme_name = ucwords( $theme_name );

        	$select_options .= '<option value="'.$theme.'" '.$selected.'>'.$theme_name.'</option>';
        }

        cs_log(print_r($select_options , true )) ;

        $output = '<div class="qa-part-form-qa-editor-settings">
            <h3>Choose Your QA Editor Settings</h3>
            <table class="qa-form-tall-table options-table">';
              
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Choose your prefered theme for code snippets</th>
                  <td class="qa-form-tall-data">
                    	<select name="cs_qa_editor_code_theme">
                    	'.$select_options.'
                    	</select>
                  </td>
                </tr>
                </tbody>
              ';
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Use minified style sheets (Recomended for fasetr loading ) </th>
                  <td class="qa-form-tall-data">
                    <input type="checkbox"' . (qa_opt('cs_qa_editor_theme_use_minified') ? ' checked=""' : '') . ' id="cs_styling_rtl" name="cs_qa_editor_theme_use_minified" data-opts="cs_qa_editor_theme_use_minified_fields">
                  </td>
                </tr>
                </tbody>
              ';
            $output .= '</table></div>';
            echo $output;
    }
}

$qw_editor_addon = new QW_Editor_Addon;