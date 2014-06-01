<?php
	class cs_featured_questions_widget {
		
		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_fq_count' => array(
						'label' => 'Questions to show',
						'type' => 'number',
						'tags' => 'name="cs_fq_count"',
						'value' => '10',
					),

				'scroll' => array(
					'label' => 'Scroll',
					'type' => 'checkbox',
					'tags' => 'name="scroll"',
					'value' => '1',
				),
				'slide_width' => array(
					'label' => 'Slide width',
					'type' => 'number',
					'tags' => 'name="slide_width" class="form-control"',
					'value' => '1400',
				),
				'min_slides' => array(
					'label' => 'Min. Slide',
					'type' => 'number',
					'tags' => 'name="min_slide" class="form-control"',
					'value' => '10',
				),
				'max_slides' => array(
					'label' => 'Max. Slide',
					'type' => 'number',
					'tags' => 'name="max_slide" class="form-control"',
					'value' => '10',
				),
				'slide_margin' => array(
					'label' => 'Slide margin',
					'type' => 'number',
					'tags' => 'name="slide_margin" class="form-control"',
					'value' => '10',
				),
				'auto' => array(
					'label' => 'Auto transition',
					'type' => 'checkbox',
					'tags' => 'name="auto"',
					'value' => '1',
				)
	
				),

			);
		}

		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
				case 'activity':
				case 'qa':
				case 'questions':
				case 'hot':
				case 'ask':
				case 'categories':
				case 'question':
				case 'tag':
				case 'tags':
				case 'unanswered':
				case 'user':
				case 'users':
				case 'search':
				case 'admin':
				case 'custom':
					$allow=true;
					break;
			}
			
			return $allow;
		}

		
		function allow_region($region)
		{
			$allow=false;
			
			switch ($region)
			{
				case 'main':
				case 'side':
				case 'full':
					$allow=true;
					break;
			}
			
			return $allow;
		}
		

		// output the list of selected post type
		function carousel_item($type, $limit){
			
			if (defined('QA_FINAL_WORDPRESS_INTEGRATE_PATH')){
				$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^postmetas, ^posts WHERE ^posts.type=$ and ( ^postmetas.postid = ^posts.postid and ^postmetas.title = "featured_question" ) ORDER BY ^posts.created DESC LIMIT #', $type, $limit));
				global $wpdb;
			}else
				$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT ^posts.*, ^users.*, ^ra_media.id as media_id, ^ra_media.type as media_type, ^ra_media.name as media_name, ^ra_media.title as media_title, ^ra_media.description as media_description FROM ^posts LEFT JOIN ^users ON  ^posts.userid=^users.userid LEFT JOIN ^ra_media ON ^ra_media.parent_post = ^posts.postid WHERE ^posts.type=$ ORDER BY ^posts.created DESC LIMIT #', $type, $limit));
	
			$output ='';
			foreach($posts as $p){
				
				$when = qa_when_to_html(strtotime($p['created']), 7);
				$avatar = cs_get_post_avatar($p, 35, false);
				
				if($p['type']=='Q'){
					$link_header = qa_q_path_html($p['postid'], $p['title']) .'" title="'. $p['title'];
				}elseif($p['type']=='A'){
					$link_header = cs_post_link($p['parentid']).'#a'.$p['postid'];
				}else{
					$link_header = cs_post_link($p['parentid']).'#c'.$p['postid'];
				}
				$handle = $p['handle'];
				$output .='<div class="item slide">';
				$output .='<div class="item-inner">';
				
				if (!empty($p['media_name']))
					$output .= '<a class="featured-image" href="'.$link_header.'"><img src="'.cs_media_filename(array('name' => $p['media_name'], 'type' =>$p['media_type']), 'large').'" /></a>';
					
				if($avatar)	$output .= '<div class="avatar" data-id="'.$p['userid'].'" data-handle="'.$handle.'">'.$avatar.'</div>';	
				$output .= '<div class="no-overflow">';
				
				$output .='<div class="inner-content">';
				if($p['type']=='Q'){
					$what = qa_lang('cleanstrap/asked');
				}elseif($p['type']=='A'){
					$what = qa_lang('cleanstrap/answered');
				}elseif($p['type'] == 'C'){
					$what = qa_lang('cleanstrap/commented');
				}
				
				$output .= '<a class="title" href="'.$link_header.'">' . cs_truncate(strip_tags($p['title']), 100).'</a>';
				$output .='</div>';
				$output .='</div>';
					
				$output .= '<p class="content" >' . cs_truncate(strip_tags($p['content']), 150).'</p>';
				
				$output .= '<div class="post-meta clearfix">';
				$output .= '<span class="icon-time">'.implode(' ', $when).'</span>';
				$output .= '<span class="vote-count icon-answer">'.qa_lang_sub('cleanstrap/x_answers', $p['acount']).'</span>';	
				$output .= '<span class="vote-count icon-thumb-up">'.qa_lang_sub('cleanstrap/x_votes', $p['netvotes']).'</span>';
				$output .= '</div>';
				
				$output .='</div>';
				$output .='</div>';
			}
			

			return $output;
		}
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = $themeobject->current_widget['param']['options'];

			$count = (isset($widget_opt['cs_fq_count']) && !empty($widget_opt['cs_fq_count'])) ?(int)$widget_opt['cs_fq_count'] : 10;

			$opt_array = htmlspecialchars(json_encode(array('slideWidth' => $widget_opt['slide_width'], 'slideMargin' => $widget_opt['slide_margin'], 'auto' => ($widget_opt['slide_width'] ? 'true' : 'false'), 'minSlides' => $widget_opt['min_slide'], 'maxSlides' => $widget_opt['max_slide'], 'prevText' => '<i class="icon-chevron-left"></i>', 'nextText' => '<i class="icon-chevron-right"></i>' )), ENT_QUOTES, 'UTF-8');
			$slider	= ($widget_opt['scroll'] ? 'data-action="slider" data-opt="'.$opt_array.'"' : '');
			
			$themeobject->output('<div class="ra-featured-widget" '.$slider.'>');
			
			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">'.qa_lang('cleanstrap/featured_question').'</h3>');
				
			$themeobject->output('

            <div class="featured-questions clearfix">
                '.$this->carousel_item('Q', $count).'                
            </div>

			');
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/