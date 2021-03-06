<?php
	class qw_followers_widget {

		function qw_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'qw_nu_count' => array(
						'label' => 'Numbers of user',
						'type' => 'number',
						'tags' => 'name="qw_nu_count" class="form-control"',
						'value' => '10',
					),
					'qw_nu_avatar' => array(
						'label' => 'Avatar Size',
						'type' => 'number',
						'tags' => 'name="qw_nu_avatar" class="form-control"',
						'value' => '30',
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
		
		
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$handle = qa_request_part(1);
			$widget_opt = $themeobject->current_widget['param']['options'];
			$account = $qa_content['active_user'];

			
			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">'.qa_lang_sub('cleanstrap/x_followers', $account['followers']).' <a href="'.qa_path_html('followers/'.$handle).'">'. qa_lang_html('cleanstrap/view_more') .'</a></h3>');
			
	
			$themeobject->output('<div class="ra-followers-widget">');
				
				if($account['followers'] <1){
					$themeobject->output('<div class="no-followers"><span>'.qa_lang_sub('cleanstrap/follow_x_for_updates',$handle ).'</span>');
					$themeobject->favorite();
					$themeobject->output('</div>');
				}else
					$themeobject->output(qw_followers_list($handle, $widget_opt['qw_nu_avatar'], $widget_opt['qw_nu_count']));
			$themeobject->output('</div>');
		
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/