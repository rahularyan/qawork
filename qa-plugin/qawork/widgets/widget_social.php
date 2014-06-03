<?php
	class cs_social_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'inline' => array(
						'label' => 'Inline',
						'type' => 'checkbox',
						'tags' => 'name="inline"',
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
		

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			if (! qa_opt('cs_social_enable')) 
				return;
				
			$widget_opt = @$themeobject->current_widget['param']['options'];

			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">Social Links</h3>');
	
			$themeobject->output('<div class="ra-social-widget clearfix '.(!!$widget_opt['inline'] ? 'inline' : 'block').'">');
			

			$links = json_decode(qa_opt('cs_social_list'));
			
			if(!empty($links)){
				$themeobject->output('<ul>');
				foreach ($links as $link) {
					$icon  = ($link->social_icon != '1' ? ' ' . $link->social_icon . '' : '');
					$image = ($link->social_icon == '1' ? '<img src="' . $link->social_icon_file . '" />' : '');
					$themeobject->output('<li><a class="' . @$icon . '" href="' . $link->social_link . '" title="Link to ' . $link->social_title . '" >' . @$image . '</a></li>');
				}
				$themeobject->output('</ul>');
			}

			
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/