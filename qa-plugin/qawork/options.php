<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_theme_options {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN && $request=='themeoptions')
			return true;

		return false;
	}
	function process_request($request)
	{
	
		$saved = false;
		if (qa_clicked('qw_reset_button')) {
			$this->reset_theme_options();
			$saved = 'Settings saved';
		}
		if (qa_clicked('qw_save_button')) {
			// General
			qa_opt('logo_url', qa_post_text('qw_logo_field'));
			qa_opt('qw_favicon_url', qa_post_text('qw_favicon_field'));
			qa_opt('qw_favicon_big_url', qa_post_text('qw_favicon_big_field'));
			qa_opt('qw_enable_gzip', (bool) qa_post_text('qw_enable_gzip'));
			qa_opt('qw_featured_image_width', (int) qa_post_text('qw_featured_image_width'));
			qa_opt('qw_featured_image_height', (int) qa_post_text('qw_featured_image_height'));
			qa_opt('qw_featured_thumbnail_width', (int) qa_post_text('qw_featured_thumbnail_width'));
			qa_opt('qw_featured_thumbnail_height', (int) qa_post_text('qw_featured_thumbnail_height'));
			qa_opt('qw_crop_x', qa_post_text('qw_crop_x'));
			qa_opt('qw_crop_y', qa_post_text('qw_crop_y'));
			
			// Layout
			qa_opt('qw_nav_position', qa_post_text('qw_nav_position'));
			qa_opt('qw_nav_fixed', (bool) qa_post_text('qw_nav_fixed'));
			qa_opt('qw_show_icon', (bool) qa_post_text('qw_show_icon'));
			qa_opt('qw_enable_ask_button', (bool) qa_post_text('qw_enable_ask_button'));
			qa_opt('qw_enable_category_nav', (bool) qa_post_text('qw_enable_category_nav'));
			qa_opt('qw_enable_clean_qlist', (bool) qa_post_text('qw_enable_clean_qlist'));
			qa_opt('qw_enable_except', (bool) qa_post_text('qw_enable_except'));
			qa_opt('qw_except_len', (int) qa_post_text('qw_except_len'));
			qa_opt('qw_enable_avatar_lists', (bool) qa_post_text('qw_enable_avatar_lists'));
			if (qa_opt('qw_enable_avatar_lists'))
				qa_opt('avatar_q_list_size', 35);
			else
				qa_opt('avatar_q_list_size', 0); // set avatar size to zero so Q2A won't load them
			qa_opt('show_view_counts', (bool) qa_post_text('qw_enable_views_lists'));
			qa_opt('qw_show_tags_list', (bool) qa_post_text('qw_show_tags_list'));
			qa_opt('qw_horizontal_voting_btns', (bool) qa_post_text('qw_horizontal_voting_btns'));
			qa_opt('qw_enble_back_to_top', (bool) qa_post_text('qw_enble_back_to_top'));
			qa_opt('qw_back_to_top_location', qa_post_text('qw_back_to_top_location'));
			
			// Styling
			qa_opt('qw_styling_rtl', (bool) qa_post_text('qw_styling_rtl'));
			qa_opt('qw_bg_select', qa_post_text('qw_bg_select'));
			qa_opt('qw_bg_color', qa_post_text('qw_bg_color'));
			qa_opt('qw_text_color', qa_post_text('qw_text_color'));
			qa_opt('qw_border_color', qa_post_text('qw_border_color'));
			qa_opt('qw_q_link_color', qa_post_text('qw_q_link_color'));
			qa_opt('qw_q_link_hover_color', qa_post_text('qw_q_link_hover_color'));
			qa_opt('qw_nav_link_color', qa_post_text('qw_nav_link_color'));
			qa_opt('qw_nav_link_color_hover', qa_post_text('qw_nav_link_color_hover'));
			qa_opt('qw_subnav_link_color', qa_post_text('qw_subnav_link_color'));
			qa_opt('qw_subnav_link_color_hover', qa_post_text('qw_subnav_link_color_hover'));
			qa_opt('qw_link_color', qa_post_text('qw_link_color'));
			qa_opt('qw_link_hover_color', qa_post_text('qw_link_hover_color'));
			qa_opt('qw_highlight_color', qa_post_text('qw_highlight_color'));
			qa_opt('qw_highlight_bg_color', qa_post_text('qw_highlight_bg_color'));
			qa_opt('qw_ask_btn_bg', qa_post_text('qw_ask_btn_bg'));
			
		
			
			// Social
			$SocialCount  = (int) qa_post_text('social_count'); // number of advertisement items
			$social_links = array();
			$i            = 0;
			while (($SocialCount > 0) and ($i < 100)) { // don't create an infinite loop
				if (null !== qa_post_text('social_link_' . $i)) {
					$social_links[$i]['social_link']  = qa_post_text('social_link_' . $i);
					$social_links[$i]['social_title'] = qa_post_text('social_title_' . $i);
					$social_links[$i]['social_icon']  = qa_post_text('social_icon_' . $i);
					if (($social_links[$i]['social_icon'] == '1') && (null !== qa_post_text('social_image_url_' . $i))) {
						$social_links[$i]['social_icon_file'] = qa_post_text('social_image_url_' . $i);
					}
					$SocialCount--;
				}
				$i++;
			}
			qa_opt('qw_social_list', json_encode($social_links));
			qa_opt('qw_social_enable', (bool) qa_post_text('qw_social_enable'));
			
			// Advertisement
			$AdsCount = (int) qa_post_text('adv_number'); // number of advertisement items
			$ads      = array();
			$i        = 0;
			while (($AdsCount > 0) and ($i < 100)) { // don't create an infinite loop
				if (null !== qa_post_text('adv_adsense_' . $i)) {
					// add adsense ads
					$ads[$i]['adv_adsense']  = qa_post_text('adv_adsense_' . $i);
					$ads[$i]['adv_location'] = qa_post_text('adv_location_' . $i);
					$AdsCount--;
				} elseif ((@getimagesize(@$_FILES['qw_adv_image_' . $i]['tmp_name']) > 0) or (null !== qa_post_text('adv_image_title_' . $i)) or (null !== qa_post_text('adv_image_link_' . $i)) or (null !== qa_post_text('adv_location_' . $i))) {
					// add static ads
					if (null !== qa_post_text('adv_image_url_' . $i)) {
						$ads[$i]['adv_image'] = qa_post_text('adv_image_url_' . $i);
					}
					$ads[$i]['adv_image_title'] = qa_post_text('adv_image_title_' . $i);
					$ads[$i]['adv_image_link']  = qa_post_text('adv_image_link_' . $i);
					$ads[$i]['adv_location']    = qa_post_text('adv_location_' . $i);
					$AdsCount--;
				}
				$i++;
			}
			qa_opt('qw_advs', json_encode($ads));
			qa_opt('qw_enable_adv_list', (bool) qa_post_text('qw_enable_adv_list'));
			qa_opt('qw_ads_below_question_title', base64_encode($_REQUEST['qw_ads_below_question_title']));
			qa_opt('qw_ads_after_question_content', base64_encode($_REQUEST['qw_ads_after_question_content']));
			
			// footer							
			qa_opt('qw_footer_copyright', qa_post_text('qw_footer_copyright'));
			
			
			$saved = true;
			$saved = 'Settings saved';
		}
		$qa_content=qa_content_prepare();

		
		$qa_content['site_title']="Options for qawork";
		$qa_content['title']="QAWork Options";
		$qa_content['error']="";
		$qa_content['suggest_next']="";
		
		$qa_content['custom']= $this->opt_form();
		
		return $qa_content;	
	}
	
	function opt_form(){
		
		$output = '<form id="theme-option-form" class="form-horizontal" enctype="multipart/form-data" method="post">';		
		$output .= '<div class="qa-part-tabs-nav">
		<ul class="ra-option-tabs">
			<li class="active">
				<a href="#" data-toggle=".qa-part-form-tc-general">General</a>
			</li>
			<li>
				<a href="#" data-toggle=".qa-part-form-tc-layout">Layouts</a>
			</li>

			<li>
				<a href="#" data-toggle=".qa-part-form-tc-social">Social</a>
			</li>
			<li>
				<a href="#" data-toggle=".qa-part-form-tc-ads">Advertisements</a>
			</li>
			'.qw_do_action('qw_theme_option_tab').'
		</ul>
	</div><div class="option-tab-content-outer"><div class="option-tab-content">';
		$output .= $this->opt_general();
		$output .= $this->opt_layout();
		//$output .= $this->opt_styling();
		$output .= $this->opt_social();
		$output .= $this->opt_ads();
		$output .= qw_do_action('qw_theme_option_tab_content');
		
		$output .= '</div>';
		$output .= '<div class="form-button-sticky-footer">';
			$output .= '<div class="form-button-holder">';
				$output .= '<input type="submit" class="qa-form-tall-button btn-success" title="" value="Save Changes" name="qw_save_button">';
				$output .= '<input type="submit" class="qa-form-tall-button" title="" value="Reset to Default" name="qw_reset_button">';
			$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		
		$output .= '</form>';
		
		return $output;
	}
	
	function opt_general(){
		return $output ='<div class="qa-part-form-tc-general active">
		<h3>General Settings</h3>
		<table class="qa-form-tall-table options-table">
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Logo
						<span class="description">Upload your own logo.</span>
					</th>
					<td class="qa-form-tall-data">
						' . (qa_opt('logo_url') ? '<img id="logo-preview" class="logo-preview img-thumbnail" src="' . qa_opt('logo_url') . '">' : '<img id="logo-preview" class="logo-preview img-thumbnail" style="display:none;" src="">') . '
						<button type="button" class="icon-image btn btn-default open-media-modal" data-args="0" data-for="logo">'.qa_lang_html('qw_media/upload').'</button>
						<input id="qw_logo_field" type="hidden" name="qw_logo_field" value="' . qa_opt('logo_url') . '">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Favicon
						<span class="description">favicon image (32px32px).</span>
					</th>
					<td class="qa-form-tall-data">
						' . (qa_opt('qw_favicon_url') ? '<img id="favicon-preview" class="favicon-preview img-thumbnail" src="' . qa_opt('qw_favicon_url') . '">' : '<img id="favicon-preview" class="favicon-preview img-thumbnail" style="display:none;" src="">') . '
						<button type="button" class="icon-image btn btn-default open-media-modal" data-args="0" data-for="favicon">'.qa_lang_html('qw_media/upload').'</button>
						<input id="qw_favicon_field" type="hidden" name="qw_favicon_field" value="' . qa_opt('qw_favicon_url') . '">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Big Favicon
						<span class="description">favicon image for HD device (144px144px).</span>
					</th>
					<td class="qa-form-tall-data">
						' . (qa_opt('qw_favicon_big_url') ? '<img id="favicon-preview-big" class="favicon-preview img-thumbnail" src="' . qa_opt('qw_favicon_big_url') . '">' : '<img id="favicon-preview-big" class="favicon-preview img-thumbnail" style="display:none;" src="">') . '
						<button type="button" class="icon-image btn btn-default open-media-modal" data-args="0" data-for="big-favicon">'.qa_lang_html('qw_media/upload').'</button>
						<input id="qw_favicon_big_field" type="hidden" name="qw_favicon_big_field" value="' . qa_opt('qw_favicon_big_url') . '">
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Enable gZIP
						<span class="description">For faster loading of assets</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" ' . (qa_opt('qw_enable_gzip') ? ' checked=""' : '') . ' id="qw_enable_gzip" name="qw_enable_gzip">
							<label for="qw_enable_gzip">
							</label>
						</div>
					</td>
				</tr>
			</tbody>
			
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Text at right side of footer
						<span class="description">you can add links or images by entering html code</span>
					</th>
					<td class="qa-form-tall-label">
						<input id="qw_footer_copyright" class="form-control" type="text" name="qw_footer_copyright" value="' . qa_opt('qw_footer_copyright') . '">
					</td>
				</tr>
			</tbody>
		</table>
	</div>';
	}

	function opt_layout(){
		return '<div class="qa-part-form-tc-layout">
		<h3>Layout Settings</h3>
		<table class="qa-form-tall-table options-table">
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Enable RTL Styling
						<span class="description">for Right to Left Languages</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_styling_rtl') ? ' checked=""' : '') . ' id="qw_styling_rtl" name="qw_styling_rtl">
							<label for="qw_styling_rtl">
							</label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody>

				<tr>
					<th class="qa-form-tall-label">
						Show menu Icon
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_show_icon') ? ' checked=""' : '') . ' id="qw_show_icon" name="qw_show_icon">
								<label for="qw_show_icon"></label>
						</div>
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Ask Button
						<span class="description">Enable to show Ask Button in header.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enable_ask_button') ? ' checked=""' : '') . ' id="qw_enable_ask_button" name="qw_enable_ask_button">
								<label for="qw_enable_ask_button"></label>
						</div>
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Categories Drop down
						<span class="description">Enable to show Categories List in drop down menu in header.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enable_category_nav') ? ' checked=""' : '') . ' id="qw_enable_category_nav" name="qw_enable_category_nav">
								<label for="qw_enable_category_nav"></label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr><td><h3>Home Page</h3></td></tr>
				<tr>
					<th class="qa-form-tall-label">
						Clean Question List
						<span class="description">Enable to switch to default question list.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enable_clean_qlist') ? ' checked=""' : '') . ' id="qw_enable_clean_qlist" name="qw_enable_clean_qlist">
								<label for="qw_enable_clean_qlist"></label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr><td><h3>Question Lists</h3></td></tr>
				<tr>
					<th class="qa-form-tall-label">
						Question Excerpt
						<span class="description">Toggle question description in question lists.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enable_except') ? ' checked=""' : '') . ' id="qw_enable_except" name="qw_enable_except">
								<label for="qw_enable_except"></label>
						</div>
					</td>
				</tr>
				<tr id="qw_except_length">
					<th class="qa-form-tall-label">
						Excerpt Length
						<span class="description">Length of questions description in question lists</span>
					</th>
					<td class="qa-form-tall-label">
						<input class="qa-form-wide-number" type="text" value="' . qa_opt('qw_except_len') . '"  id="qw_except_len" name="qw_except_len">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Avatars in lists
						<span class="description">Toggle avatars in question lists.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enable_avatar_lists') ? ' checked=""' : '') . ' id="qw_enable_avatar_lists" name="qw_enable_avatar_lists">
								<label for="qw_enable_avatar_lists"></label>
						</div>
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						View Count
						<span class="description">Toggle View Count in question lists.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('show_view_counts') ? ' checked=""' : '') . ' id="qw_enable_views_lists" name="qw_enable_views_lists">
								<label for="qw_enable_views_lists"></label>
						</div>
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Question Tags
						<span class="description">Toggle Tags in question lists.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_show_tags_list') ? ' checked=""' : '') . ' id="qw_show_tags_list" name="qw_show_tags_list">
								<label for="qw_show_tags_list"></label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Horizontal Voting Buttons
						<span class="description">Switch between horizontal and vertical voting buttons</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_horizontal_voting_btns') ? ' checked=""' : '') . ' id="qw_horizontal_voting_btns" name="qw_horizontal_voting_btns">
							<label for="qw_horizontal_voting_btns">
							</label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Back to Top Button
						<span class="description">Enable Back to Top</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
								<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enble_back_to_top') ? ' checked=""' : '') . ' id="qw_enble_back_to_top" name="qw_enble_back_to_top">
							<label for="qw_enble_back_to_top">
							</label>
						</div>
					</td>
					</tr>
					<tr id="back_to_top_location_container" ' . (qa_opt('qw_enble_back_to_top') ? '' : ' style="display:none;"') . '>
					<th class="qa-form-tall-label">
						Back To Top\'s Position
						<span class="description">Back To Top button\'s Position</span>
					</th>
					<td class="qa-form-tall-label">
						<input class="theme-option-radio" type="radio"' . (qa_opt('qw_back_to_top_location') == 'nav' ? ' checked=""' : '') . ' id="qw_back_to_top_nav" name="qw_back_to_top_location" value="nav">
						   <label for="qw_back_to_top_nav">Under Navigation</label>
						<input class="theme-option-radio" type="radio"' . (qa_opt('qw_back_to_top_location') == 'right' ? ' checked=""' : '') . ' id="qw_back_to_top_right" name="qw_back_to_top_location" value="right">
						   <label for="qw_back_to_top_right">Bottom Right</label> 
					</td>
				</tr>
			</tbody>
		</table>
	</div>';
	}

	function opt_styling(){
            $list_options = '';

		return '<div class="qa-part-form-tc-styling">
		<h3>Colors</h3>
		<table class="qa-form-tall-table options-table">
			<tbody>
				<tr id="bg-color-container"' . ((qa_opt('qw_bg_select') == 'bg_color') ? '' : ' style="display:none;"') . '>
					<th class="qa-form-tall-label">
						Body Font Color
					</th>
					<td class="qa-form-tall-label">
						<input type="colorpicker" class="form-control" value="' . qa_opt('qw_bg_color') . '" id="qw_bg_color" name="qw_bg_color">
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Text color
					</th>
					<td class="qa-form-tall-label">
						<input type="colorpicker" class="form-control" value="' . qa_opt('qw_text_color') . '" id="qw_text_color" name="qw_text_color">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Border color
					</th>
					<td class="qa-form-tall-label">
						<input type="colorpicker" class="form-control" value="' . qa_opt('qw_border_color') . '" id="qw_border_color" name="qw_border_color">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Link color
					</th>
					<td class="qa-form-tall-label">
						Link Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_link_color') . '" id="qw_link_color" name="qw_link_color">
						Hover Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_link_hover_color') . '" id="qw_link_hover_color" name="qw_link_hover_color">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Question Link color
					</th>
					<td class="qa-form-tall-label">
						Link Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_q_link_color') . '" id="qw_q_link_color" name="qw_q_link_color">
						Hover Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_q_link_hover_color') . '" id="qw_q_link_hover_color" name="qw_q_link_hover_color">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Navigation Link color
					</th>
					<td class="qa-form-tall-label">
						Text Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_nav_link_color') . '" id="qw_nav_link_color" name="qw_nav_link_color">
						Hover Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_nav_link_color_hover') . '" id="qw_nav_link_color_hover" name="qw_nav_link_color_hover">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Sub Navigation Link color
					</th>
					<td class="qa-form-tall-label">
						Text Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_subnav_link_color') . '" id="qw_subnav_link_color" name="qw_subnav_link_color">
						Hover Color<input type="colorpicker" class="form-control" value="' . qa_opt('qw_subnav_link_color_hover') . '" id="qw_subnav_link_color_hover" name="qw_subnav_link_color_hover">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Highlight Text color
					</th>
					<td class="qa-form-tall-label">
						<input type="colorpicker" class="form-control" value="' . qa_opt('qw_highlight_color') . '" id="qw_highlight_color" name="qw_highlight_color">
					</td>
				</tr>
				<tr>
					<th class="qa-form-tall-label">
						Highlight background color
					</th>
					<td class="qa-form-tall-label">
						<input type="colorpicker" class="form-control" value="' . qa_opt('qw_highlight_bg_color') . '" id="qw_highlight_bg_color" name="qw_highlight_bg_color">
					</td>
				</tr>
			</tbody>
		</table>
		<h3>Background color of questions</h3>
		<table class="qa-form-tall-table options-table">
			
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Ask button background
						<span class="description">ADD DETAIL.</span>
					</th>
					<td class="qa-form-tall-label">
						<input type="colorpicker" class="form-control" value="' . qa_opt('qw_ask_btn_bg') . '" id="qw_ask_btn_bg" name="qw_ask_btn_bg">
					</td>
				</tr>
			</tbody>
		</table>
	</div>';
	}

	
	function opt_social(){
		$i              = 0;
		$social_content = '';
		$social_fields  = json_decode(qa_opt('qw_social_list'), true);
		if (isset($social_fields))
			foreach ($social_fields as $k => $social_field) {
				$list_options = '<option class="icon-wrench" value="1"' . ((@$social_field['social_icon'] == '1') ? ' selected' : '') . '>Upload Social Icon</option>';
				foreach (qw_social_icons() as $icon => $name) {

					$list_options .= '<option class="' . $icon . '" value="' . $icon . '"' . (($icon == @$social_field['social_icon']) ? ' selected' : '') . '>' . $name . '</option>';
				}
				$social_icon_list = '<select id="social_icon_' . $i . '" name="social_icon_' . $i . '" class="qa-form-wide-select  social-select" sociallistid="' . $i . '">' . $list_options . '</select>';
				if (isset($social_field['social_link'])) {
					if ((!empty($social_field['social_icon_file'])) and (@$social_field['social_icon'] == '1'))
						$image = '<img id="social_image_preview_' . $i . '" src="' . $social_field['social_icon_file'] . '" class="social-preview img-thumbnail">';
					else
						$image = '<img id="social_image_preview_' . $i . '" src="" class="social-preview img-thumbnail" style="display:none;">';
					$social_content .= '<tr id="soical_box_' . $i . '">
		<th class="qa-form-tall-label">
			Social Link #' . ($i + 1) . '
			<span class="description">choose Icon and link to your social profile</span>
		</th>
		<td class="qa-form-tall-data">
			<span class="description">Social Profile Link</span>
			<input class="form-control" id="social_link_' . $i . '" name="social_link_' . $i . '" type="text" value="' . $social_field['social_link'] . '">
			<span class="description">Link Title</span>
			<input class="form-control" id="social_title_' . $i . '" name="social_title_' . $i . '" type="text" value="' . $social_field['social_title'] . '">
			<span class="description">Choose Social Icon</span>
			' . $social_icon_list . '
			<div class="social_icon_file_' . $i . '"' . ((@$social_field['social_icon'] == '1') ? '' : ' style="display:none;"') . '>
				<span class="description">upload Social Icon</span>
				' . $image . '
				<div id="social_image_uploader_' . $i . '">Upload Icon</div>
				<input type="hidden" value="' . @$social_field['social_icon_file'] . '" id="social_image_url_' . $i . '" name="social_image_url_' . $i . '">
			</div>
			<button id="social_remove" class="qa-form-tall-button social_remove pull-right btn" type="submit" name="social_remove" socialid="' . $i . '">Remove This Link</button>
		</tr>';
				}
				$i++;
			}
		$social_content .= '<input type="hidden" value="' . $i . '" id="social_count" name="social_count">';
		return '<div class="qa-part-form-tc-social">
		<table class="qa-form-tall-table options-table">
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Social Toolbar
						<span class="description">Enable social links in your site\'s header.</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
							<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_social_enable') ? ' checked=""' : '') . ' id="qw_social_enable" name="qw_social_enable">
							<label for="qw_social_enable"></label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Add New Social Links
						<span class="description">Add a new social link</span>
					</th>
					<td class="qa-form-tall-label text-center">
						<button type="submit" id="add_social" name="add_social" class="qa-form-tall-button btn">Add Social Links</button>
					</td>
				</tr>
			</tbody>
			<tbody id="social_container">
				' . $social_content . '	
			</tbody>
		</table>
	</div>';
	}
	
	function opt_ads(){
	
	 $advs        = json_decode(qa_opt('qw_advs'), true);
            $i           = 0;
            $adv_content = '';
            if (isset($advs))
                foreach ($advs as $k => $adv) {
                    if (true) { // use list to choose location of advertisement
                        $list_options = '';
                        for ($count = 1; $count <= qa_opt('page_size_qs'); $count++) {
                            $list_options .= '<option value="' . $count . '"' . (($count == @$adv['adv_location']) ? ' selected' : '') . '>' . $count . '</option>';
                        }
                        $adv_location = '<select id="adv_location_' . $i . '" name="adv_location_' . $i . '" class="qa-form-wide-select">' . $list_options . '</select>';
                    } else {
                        $adv_location = '<input id="adv_location_' . $i . '" name="adv_location_' . $i . '" class="form-control" value="" placeholder="Position of advertisements in list" />';
                    }
                    if (isset($adv['adv_adsense'])) {
                        $adv_content .= '<tr id="adv_box_' . $i . '">
			<th class="qa-form-tall-label">
				Advertisment #' . ($i + 1) . '
				<span class="description">Google Adsense Code</span>
			</th>
			<td class="qa-form-tall-data">
				<textarea class="form-control" id="adv_adsense_' . $i . '" name="adv_adsense_' . $i . '" rows="10">' . $adv['adv_adsense'] . '</textarea>
				<span class="description">Display After this number of questions</span>
				' . $adv_location . '
				<button advid="' . $i . '" id="advremove" name="advremove" class="qa-form-tall-button advremove pull-right btn" type="submit">Remove This Advertisement</button></td>
			</tr>';
                    } else {
                        if (!empty($adv['adv_image']))
                            $image = '<img id="adv_preview_' . $i . '" src="' . $adv['adv_image'] . '" class="adv-preview img-thumbnail">';
                        else
                            $image = '<img id="adv_preview_' . $i . '" src="" class="adv-preview img-thumbnail" style="display:none;">';
                        $adv_content .= '<tr id="adv_box_' . $i . '">
			<th class="qa-form-tall-label">
				Advertisement #' . ($i + 1) . '
				<span class="description">static advertisement</span>
			</th>
			<td class="qa-form-tall-data">
				<div class="clearfix"></div>
				' . $image . '
				<div class="clearfix"></div>
				<div id="adv_image_uploader_' . $i . '">Upload Icon</div>
				<input type="hidden" value="' . @$adv['social_icon_file'] . '" id="social_image_url_' . $i . '" name="social_image_url_' . $i . '">
				
				<span class="description">Image Title</span>
				<textarea class="form-control" type="text" id="adv_image_title_' . $i . '" name="adv_image_title_' . $i . '" value="' . @$adv['adv_image_title'] . '">
				<span class="description">Target link</span>
				
				<textarea class="form-control" id="adv_image_link_' . $i . '" name="adv_image_link_' . $i . '" >' . @$adv['adv_image_link'] . '</textarea>
				<span class="description">Display After this number of questions</span>
				
				' . $adv_location . '
				
				<input type="hidden" value="' . @$adv['adv_image'] . '" id="adv_image_url_' . $i . '" name="adv_image_url_' . $i . '">
				
				<button advid="' . $i . '" id="advremove" name="advremove" class="qa-form-tall-button advremove pull-right btn" type="submit">Remove This Advertisement</button>
			</td>
			</tr>';
                    }
                    $i++;
                }
            $adv_content .= '<input type="hidden" value="' . $i . '" id="adv_number" name="adv_number">';
            $adv_content .= '<input type="hidden" value="' . qa_opt('page_size_qs') . '" id="question_list_count" name="question_list_count">';
		return '<div class="qa-part-form-tc-ads">
		<h3>Advertisment in question list</h3>
		<table class="qa-form-tall-table options-table">
			<tbody>
				<tr>
					<th class="qa-form-tall-label">
						Advertisement in Lists
						<span class="description">Enable Advertisement in question lists</span>
					</th>
					<td class="qa-form-tall-label">
						<div class="on-off-checkbox-container">
							<input type="checkbox" class="on-off-checkbox" value="1"' . (qa_opt('qw_enable_adv_list') ? ' checked=""' : '') . ' id="qw_enable_adv_list" name="qw_enable_adv_list">
							<label for="qw_enable_adv_list"></label>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody id="ads_container" ' . (qa_opt('qw_enable_adv_list') ? '' : ' style="display:none;"') . '>
				<tr>
					<th class="qa-form-tall-label">
						Add Advertisement
						<span class="description">Create advertisement with static or Google Adsense</span>
					</th>
					<td class="qa-form-tall-label text-center">
						
						<button type="submit" id="add_adsense" name="add_adsense" class="qa-form-tall-button btn">Add Google Adsense</button>
					</td>
				</tr>
			' . $adv_content . '
			</tbody>
			
		</table>
		<h3>Advertisement in question page</h3>
		<table class="qa-form-tall-table options-table">
			<tbody><tr>
				<th class="qa-form-tall-label">
					Under question title
					<span class="description">Advertisement below Question Title</span>
				</th>
				<td class="qa-form-tall-label">
					<textarea class="form-control" cols="40" rows="5" name="qw_ads_below_question_title">' . base64_decode(qa_opt('qw_ads_below_question_title')) . '</textarea>
				</td>
			</tr>
			<tr>
				<th class="qa-form-tall-label">
					After question content
					<span class="description">this advertisement will show up between Question & Answer</span>
				</th>
				<td class="qa-form-tall-label">
					<textarea class="form-control" cols="40" rows="5" name="qw_ads_after_question_content">' . base64_decode(qa_opt('qw_ads_after_question_content')) . '</textarea>
				</td>
			</tr>
			</tbody>
		</table>
	</div>';
	}
	function get_font_options($font_name = '')
    {
		$normal_fonts    = array(
			"Arial, Helvetica, sans-serif" => "Arial, Helvetica, sans-serif",
			"'Arial Black', Gadget, sans-serif" => "'Arial Black', Gadget, sans-serif",
			"'Bookman Old Style', serif" => "'Bookman Old Style', serif",
			"'Comic Sans MS', cursive" => "'Comic Sans MS', cursive",
			"Courier, monospace" => "Courier, monospace",
			"Garamond, serif" => "Garamond, serif",
			"Georgia, serif" => "Georgia, serif",
			"Impact, Charcoal, sans-serif" => "Impact, Charcoal, sans-serif",
			"'Lucida Console', Monaco, monospace" => "'Lucida Console', Monaco, monospace",
			"'Lucida Sans Unicode', 'Lucida Grande', sans-serif" => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
			"'MS Sans Serif', Geneva, sans-serif" => "'MS Sans Serif', Geneva, sans-serif",
			"'MS Serif', 'New York', sans-serif" => "'MS Serif', 'New York', sans-serif",
			"'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
			"Tahoma,Geneva, sans-serif" => "Tahoma, Geneva, sans-serif",
			"'Times New Roman', Times,serif" => "'Times New Roman', Times, serif",
			"'Trebuchet MS', Helvetica, sans-serif" => "'Trebuchet MS', Helvetica, sans-serif",
			"Verdana, Geneva, sans-serif" => "Verdana, Geneva, sans-serif"
		);
        $font_options = '<option value=""></option><optgroup label="Normal Fonts">';
        foreach ($normal_fonts as $k => $font) {
            $font_options .= '<option font-data-type="normalfont" value="' . $k . '"' . (($font_name == $k) ? ' selected' : '') . '>' . $k . '</option>';
        }
        //$font_options .= '<optgroup label="Google Fonts">';
		/* if(is_array($google_webfonts))
        foreach ($google_webfonts as $k => $font) {
            $font_options .= '<option font-data-type="googlefont" font-data-detail=\'' . json_encode($google_webfonts[$k]['variants']) . '\' value="' . $k . '"' . (($font_name == $k) ? ' selected' : '') . '>' . $k . '</option>';
        } */
        return $font_options;
    }
	
	function get_normal_font_options($font_name = '')
    {
        /* global $normal_fonts;
        $font_options = '<option value=""></option>';
        foreach ($normal_fonts as $k => $font) {
            $font_options .= '<option font-data-type="normalfont" value="' . $k . '"' . (($font_name == $k) ? ' selected' : '') . '>' . $k . '</option>';
        }
        return $font_options; */
    }
	function get_font_style_options($font_name, $style)
    {
        global $google_webfonts;
        global $normal_fonts;
        $style_options = '<option value=""></option>';
        if (($font_name == '') or (!(isset($google_webfonts[$font_name])))) {
            $style_options .= '
				<option value="400"' . (($style == "400") ? ' selected' : '') . '>Normal 400</option>
				<option value="700"' . (($style == "700") ? ' selected' : '') . '>Bold 700</option>
				<option value="400italic"' . (($style == "400italic") ? ' selected' : '') . '>Normal 400+Italic</option>
				<option value="700italic"' . (($style == "700italic") ? ' selected' : '') . '>Bold 700+Italic</option>';
        } else {
            foreach ($google_webfonts[$font_name]['variants'] as $k => $fontstyle) {
                $style_options .= '<option value="' . $fontstyle["id"] . '"' . (($style == $fontstyle["id"]) ? ' selected' : '') . '>' . $fontstyle["name"] . '</option>';
                //var_dump($style);
            }
        }
        return $style_options;
    }

    function reset_theme_options()
    {
    	// General 
    	qa_opt('logo_url', ''); /*Needs to be changed */
		qa_opt('qw_favicon_url', ''); /*Needs to be changed */
		qa_opt('qw_enable_gzip', 1 );
		qa_opt('qw_featured_image_width', 250);
		qa_opt('qw_featured_image_height', 250);
		qa_opt('qw_featured_thumbnail_width', 250);
		qa_opt('qw_featured_thumbnail_height', 250);
		qa_opt('qw_crop_x', 0);
		qa_opt('qw_crop_y', 0);
	 
		// Layout
		qa_opt('qw_nav_position', 'top');
		qa_opt('qw_nav_fixed', 1 );
		qa_opt('qw_show_icon', 1 );
		qa_opt('qw_enable_ask_button', 1 );
		qa_opt('qw_enable_category_nav', 1 );
		qa_opt('qw_enable_clean_qlist', 1 );
		qa_opt('qw_enable_except', 1 );
		qa_opt('qw_except_len', 120 );
		qa_opt('qw_enable_avatar_lists', 1 );
		if (qa_opt('qw_enable_avatar_lists'))
			qa_opt('avatar_q_list_size', 35);
		else
			qa_opt('avatar_q_list_size', 0); // set avatar size to zero so Q2A won't load them
		qa_opt('show_view_counts', 1 );
		qa_opt('qw_show_tags_list', 1 );
		qa_opt('qw_horizontal_voting_btns', 0 );
		qa_opt('qw_enble_back_to_top', 0 );
		qa_opt('qw_back_to_top_location', 'right');
	 
		// Styling
		// these color combinations needs to be changed 
		qa_opt('qw_styling_rtl', 0 );
		// social 
		qa_opt('qw_social_enable', 0 );
		// Advertisement
		qa_opt('qw_enable_adv_list', 0 );
		// footer							
		qa_opt('qw_footer_copyright', "© ".date('Y').qa_opt('site_name'));
		
		// now add the hook for other addons for reseting their settings 
		qw_do_action('qw_reset_theme_options') ;
    }
	
}

