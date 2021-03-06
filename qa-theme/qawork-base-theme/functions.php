<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	$qw_custom_hooks = new Qw_Custom_Hooks;
	
	class Qw_Custom_Hooks{
		function __construct(){
			qw_add_filter('enqueue_css', array($this, 'qw_enqueue_css'), 110);
			qw_add_filter('enqueue_scripts', array($this, 'qw_enqueue_scripts'), 110);
			qw_event_hook('widget_positions', NULL, array($this, 'qw_register_widget_positions'));
			qw_event_hook('template_array', NULL, array($this, 'qw_default_page_templates'));
			//qw_add_filter('custom_question_fields', array($this, 'qw_custom_question_field'));
			//qw_add_filter('custom_save_question_fields', array($this, 'qw_custom_save_question_field'));
			//qw_add_action('show_custom_question_fields', array($this, 'qw_show_custom_question_field'));
		}
		
		function qw_enqueue_css($css_src){
			$css_src['icon'] = array('file' => Q_THEME_URL . '/css/fonts.css');			
			$css_src['qw_main'] = array('file' => Q_THEME_URL . '/css/main.css');
			$css_src['qw_color'] = array('file' => Q_THEME_URL . '/css/theme-green.css');
			$css_src['Questrial'] = array('file' => 'http://fonts.googleapis.com/css?family=Questrial');
			$css_src['qw_responsive'] = array('file' => Q_THEME_URL . '/css/responsive.css', 'exclude' => true);
			
			if(qa_opt('qw_styling_rtl'))
				$css_src['qw_rtl_exclude'] = array('file' => Q_THEME_URL . '/css/rtl.css', 'exclude' => true);
			
			if (qa_opt('qw_styling_rtl'))
				$css_src['qw_rtl'] = array('file' => Q_THEME_URL . '/css/rtl.css', 'exclude' => true);

			return  $css_src;
		}
		
		
		function qw_enqueue_scripts($src){		
			$src['jquery-ui'] = array('file' => Q_THEME_URL . '/js/jquery-ui.min.js', 'footer' => true);
			$src['oembed'] = array('file' => Q_THEME_URL . '/js/jquery.oembed.js', 'footer' => true);
			$src['bxslider'] = array('file' =>  Q_THEME_URL . '/js/jquery.bxslider.min.js');			
			$src['qw_theme'] = array('file' => Q_THEME_URL . '/js/theme.js');

			return  $src;
		}
		
		
		function qw_register_widget_positions($positions){
			$new_positions = array(
				'Top' => 'Before navbar', 
				'Header' => 'After navbar', 
				'Header Right' => 'Right side of header', 
				'Header Below' => 'Below header', 
				'Ask Form' => 'For showing ask form', 
				'Breadcrumbs' => 'For show bread navigation', 
				'Left' => 'Right side below menu', 
				'Content Top' => 'Before questions list', 
				'Content Bottom' => 'After questions lists', 
				'Right' => 'Right side of content', 
				'Bottom' => 'Below content and before footer',
				'Home Slide' => 'Home Top',
				'Home Top Users' => 'For showing top users',
				'Home Activity' => 'For showing activity',
				'Home Right' => 'Home right side',						
				'Home Bottom' => 'Home below activity',						
				'Question Right' => 'Right side of question',
				'User Content' => 'On user page',
				'User Right' => 'Right side of user page',
				'Profile Left Bottom' => 'Left side of user page',
				'Profile Left Top' => 'Left side of user page',
				'Profile Right' => 'Right side of user page',
				'Profile Bottom' => 'Profile bottom',
				'Site Counts' => 'Above footer position',
				'Footer 1' => 'footer position',
				'Footer 1 Right' => 'footer 1 right',
			);
			return array_merge($positions, $new_positions); 
		}
			
			
		function qw_default_page_templates(){
				return array(
				'qa' 			=> 'QA',
				'home' 			=> 'Home',
				'ask' 			=> 'Ask',
				'question' 		=> 'Question',
				'questions' 	=> 'Questions',
				'activity' 		=> 'Activity',
				'unanswered' 	=> 'Unanswered',
				'hot' 			=> 'Hot',
				'tags' 			=> 'Tags',
				'tag' 			=> 'Tag',
				'categories' 	=> 'Categories',
				'users' 		=> 'Users',
				'user' 			=> 'User',
				'account' 		=> 'Account',
				'favorite' 		=> 'Favorite',
				'user-wall' 	=> 'User Wall',
				'user-activity' => 'User Activity',
				'user-questions' => 'User Questions',
				'user-answers' 	=> 'User Answers',
				'custom' 		=> 'Custom',
				//'login' 		=> 'Login',
				'feedback' 		=> 'Feedback',
				'updates' 		=> 'Updates',
				'search' 		=> 'Search',
				///'not-found' 	=> 'Not Found',
				'plugin' 		=> 'Plugin Pages',
				//'admin' 		=> 'Admin'
			);
		}
		
		function qw_custom_question_field($form){

			$form['qw_custom'] = array(
				'label' => 'Custom Field',
				'tags' => 'name="qw_custom"',
				'type' => 'text',
				'value' => ''
			);
			$form['qw_coding'] = array(
				'label' => 'Your coding language',
				'tags' => 'name="qw_coding"',
				'type' => 'select',
				'options' => array('PHP' => 'PHP', 'JavaScript' => 'JavaScript', 'HTML' => 'HTML', 'Visual Basic' => 'Visual Basic')
			);

			return $form;
		}

		function qw_custom_save_question_field($fields){
			$fields['qw_custom'] = qa_post_text('qw_custom');
			$fields['qw_coding'] = qa_post_text('qw_coding');
			return $fields;
		}

		
		function qw_show_custom_question_field($fields, $post){
			$output = '';
			if(isset($fields['qw_coding']))
				$output .= '<p>I love '.$fields['qw_coding'].'</p>';
			
			if(isset($fields['qw_custom']))
				$output .= '<p>My reason: '.$fields['qw_custom'].'</p>';
				
			return $output;
		}	

	}
