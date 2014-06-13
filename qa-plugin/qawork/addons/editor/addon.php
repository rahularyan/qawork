<?php
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('editor', '/addons/editor/editor.php', 'qw_editor', 'QW Editor');

define('QW_EDITOR_STYLE_SHEET_PATH', QW_CONTROL_DIR . '/addons/editor/plugins/codesnippet/lib/highlight/styles' );
define('QW_EDITOR_STYLE_SHEET_LINK', QW_CONTROL_URL . '/addons/editor/plugins/codesnippet/lib/highlight/styles' );

class QW_Editor_Addon{
	function __construct(){
		qw_event_hook('doctype', NULL, array($this, 'navigation'));
		//qw_event_hook('register_language', NULL, array($this, 'language'));
		qw_add_filter('enqueue_css', array($this, 'css'));
		qw_event_hook('enqueue_script', NULL, array($this, 'script'));
		qw_add_action('qw_theme_option_tab', array($this, 'option_tab'));
		qw_add_action('qw_theme_option_tab_content', array($this, 'option_tab_content'));
		qw_add_action('qw_reset_theme_options', array($this, 'reset_theme_options'));

	}
		
	public function language($lang_arr){
		// $lang_arr['qw_editor'] = QW_CONTROL_DIR .'/addons/breadcrumbs/language-*.php';
		return $lang_arr;
	}

	public function css($css_src , $template ){
		if ($template == 'question' || $template == 'gzip') {
			$selected_theme  = qa_opt('qw_qa_editor_code_theme');
	    	if (!$selected_theme ) {
	    		$selected_theme  = "github.css" ;
	    	}
	    	$suffix = "/" ;
	    	$minify_opt  = qa_opt('qw_qa_editor_theme_use_minified');
	    	if (!!$minify_opt) {
	    		$suffix = ".min/" ;
	    	}
	    	$root_theme_url = QW_EDITOR_STYLE_SHEET_LINK.$suffix ;
	    	$theme_url = $root_theme_url . $selected_theme ;
			$css_src['qw_editor'] = $theme_url ;
		}
		return  $css_src;
	}
	
	public function script($script_src){		
		return  $script_src;
	}

	public function navigation($themeclass) {
        return $themeclass ; 
  }

    public function option_tab(){
          $saved=false;
          if(qa_clicked('qw_save_button')){   
              qa_opt("qw_qa_editor_code_theme", qa_post_text("qw_qa_editor_code_theme"));
              qa_opt("qw_qa_editor_theme_use_minified", !!qa_post_text("qw_qa_editor_theme_use_minified"));
              qa_opt("qw_editor_allowed_OE_providers", qa_post_text("qw_editor_allowed_OE_providers"));
              qa_opt("qw_editor_min_user_level_to_upload", qa_post_text("qw_editor_min_user_level_to_upload"));
              $min_user_points = (int)qa_post_text('qw_qa_editor_min_user_points') ;
              if (!is_numeric($min_user_points) || $min_user_points < 100) {
                $min_user_points = 100 ;
              }
              qa_opt('qw_qa_editor_min_user_points' , $min_user_points) ;
              $saved=true;
          }
          
          return '<li>
              <a href="#" data-toggle=".qa-part-form-qa-editor-settings">QA Editor Settings</a>
            </li>';
    }

    public function option_tab_content(){
    	$selected_theme  = qa_opt('qw_qa_editor_code_theme');
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

        $output = '<div class="qa-part-form-qa-editor-settings">
            <h3>Choose Your QA Editor Settings</h3>
            <table class="qa-form-tall-table options-table">';
              
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Choose your prefered theme for code snippets</th>
                  <td class="qa-form-tall-data">
                    	<select name="qw_qa_editor_code_theme">
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
                    <input type="checkbox"' . (qa_opt('qw_qa_editor_theme_use_minified') ? ' checked=""' : '') . ' id="qw_styling_rtl" name="qw_qa_editor_theme_use_minified" data-opts="qw_qa_editor_theme_use_minified_fields">
                  </td>
                </tr>
                </tbody>
              ';
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Allowed Providers for oEmbed (comma separated values )</th>
                  <td class="qa-form-tall-data">
                    <textarea name="qw_editor_allowed_OE_providers" data-opts="qw_editor_allowed_OE_providers_fields">'.qa_opt('qw_editor_allowed_OE_providers').'</textarea>
                  </td>
                </tr>
                </tbody>
              ';
              $selected_level = qa_opt('qw_editor_min_user_level_to_upload') ;

              $options = '<option value="'.QA_USER_LEVEL_MODERATOR.'" '.(($selected_level==QA_USER_LEVEL_MODERATOR) ? 'selected="selected"' : '') .' >Moderator </option>' ;
              $options .= '<option value="'.QA_USER_LEVEL_EDITOR.'" '.(($selected_level==QA_USER_LEVEL_EDITOR) ? 'selected="selected"' : '') .'>Editor </option>' ;
              $options .= '<option value="'.QA_USER_LEVEL_EXPERT.'" '.(($selected_level==QA_USER_LEVEL_EXPERT) ? 'selected="selected"' : '') .'>Expert</option>' ;
              $options .= '<option value="'.QA_USER_LEVEL_APPROVED.'" '.(($selected_level==QA_USER_LEVEL_APPROVED) ? 'selected="selected"' : '') .'>Approved user</option>' ;
              $options .= '<option value="'.QA_USER_LEVEL_BASIC.'" '.(($selected_level==QA_USER_LEVEL_BASIC) ? 'selected="selected"' : '') .'>Basic User</option>' ;
              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Minimum User level to upload media </th>
                  <td class="qa-form-tall-data">
                    <select name="qw_editor_min_user_level_to_upload">
                    '.$options.'
                    </select>
                  </td>
                </tr>
                </tbody>
              ';

              $output .= '
                <tbody>
                <tr>
                  <th class="qa-form-tall-label">Minimum user points to upload media </th>
                  <td class="qa-form-tall-data">
                      <input type="text" name="qw_qa_editor_min_user_points" value="'.qa_opt('qw_qa_editor_min_user_points').'" data-opts="qw_qa_editor_min_user_points_fields">
                  </td>
                </tr>
                </tbody>
              ';

            $output .= '</table></div>';
            return $output;
    }
    public function reset_theme_options() {
        if (qa_clicked('qw_reset_button')) {
          qa_opt("qw_qa_editor_code_theme", "github.css");
          qa_opt("qw_qa_editor_theme_use_minified", 1);
          $saved=true;
        }
    }
}

$qw_editor_addon = new QW_Editor_Addon;