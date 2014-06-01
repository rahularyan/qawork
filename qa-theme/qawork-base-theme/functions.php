<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	$cs_custom_hooks = new Cs_Custom_Hooks;
	
	class Cs_Custom_Hooks{
		function __construct(){
			cs_event_hook('enqueue_css', NULL, array($this, 'cs_enqueue_css'));
			cs_event_hook('enqueue_scripts', NULL, array($this, 'cs_enqueue_scripts'));
			cs_event_hook('widget_positions', NULL, array($this, 'cs_register_widget_positions'));
			cs_event_hook('template_array', NULL, array($this, 'cs_default_page_templates'));
		}
		
		function cs_enqueue_css($css_src){
			$css_src['icon'] = Q_THEME_URL . '/css/fonts.css';		
			$css_src['cs_responsive'] = Q_THEME_URL . '/css/responsive.css';
			$css_src['cs_main'] = Q_THEME_URL . '/css/main.css';
			$css_src['cs_color'] = Q_THEME_URL . '/css/theme-green.css';
			$css_src['Questrial'] = '//fonts.googleapis.com/css?family=Questrial';
			
			if (qa_opt('cs_styling_rtl'))
				$css_src['cs_rtl'] = Q_THEME_URL . '/css/rtl.css';

			return  $css_src;
		}
		
		
		function cs_enqueue_scripts($src){		
			$src['jquery-ui'] = Q_THEME_URL . '/js/jquery-ui.min.js';
			$src['oembed'] = Q_THEME_URL . '/js/jquery.oembed.js';
			$src['bxslider'] = Q_THEME_URL . '/js/jquery.bxslider.min.js';			
			$src['cs_theme'] = Q_THEME_URL . '/js/theme.js';

			return  $src;
		}
		
		
		function cs_register_widget_positions($positions){
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
				'Home Featured' => 'For featured questions',
				'Home Count' => 'For showing activity count',
				'Home Top Users' => 'For showing top users',
				'Home Activity' => 'For showing activity',
				'Home Right' => 'Home right side',						
				'Question Right' => 'Right side of question',
				'User Content' => 'On user page',
				'User Right' => 'Right side of user page',
				'Profile Left Bottom' => 'Left side of user page',
				'Profile Left Top' => 'Left side of user page',
				'Profile Right' => 'Right side of user page',
				'Profile Bottom' => 'Profile bottom'
			);
			return array_merge($positions, $new_positions); 
		}
			
			
		function cs_default_page_templates(){
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
	
