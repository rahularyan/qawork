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
		}
		
		function qw_enqueue_css($css_src){
			$css_src['icon'] = Q_THEME_URL . '/css/fonts.css';			
			$css_src['qw_main'] = Q_THEME_URL . '/css/main.css';
			$css_src['qw_color'] = Q_THEME_URL . '/css/theme-green.css';
			$css_src['Questrial'] = 'http://fonts.googleapis.com/css?family=Questrial';
			$css_src['qw_responsive_exclude'] = Q_THEME_URL . '/css/responsive.css';
			
			if (qa_opt('qw_styling_rtl'))
				$css_src['qw_rtl'] = Q_THEME_URL . '/css/rtl.css';

			return  $css_src;
		}
		
		
		function qw_enqueue_scripts($src){		
			$src['jquery-ui'] = Q_THEME_URL . '/js/jquery-ui.min.js';
			$src['oembed'] = Q_THEME_URL . '/js/jquery.oembed.js';
			$src['bxslider'] = Q_THEME_URL . '/js/jquery.bxslider.min.js';			
			$src['qw_theme'] = Q_THEME_URL . '/js/theme.js';

			return  $src;
		}
		
		
		function qw_register_widget_positions($positions){
			$new_positions = array(
				'Top' => 'Before navbar', 
				'Header' => 'After navbar', 
				'Header Right' => 'Right side of header', 
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

	}
	
