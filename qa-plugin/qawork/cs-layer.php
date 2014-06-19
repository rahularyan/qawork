<?php

class qa_html_theme_layer extends qa_html_theme_base {
	var $postid;
	var $widgets;
	function doctype(){	
		global $widgets;
        $widgets       = get_all_widgets();
        $this->widgets = $widgets;

		if(qw_is_user()){
			$handle = qa_request_part(1);
			if(empty($handle)) $handle = qa_get_logged_in_handle();
			$userid = qa_handle_to_userid($handle);
			$this->content['active_user'] = qw_user_data($handle);
			$this->content['active_user_profile'] = qw_user_profile($handle);
		}
		

		if (isset($_REQUEST['qw_ajax_html'])) {
            $action = 'qw_ajax_' . $_REQUEST['action'];
            if (method_exists($this, $action))
                $this->$action();
        } else {
			if(qw_is_user() && empty($this->content['navigation']['sub']['profile'])){
				$userid=qa_get_logged_in_userid();
				$this->content['navigation']['sub']=qa_user_sub_navigation($handle, 'profile',	$userid );
			}
            $this->output('<!DOCTYPE html>');
            
            unset($this->content['navigation']['main']['ask']);
            unset($this->content['navigation']['main']['admin']);
			
			if((bool)qa_opt('qw_enable_category_nav'))
				unset($this->content['navigation']['main']['categories']);

			if(qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)	{

				// theme installation & update
				/* $version = qa_opt('qw_version');
				if( QW_VERSION > $version )
					qa_redirect('qw_installation'); */

				//show theme option menu if user is admin
				$this->content['navigation']['user']['themeoptions'] = array(
					'label' => qa_lang('cleanstrap/theme_options'),
					'url' => qa_path_html('themeoptions'),
					'icon' => 'icon-spanner'
				);
				$this->content['navigation']['user']['themewidgets'] = array(
					'label' => 'Theme Widgets',
					'url' => qa_path_html('themewidgets'),
					'icon' => 'icon-puzzle',
				);
				
				if ($this->request == 'themeoptions') {
					$this->content['navigation']['user']['themeoptions']['selected'] = true;
					$this->content['navigation']['user']['selected']                 = true;
					
					$this->template = 'themeoptions';
				}
				if($this->request == 'themewidgets') {
					$this->content['navigation']['user']['themewidgets']['selected'] = true;
					$this->content['navigation']['user']['selected'] = true;
					$this->template = 'widgets';
				}
			
			}
			
			if (qw_hook_exist('custom_question_fields') && (isset($this->content['form_q_edit']) || qa_request_part(0) == 'ask')){
				
				$form = array();
				$form = qw_apply_filter('custom_question_fields', $form);
				
				if(!empty($form) && is_array($form)){					
					if($this->template =='ask') 
						$this->content['form']['fields'] = array_merge($this->content['form']['fields'], $form) ;
					else{
						require_once QA_INCLUDE_DIR.'qa-db-metas.php';
						
						$fields = array_keys($form);
						$values = qa_db_postmeta_get(qa_request_part(0), $fields);
						foreach ($form as $k => $value){
							if(!empty($values[$k]))
								@$form[$k]['value'] = @$values[$k];
						}
						$this->content['form_q_edit']['fields'] = array_merge($this->content['form_q_edit']['fields'], $form) ;

					}
				}
			}
			
		}

		if(qw_hook_exist('doctype'))
			$this->content = qw_apply_filter('doctype', $this->content);
	}
	
	function head_script()
	{
		//register a hook
		if(qw_hook_exist('head_script'))
			$this->output(qw_do_action('head_script', $this));
			
		$scripts = qw_get_all_scripts($this->template);
		// unset old jQuery
		if($this->content['script'] && ($key = qw_array_find('jquery-1.7.2.min.js', $this->content['script'])) !== false) {
			unset($this->content['script'][$key]);
		}
		if($this->content['script_rel'] && ($key = qw_array_find('jquery-1.7.2.min.js', $this->content['script'])) !== false) {
			unset($this->content['script_rel'][$key]);
		}

		$this->output('<script> ajax_url = "' . QW_CONTROL_URL . '/ajax.php"; theme_url = "' . Q_THEME_URL . '";</script>');

		qa_html_theme_base::head_script();


		if (qa_opt('qw_enable_gzip')){ //Gzip
			$this->output('<script type="text/javascript" src="'.QW_CONTROL_URL.'/gzip.php?type=js"></script>');
			
			if (!empty($scripts))
				foreach ($scripts as $script_src){
					// load if external url
					if(!qw_is_internal_link($script_src) && filter_var($script_src, FILTER_VALIDATE_URL) !== FALSE)
						$this->output('<script type="text/javascript" src="'.$script_src.'"></script>');
				}
		}else{
			if (!empty($scripts))
				foreach ($scripts as $script_src){
					if(filter_var($script_src, FILTER_VALIDATE_URL) !== FALSE)
						$this->output('<script type="text/javascript" src="'.$script_src.'"></script>');
				}
		}

		
		if($this->qw_is_widget_active('QW Ask Form') && $this->template != 'ask'){
			$this->output('<script type="text/javascript" src="'.get_base_url().'/qa-content/qa-ask.js"></script>');
			
			list($categories, $completetags)=qa_db_select_with_pending(
				qa_db_category_nav_selectspec(qa_get('cat'), true),
				qa_db_popular_tags_selectspec(0, QA_DB_RETRIEVE_COMPLETE_TAGS)
			);
			
			if(qa_using_tags()){
				$completetags = qa_opt('do_complete_tags') ? array_keys($completetags) : array();
				$a_template='<a href="#" class="qa-tag-link" onclick="return qa_tag_click(this);">^</a>';
				$this->output('<script type="text/javascript">
					var qa_tag_template = \''.$a_template.'\',
						qa_tag_onlycomma = \''.(int)qa_opt('tag_separator_comma').'\',
						qa_tags_examples = "",
						qa_tags_complete = \''.qa_html(implode(',', $completetags)).'\',
						qa_tags_max = "'.(int)qa_opt('page_size_ask_tags').'";
				</script>');
			}
			
			
			if (qa_using_categories() && count($categories)) {
				$pathcategories=qa_category_path($categories, qa_get('cat'));
				$startpath='';
				foreach ($pathcategories as $category)
					$startpath.='/'.$category['categoryid'];
				$allownosub = qa_opt('allow_no_sub_category');
			}
			
			

			$this->output('
			<script type="text/javascript">
				var qa_cat_exclude=\'' . qa_opt('allow_no_sub_category') . '\';
				var qa_cat_allownone=1;
				var qa_cat_allownosub=' . (int)qa_opt('allow_no_sub_category') . ';
				var qa_cat_maxdepth=' . QA_CATEGORY_DEPTH . ';
				qa_category_select(\'category\', '.qa_js($startpath).');
			</script>');
		}
	}
	
	
	function head_css()
	{
		$css 	= qw_get_all_styles($this->template);
		if (qa_opt('qw_enable_gzip')){ //Gzip
			$this->output('<link href="'.QW_CONTROL_URL.'/gzip.php?type=css" rel="stylesheet" type="text/css">');
										
			if (!empty($css))
				foreach ($css as $k => $css_src){
					if((!qw_is_internal_link($css_src) && filter_var($css_src, FILTER_VALIDATE_URL)) || (strpos($k,'exclude') !== false))
						$this->output('<link rel="stylesheet" type="text/css" href="'.$css_src.'"/>');
				}
		}else{
			if (!empty($css))
			foreach ($css as $css_src){
				if(filter_var($css_src, FILTER_VALIDATE_URL) !== FALSE)
					$this->output('<link rel="stylesheet" type="text/css" href="'.$css_src.'"/>');
			}
				
			if (!empty($this->content['notices']))
				$this->output(
					'<style><!--',
					'.qa-body-js-on .qa-notice {display:none;}',
					'//--></style>'
				);
		}
		qa_html_theme_base::head_css();	
		$this->output('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
			<meta http-equiv="X-UA-Compatible" content="IE=edge"> ');

		$fav = qa_opt('qw_favicon_url');
		if( !!$fav )
			$this->output('<link rel="shortcut icon" href="' .  $fav . '" type="image/x-icon">');
		else
			$this->output('<link rel="shortcut icon" href="' .  Q_THEME_URL . '/images/ico/favicon.png" type="image/x-icon">');
		
		$big_fav = qa_opt('qw_favicon_url');
		if( !!$big_fav )
			$this->output('<link rel="shortcut icon" href="' .  $big_fav . '" sizes="144x144" rel="apple-touch-icon-precomposed">');
		else
			$this->output('<link rel="shortcut icon" href="' .  Q_THEME_URL . '/images/ico/apple-touch-icon-144-precomposed.png" sizes="144x144" rel="apple-touch-icon-precomposed">');
		$this->output('

				<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
				<!--[if lte IE 9]>
					<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/ie.css"/>
				  <script src="' . Q_THEME_URL . '/js/html5shiv.js"></script>
				  <script src="' . Q_THEME_URL . '/js/respond.min.js"></script>
				<![endif]-->
			');
		
		
		if (qa_opt('qw_custom_style_created')){
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/dynamic.css"/>');
		}else{
			$css = qa_opt('qw_custom_css');
			$this->output('<style>' . $css . '</style>');
		}
	
		//register a hook
		if(qw_hook_exist('head_css'))
			$this->output(qw_do_action('head_css', $this));
		
	}
		
	function form_field($field, $style)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }

        if (@$field['type'] == 'qw_qaads_multi_text') {
            $this->form_prefix($field, $style);
            $this->qw_qaads_form_multi_text($field, $style);
            $this->form_suffix($field, $style);
            
        } else {
            qa_html_theme_base::form_field($field, $style); // call back through to the default function
        }
    }
    
    function qw_qaads_form_multi_text($field, $style)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="ra-multitext"><div class="ra-multitext-append">');
        
        $i = 0;
        
        if ((strlen($field['value']) != 0) && is_array(unserialize($field['value']))) {
            $links = unserialize($field['value']);
            foreach ($links as $k => $ads) {
                
                $this->output('<div class="ra-multitext-list" data-id="' . $field['id'] . '">');
                $this->output('<input name="' . $field['id'] . '[' . $k . '][name]" type="text" value="' . $ads['name'] . '" class="ra-input name" placeholder="' . $field['input_label'] . '" />');
                
                $this->output('<textarea name="' . $field['id'] . '[' . $k . '][code]" class="ra-input code"  placeholder="Your advertisement code.." />' . str_replace('\\', '', base64_decode($ads['code'])) . '</textarea>');
                
                $this->output('<span class="ra-multitext-delete icon-trashcan btn btn-danger btn-xs">Remove</span>');
                $this->output('</div>');
            }
        } else {
            $this->output('<div class="ra-multitext-list" data-id="' . $field['id'] . '">');
            $this->output('<input name="' . $field['id'] . '[0][name]" type="text"  class="ra-input name" placeholder="' . $field['input_label'] . '" />');
            $this->output('<textarea name="' . $field['id'] . '[0][code]" class="ra-input code" placeholder="Your advertisement code.."></textarea>');
            
            $this->output('<span class="ra-multitext-delete icon-trashcan btn btn-danger btn-xs">Remove</span>');
            
            $this->output('</div>');
        }
        
        
        $this->output('</div></div>');
        $this->output('<span class="ra-multitext-add icon-plus btn btn-primary btn-xs" title="Add more">Add more</span>');
    }
    

    
    function q_list_items($q_items)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (qa_opt('qw_enable_adv_list')) {
            $advs = json_decode(qa_opt('qw_advs'), true);
            foreach ($advs as $k => $adv) {
                $advertisments[@$adv['adv_location']][] = $adv;
            }

            $i = 0;
            foreach ($q_items as $q_item) {
                $this->q_list_item($q_item);
                if (isset($advertisments[$i])) {
                    foreach ($advertisments[$i] as $k => $adv) {
                        $this->output('<div class="cs-advertisement">');
                        if (isset($adv['adv_adsense']))
                            $this->output(str_replace('\\', '', $adv['adv_adsense']));
                        else {
                            if (isset($adv['adv_image']))
                                $this->output('<a href="' . $adv['adv_image_link'] . '"><img src="' . $adv['adv_image'] . '" title="' . $adv['adv_image_title'] . '" alt="advert" /></a>');
                            else
                                $this->output('<a href="' . $adv['adv_image_link'] . '">' . $adv['adv_image_title'] . '</a>');
                        }
                        $this->output('</div>');
                    }
                }
                $i++;
            }
        } else
            qa_html_theme_base::q_list_items($q_items);
    }
	
	
	function install_page(){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$content = $this->content;
		$this->output('<div class="clearfix qa-main container ' . (@$this->content['hidden'] ? ' qa-main-hidden' : '') . '">');
		$this->main_parts($content);
		$this->output('</div>');
		$this->output('<div class="install-footer">Copyright &copy; RahulAryan</div>');
	}
	
	function html()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (isset($_REQUEST['qw_ajax_html'])) {
            return;
        } else {
            $this->output('<html lang="' . qa_opt('site_language') . '">');
            
            $this->head();
            $this->body();
            
            $this->output('</html>');
        }

    }
    
    function body_tags()
    {
        if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('id="nav-' . qa_opt('qw_nav_position') . '"');
        qa_html_theme_base::body_tags();
    }
    function finish()
    {
        if (isset($_REQUEST['qw_ajax_html'])) {
            return;
        }
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
    }

    function body()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<body');
        $this->body_tags();
        $this->output('>');
        
        $this->body_script();
        $this->body_header();
        $this->body_content();
        $this->body_footer();
        $this->body_hidden();
        
        $this->output('</body>');
    }

    
    
    function body_content()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->body_prefix();
        $this->notices();
        $this->header();
		$this->main_top($this->content);
        $this->output('<div class="clearfix qa-main ' .(qw_is_user() ? 'cs-user-template' : ''). (@$this->content['hidden'] ? ' qa-main-hidden' : '') . '">');
		
		if(qw_is_home())			
			include_once Q_THEME_DIR.'/home.php';
			
		else{
		
			if($this->qw_position_active('Home Slide') && qw_is_home()){
				$this->output('<div class="home-slider-outer"><div class="container">');
				$this->qw_position('Home Slide');
				$this->output('</div></div>');
			}
			
			$this->output('<div class="container"><div class="'.($this->template == 'admin' ? 'lr-table' : 'row').'">');
			
			if ($this->template == 'admin'){
				$this->output('<div class="left-side">');
				$this->nav('sub');
				$this->output('</div>');
			}
			
			if ($this->template == 'admin')
				$this->output('<div class="left-content">');
			else
				$this->output('<div class="left-content '. ($this->qw_position_active('Right') ? 'col-md-8' : 'col-md-12').'">');
				
			if ($this->template != 'question')
				$this->qw_position('Content Top');

			if (isset($this->content['error']) && $this->template != 'not-found')
				$this->error(@$this->content['error']);

				if(!qw_is_user() && $this->template != 'admin')
					$this->nav('sub');
					
				$this->main();

			
			if ($this->template != 'question')
				$this->page_links();			
			$this->output('</div>');
			
			if ($this->template != 'admin')
				$this->sidepanel();
				
			$this->output('</div></div>');
		}
        $this->output('</div>');
        
		$this->footer();
		 
        $this->body_suffix();
        if ((qa_opt('qw_enble_back_to_top')) && (qa_opt('qw_back_to_top_location') == 'right'))
            $this->output('<a id="back-to-top" class="back-to-top-right icon-arrow-up-thick t-bg" href="#"></a>');
    }
    function header()
    {
		$update_check = qa_opt('qw_update_check');
		if(qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN && !$update_check && ($update_check+86430) < time()){
			$this->output(qw_check_for_new_version(true));
			qa_opt('qw_update_check', time());
		}
		
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->qw_position('Top');
        
        $this->output('<header id="site-header" class="clearfix">');		
			$this->output('<div id="header-top" class="clearfix">');
			$this->output('<div class="container">');
			$this->output('<div class="logo-menu">');
			$this->logo();			
			$this->nav_ask_btn();
			$this->output('</div>');
			
			$this->output('<div class="user-navs">');
				$this->output('<a href="#" class="mobile-menu-toggle icon-th-menu">Menu</a>');
				$this->main_nav_menu();
				
				if ( (qa_opt('qw_enable_category_nav')) && (qa_using_categories()) )
					$this->cat_drop_nav();
				
				$this->user_drop_nav();						
			$this->output('</div></div>');
			$this->output('</div>');
		$this->output('</header>');
		
		$this->get_social_links();
		$this->qw_page_title();
		
		if(!qw_is_home() && !qw_is_user()){
			$this->output('<div id="header-below" class="clearfix"><div class="container">');		
			$this->qw_position('Breadcrumbs');		
			$this->search();			
			$this->output('</div></div>');
		}
    }
	function main_nav_menu(){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }		
		
		$this->output('<nav class="pull-left clearfix">');
			
		$this->nav('main');
		
		$this->output('</nav>');		 		
	}
	function nav_ask_btn(){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		if (qa_opt('qw_enable_ask_button')){
			$this->output('<a id="nav-ask-btn" href="' . qa_path_html('ask') . '" class="btn">' . qa_lang_html('cleanstrap/ask_question') . '</a>');
		}
	}

    function site_top()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
        $this->output('<div id="site-top" class="container">');
        $this->page_title_error();
        if (qa_is_logged_in()) { // output user avatar to login bar
            $this->output('<div class="qa-logged-in-avatar">', QA_FINAL_EXTERNAL_USERS ? qa_get_external_avatar_html(qa_get_logged_in_userid(), 24, true) : qa_get_user_avatar_html(qa_get_logged_in_flags(), qa_get_logged_in_email(), qa_get_logged_in_handle(), qa_get_logged_in_user_field('avatarblobid'), qa_get_logged_in_user_field('avatarwidth'), qa_get_logged_in_user_field('avatarheight'), 24, true), '</div>');
        } else {
            $this->output('<ul class="pull-right top-buttons clearfix">', '<li><a href="#" class="btn">' . qa_lang_html('cleanstrap/login') . '</a></li>', '<li><a href="#" class="btn">' . qa_lang_html('cleanstrap/register') . '</a></li>', '</ul>');
        }
        $this->output('</div>');
    }
    
    function logo()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
        $logo = qa_opt('logo_url');
        $this->output('<div class="site-logo">', '<a class="navbar-brand'.(!!$logo ? '' :' icon-qawork').'" title="' . strip_tags($this->content['logo']) . '" href="' . get_base_url() . '">
						'.($logo ? '<img class="navbar-site-logo" src="' . $logo . '">' : '').'
					</a>', '</div>');
    }
	
    function cat_drop_nav()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
        ob_start();
?>
			<div class="nav navbar-nav category-nav pull-left">
				<a data-toggle="dropdown" href="#" class="category-toggle icon-folder" title="<?php echo qa_lang_html('cleanstrap/categories'); ?>"></a>					
				<?php $this->qw_full_categories_list(); ?>
			</div>
			<?php
        $this->output(ob_get_clean());
    }
    function user_drop_nav()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        ob_start();
        if (qa_is_logged_in()) {
            
?>
				<ul class="nav navbar-nav navbar-avatar pull-right">
					<li class="dropdown pull-right" id="menuLogin">
						<a id="profile-link" data-toggle="dropdown" href="<?php
            echo qa_path_html('user/' . qa_get_logged_in_handle());
?>" class="avatar">
							<?php
            $LoggedinUserAvatar = qw_get_avatar(qa_get_logged_in_handle(), 25, false);
            if (!empty($LoggedinUserAvatar))
                echo '<img src="' . $LoggedinUserAvatar . '" />'; // in case there is no Avatar image and theme doesn't use a default avatar
            else
                echo '<span class="profile-name">' . qa_get_logged_in_handle() . '</span>';
?>
						</a>
						<ul class="user-nav dropdown-menu">
							
							<?php
			if(qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)	
				$this->content['navigation']['user']['admin'] = array('label' => qa_lang_html('cleanstrap/admin'), 'url' => qa_path_html('admin'), 'icon'=> 'icon-spanner');
				
			$this->content['navigation']['user']['profile'] = array('label' => qa_lang_html('cleanstrap/profile'), 'url' => qa_path_html('user/' . qa_get_logged_in_handle()), 'icon'=> 'icon-user');
			$this->content['navigation']['user']['updates']['icon'] = 'icon-rss';
			$this->content['navigation']['user']['account'] = array('label' => qa_lang('cleanstrap/account'), 'url' => qa_path_html('account'), 'icon' => 'icon-cog');
			$this->content['navigation']['user']['favorites'] = array('label' => qa_lang('cleanstrap/favorites'), 'url' => qa_path_html('favorites'), 'icon' =>'icon-heart');
			
			$user_menu = array_merge(array_flip(array('admin', 'themewidgets', 'themeoptions', 'profile')), $this->content['navigation']['user']);

            foreach ($user_menu as $k => $a) {
                if (isset($a['url']) && $k != 'logout') {
                    $icon = (isset($a['icon']) ? ' class="' . $a['icon'] . '" ' : '');
                    echo '<li class="user-nav-'.$k . (isset($a['selected']) ? ' active' : '') . '"><a' . $icon . ' href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
                }
            }
			
           // if (isset($this->content['navigation']['user']['logout']['url'])) {
                $link = qa_path_html("logout");
                echo "<li><a class='icon-power' href = '$link'> " . qa_lang_html('cleanstrap/logout') . " </a></li>";
           // }
?>
						</ul>
					</li>
				</ul>
			
			<?php
			$this->qw_notification_btn();
        } else {
?>				<div id="login-drop" class="dropdown pull-right">
					<a class="icon-key login-register"  href="#" title="<?php echo qa_lang_html('cleanstrap/login_register'); ?>" data-toggle="dropdown"><span><?php echo qa_lang_html('cleanstrap/login'); ?></span></a>
					<div class="dropdown-menu login-drop">
						<div class="social-logins">
						
							<?php      
								if(!empty($this->content['navigation']['user']))
								foreach ($this->content['navigation']['user'] as $k => $custom) {
									if (isset($custom) && (($k != 'login') && ($k != 'register'))) {
										preg_match('/class="([^"]+)"/', @$custom['label'], $class);
										
										if ($k == 'facebook')
											$icon = 'class="' . @$class[1] . ' icon-social-facebook"';
										elseif ($k == 'google')
											$icon = 'class="' . @$class[1] . ' icon-social-google"';
										elseif ($k == 'twitter')
											$icon = 'class="' . @$class[1] . ' icon-social-twitter"';
										
										$this->output(str_replace(@$class[0], @$icon, @$custom['label']));
									}
								}	
							?>
						</div>
						<p><span>OR</span></p>
						<form role="form" action="<?php
            echo $this->content['navigation']['user']['login']['url'];
?>" method="post">
							  <input type="text" class="form-control" id="qa-userid" name="emailhandle" placeholder="<?php
            echo trim(qa_lang_html('users/email_handle_label'), ':');
?>" />
							
							  <input type="password" class="form-control" id="qa-password" name="password" placeholder="<?php
            echo trim(qa_lang_html('users/password_label'), ':');
?>" />
								
								<label class="checkbox inline">
									<input type="checkbox" name="remember" id="qa-rememberme" value="1"> <?php
            echo qa_lang_html('users/remember');
?>
								</label>
								<input type="hidden" name="code" value="<?php
            echo qa_html(qa_get_form_security_code('login'));
?>"/>
								<input type="submit" value="<?php
            echo $this->content['navigation']['user']['login']['label'];
?>" id="qa-login" name="dologin" class="btn btn-primary btn-large btn-block btn-success" />
						<a href="<?php echo qa_path_html('forgot'); ?>">I forgot my password ?</a>
							</form>
					</div>
				</div>
				
				<a class="login-register icon-user-add"  href="<?php echo qa_path_html('register'); ?>" title="<?php echo qa_lang_html('cleanstrap/register_on_site'); ?>"><span><?php echo qa_lang_html('cleanstrap/register'); ?></span></a>
				
			<?php
        }
        unset($this->content['navigation']['user']['login']);
        unset($this->content['navigation']['user']['register']);
        $this->output(ob_get_clean());
        
    }
	function qw_notification_btn(){
	
	}
    function search()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $search = $this->content['search'];
        
        $this->output('<form ' . $search['form_tags'] . ' class="form-search" role="search" >', @$search['form_extra']);
        
        $this->search_field($search);
        $this->search_button($search);
        
        $this->output('</form>');
    }
    function search_field($search)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<i class="icon-search"></i><input type="text" ' . $search['field_tags'] . ' value="' . @$search['value'] . '" class="form-control search-query" placeholder="' . qa_lang_html('cleanstrap/search') . '" autocomplete="off" />', '');
    }
    
    function search_button($search)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<button type="submit" value="' . $search['button_label'] . '" class="btn btn-default">Search</button>');
    }
    
    function sidepanel()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if ($this->qw_position_active('Right')) {
            $this->output('<div class="side-c col-md-4">');
            $this->output('<div class="qa-sidepanel">');
            $this->qw_position('Right');
            $this->output('</div>', '');
            
            $this->output('</div>');
        }
    }
    

    
    function user_sidebar()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        ob_start();
?>
				<ul class="user-sidebar">
					<li class="points"><?php
        echo qa_get_logged_in_points();
?></li>
					<li><a class="icon-user" href="<?php
        echo qa_path_html('user/' . qa_get_logged_in_handle());
?>"><?php
        qa_lang('Profile');
?></a></li>
					<?php
        foreach ($this->content['navigation']['user'] as $a) {
            if (isset($a['url'])) {
                $icon = (isset($a['icon']) ? ' class="' . $a['icon'] . '" ' : '');
                echo '<li' . (isset($a['selected']) ? ' class="active"' : '') . '><a' . $icon . ' href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
            }
        }
        if (!isset($this->content['navigation']['user']['logout']['url'])) {
            $link = qa_path_html('logout');
            echo "<li><a class='icon-power' href = '$link'> " . qa_lang_html('cleanstrap/logout') . " </a></li>";
        }
?>
				</ul>
			<?php
        $this->output(ob_get_clean());
    }

    
    function qw_full_categories_list($show_sub = false)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        
        $level      = 1;
        $navigation = @$this->content['navigation']['cat'];
        
        if (!isset($navigation)) {
            $categoryslugs = qa_request_parts(1);
            $cats          = qa_db_select_with_pending(qa_db_category_nav_selectspec($categoryslugs, false, false, true));
            $navigation    = qa_category_navigation($cats);
        }

		$this->output('<div class="dropdown-menu">');
		$this->output('<a class="all-cat" href="'.qa_path_html('categories').'">'.qa_lang('cleanstrap/all_categories').'</a>');
		$this->output('<div class="category-list">');
		$this->output('<div class="category-list-drop"><ul>');
		$index = 0;
		
		unset($navigation['all']);
		$row = ceil(count($navigation)/2);
		$col = 1;
		
		foreach ($navigation as $key => $navlink) {
			$this->set_context('nav_key', $key);
			$this->set_context('nav_index', $index++);
			$this->qw_full_categories_list_item($key, $navlink, '', $level, $show_sub);
			
			if($row == $col)
				$this->output('</ul></div><div class="category-list-drop"><ul>');
			$col++;
		}
		$this->clear_context('nav_key');
		$this->clear_context('nav_index');
		
		$this->output('</div>');
		$this->output('</div>');
		$this->output('</div>');

        unset($navigation);
    }
    
    function qw_full_categories_list_item($key, $navlink, $class, $level = null, $show_sub)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $suffix = strtr($key, array( // map special character in navigation key
            '$' => '',
            '/' => '-'
        ));
        $class .= "nav-cat";
        $this->output('<li class="qa-nav-cat-item">');
        $this->nav_link($navlink, $class);
        $this->output('</li>');
		
        if (count(@$navlink['subnav']) && $show_sub)
            $this->nav_list($navlink['subnav'], $class, 1 + $level);
        
        $this->output('</li>');
    }
    
    function main()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
       $content = $this->content;

		if($this->template == 'not-found'){
				$this->notfound_template($content);				
		}elseif(qw_is_user()){			
			$this->user_template($content);				
		}elseif($this->template == 'question'){
			$this->question_view($content);
		}elseif($this->template == 'user-wall'){
			$handle = qa_request_part(1);
			$this->output('<section id="content" class="content-sidebar user-cols">');
			$this->qw_user_nav($handle);
			$this->output('<div class="messages">');
			$this->message_list_and_form($this->content['message_list']);
			$this->output('</div></section>');
		}elseif($this->template == 'admin'){
			$this->admin_template($content);				
		}else{
			if(qw_hook_exist('main_'.$this->template))
				qw_do_action('main_'.$this->template, $this);
			else	
				$this->default_template($content);
					
		}
	}
	function main_top($content){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }

		if ($this->qw_position_active('Header') || $this->qw_position_active('Header Right')) {
			$this->output('<div class="header-position-c clearfix"><div class="container">');	
				if ($this->qw_position_active('Header')){
					$this->output('<div class="col-md-6">');
					$this->qw_position('Header');
					$this->output('</div>');
				}
				if ($this->qw_position_active('Header Right')){
					$this->output('<div class="col-md-6">');
					$this->qw_position('Header Right');
					$this->output('</div>');
				}
			$this->output('</div></div>');
		}
	}
	
	function default_template($content){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
			$this->main_parts($content);
			$this->qw_position('Content Bottom');
			$this->suggest_next();	
	}	
	
	function admin_template($content){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$this->main_parts($content);
	}
	
	function qw_page_title(){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		if(qw_is_user()){
			$handle = qa_request_part(1);
			if(empty($handle)) $handle = qa_get_logged_in_handle();
			$this->profile_user_card($handle);	
		}elseif (!qw_is_home() && (!empty($this->content['title']))) {
            $this->output('<div class="page-title"><div class="container">');
            $this->feed();
			$this->favorite();
			$this->output('<h1>'. $this->content['title'] .'</h1>');
			$this->qw_question_head();
            $this->output('</div></div>');
        }
	}
    function title() // add RSS feed icon after the page title
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        qa_html_theme_base::title();
        
        $feed = @$this->content['feed'];
        
        if (!empty($feed))
            $this->output('<a href="' . $feed['url'] . '" title="' . @$feed['label'] . '"><img src="' . $this->rooturl . 'images/rss.jpg" alt="" width="16" height="16" border="0" class="qa-rss-icon"/></a>');
    }
    
    function home($content)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
			
        $this->output('<div class="home-left-inner">'); 
			
			$this->main_parts($content);
		
        $this->output('</div>');
    }
    
	function user_template($content){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
			$userid = $this->content['raw']['userid'];
			$user_date =  get_userdata( $userid );
			$handle =  $user_date->user_login;
			$about  = qw_name($handle);
		}else{
			$handle = qa_request_part(1);
			if(empty($handle)) $handle = qa_get_logged_in_handle();
		}
		$this->output('<div class="user-right">');
	
		$this->qw_user_nav($handle);
		$this->profile_page($content, $handle);
		$this->output('</div>');		
			
	}
	
	function profile_user_card($handle){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }

		$user = $this->content['active_user'];
		$profile = $this->content['active_user_profile'];	

		$this->output('<div class="user-card"'. (!empty($profile['cover']) ? ' style="background-image:url('.qw_upload_url().'/'.$profile['cover'].')"' : '').'><div class="cover-overlay"><div class="container">');
			/* start user info */
			$this->output(
				'<div class="user-info">',
				'<div class="user-thumb">' . qw_get_post_avatar($user['account'], 150) . '</div>',
				'<div class="user-name">',
					'<span>'.$handle.'</span>',
					'<small class="block">' . qa_user_level_string($user['account']['level']) . '</small>',
					'<p class="user-rank">'. $handle .' ' . qa_lang_sub('cleanstrap/ranked_x_among_all_user', $user['rank'] ) . '</p>',
				'</div>'
			);
			if(qa_get_logged_in_handle() != $handle){
				$this->favorite();
				
				$this->output('<a class="btn icon-email" href="'.qa_path_html('message/'.$handle).'">'.qa_lang_html('cleanstrap/message').'</a>');
			}
			$this->output('</div>');
			/* end user info */
			
			$this->qw_user_activity_count($handle);			
		$this->output('</div></div></div>');

	}
	
	function qw_user_profile_button($handle){
		$user = $this->content['active_user'];
		$profile = $this->content['active_user_profile'];	
		
		$this->output('<div class="user-personal-links clearfix">');
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
			
			$form = @$this->content['form_profile'];
			
			if(isset($form) && !qw_is_state_edit() && $this->template != 'account'){
				$this->output('<form class="user-buttons" '.$form['tags'].'><div class="btn-group">');
				if(isset($form['buttons']) && !empty($form['buttons']) && is_array($form['buttons'])){
					foreach($form['buttons'] as $k => $btn)
					$this->output('<button class="btn '.$k .($k == 'delete' ? ' btn-danger' : '' ).'" ' . $btn['tags'] . ' type="submit">' . $btn['label'] . '</button>');
				}
				$this->output('</div>');
				$this->output('<input type="hidden" name="code" value="'.@$form['hidden']['code'].'" />');
				$this->output('</form>');
			}
        }
		if(qa_get_logged_in_handle() == $handle)
			$this->output(
				'<div class="btn-group pull-right">',				
					'<a class="btn btn-success" href="#" data-qawork="change-cover">Change cover</a>',
					'<a class="btn" href="'.qa_path_html('account').'">Account</a>',
					'<a class="btn" href="'.qa_path_html('notifications').'">'.qa_lang_html('cleanstrap/notifications').'</a>',
					qw_do_action('user_profile_btn', $handle, $user),
				'</div>'
			);
			
		$this->output('</div>');
	}
	
	function qw_user_activity_count($handle)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
        $user = $this->content['active_user'];

        $this->output(
			'<ul class="user-activity-count clearfix">', 
				'<li class="points-count counts">', $user['points']['points'], 
					'<span>' . qa_lang_html('cleanstrap/points') . '</span>', 
				'</li>',				
				'<li class="counts">', 
					'<i>'.qw_count_followers($handle).'</i>',
					'<span>' .qa_lang_html('cleanstrap/followers'). '</span>', 
				'</li>', 
				'<li class="counts">', 
					'<i>'.qw_count_following($handle).'</i>',
					'<span>' .qa_lang_html('cleanstrap/following'). '</span>', 
				'</li>', 
			'</ul>'
		);
    }

	
	function profile_page($content, $handle)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$user = $this->content['active_user'];
		$profile = $this->content['active_user_profile'];
		
		$this->output('<div class="user-main-content">');
		$this->qw_user_profile_button($handle);	
		
		if($this->template == 'user' && !qw_is_state_edit()){
			$this->output('<div class="user-widgets row">');
				$this->output('<div class="col-md-4">');
					$this->qw_position('Profile Left Top');
					$this->qw_user_about($user, $profile);
					$this->qw_user_activities($user, $profile);
					$this->qw_position('Profile Left Bottom');
				$this->output('</div>');
				$this->output('<div class="col-md-8">');
					if(!!qa_opt('qw_show_wall_on_profile'))
						$this->qw_user_wall_widget($content['message_list'], $handle);
					$this->qw_position('Profile Right');
				$this->output('</div>');				
			$this->output('</div>');
		}else{
			$this->main_parts($content);
		}
		$this->output('</div>');

    }
	function qw_user_about($user, $profile){
		
		if(isset($profile) && !empty($profile)){
			$profile = qw_order_profile_fields($profile);
			if (isset($profile['website']) && !empty($profile['website'])) {
				$new_url = $profile['website'] ;
				if(!filter_var($new_url, FILTER_VALIDATE_URL)){ 
				  $new_url = "http://".$new_url ;
				}
				$profile['website'] = '<a href="'.$new_url.'" target="_blank">'.$profile['website'].'</a>' ;
			}
			$html = '';
			foreach ($profile as $k => $p)
				if(!empty($p))
					$html .= '<li class="'.$k.'"><strong>'.$k.'</strong> '.$p.'</li>';
			if(!empty($html))	
			$this->output(
				'<div class="user-widget user-profile">',
					'<h3 class="icon-user">'.qa_lang_html('cleanstrap/profile').'</h3>',
					'<div class="widget-inner">',
						'<ul>',
							$html,
						'</ul>',
					'</div>',
				'</div>'
			);
		}

	}
	function qw_user_activities($user, $profile){
		if(isset($user['points']) && !empty($user['points'])){
			$p = $user['points'];
			$this->output(
				'<div class="user-widget user-activities">',
					'<h3 class="icon-chart-bar">'.qa_lang_html('cleanstrap/activities').'</h3>',
					'<div class="widget-inner">',
						'<ul>',
							'<li class="points"><strong>'.qa_lang_html('cleanstrap/score').'</strong> '.$p['points'].' '.qa_lang_sub('cleanstrap/ranked_x_among_all_user', $user['rank']).'</li>',
							'<li class="qcount"><strong>'.qa_lang_html('cleanstrap/questions').'</strong> '.$p['qposts'].' '.qa_lang_sub('cleanstrap/x_wth_best_answer_chosen', $p['aselects']).'</li>',
							'<li class="acount"><strong>'.qa_lang_html('cleanstrap/answers').'</strong> '.$p['aposts'].' '.qa_lang_sub('cleanstrap/x_chosen_as_best', $p['aselects']).'</li>',
							'<li class="acount"><strong>'.qa_lang_html('cleanstrap/voted_on').'</strong> '.qa_lang_sub('cleanstrap/x_questions', $p['qvoteds']).', '.qa_lang_sub('cleanstrap/x_answers', $p['avoteds']).'</li>',
							'<li class="acount"><strong>'.qa_lang_html('cleanstrap/gave_out').'</strong> '.qa_lang_sub('cleanstrap/x_up_votes', $p['qupvotes'] + $p['aupvotes']).', '.qa_lang_sub('cleanstrap/x_down_votes', $p['qdownvotes'] + $p['adownvotes']).'</li>',
							'<li class="acount"><strong>'.qa_lang_html('cleanstrap/received').'</strong> '.qa_lang_sub('cleanstrap/x_up_votes', $p['upvoteds']).', '.qa_lang_sub('cleanstrap/x_down_votes', $p['downvoteds']).'</li>',
						'</ul>',
					'</div>',
				'</div>'
			);
		}
	}
	
	function qw_user_wall_widget($message_list, $handle){
		/* unset title */
		unset($message_list['title']);
		
		if(isset($message_list) and !empty($message_list)){
			$this->output('<div class="user-wall">');
				$this->message_list_and_form($message_list, $handle);
			$this->output('</div>');
		}
	}
	
    function qw_user_nav($handle)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		if(isset($this->content['navigation']['sub'])){
			$sub = $this->content['navigation']['sub'];
			unset($sub['account']);
			unset($sub['favorites']);
			
			$sub['profile']['icon'] 		= 'icon-user';		
			$sub['favorites']['icon'] 		= 'icon-heart';
			$sub['wall']['icon'] 			= 'icon-pin';
			$sub['activity']['icon'] 		= 'icon-chart-bar';
			$sub['answers']['icon'] 		= 'icon-answer';
			$sub['questions']['icon'] 		= 'icon-question';
			
			$this->content['navigation']['sub'] = $sub;
		}
		
        $this->output('	<div class="user-navigation">'); 
		
        $this->nav('sub');		
        $this->output('</div>');
    }
	
	function qw_question_head(){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		$content = $this->content;
		$q_view = @$content['q_view'];

		if(isset($q_view)){

			$this->output('<div class="question-metas">
				'.(is_featured($q_view['raw']['postid']) ? '<span class="featured-sticker icon-star ra-tip" title="'.qa_lang_html('cleanstrap/question_is_featured').'">' . qa_lang_html('cleanstrap/featured') . '</span>' : '').'
				'.qw_post_status($q_view).'				
				<span class="meta-icon icon-answer"></span>
				<span class="q-view-a-count">'.qa_lang_sub('cleanstrap/x_answers', $q_view['raw']['acount']).' </span>
				<span class="icon-eye meta-icon"></span>
				<span class="q-view-a-count">' . qa_lang_sub('cleanstrap/x_views', $q_view['raw']['views']) . ' </span>
				<span class="icon-folder meta-icon"></span>
				'.qa_lang_html('cleanstrap/posted_under').' <a class="cat-in" href="' . qw_cat_path($q_view['raw']['categorybackpath']) . '">' . $q_view['raw']['categoryname'] . '</a>
				</div>');
				
				if (!empty($q_view['q_tags'])) {
					$this->output('<div class="question-tags icon-tags">');
					$this->post_tag_list($q_view, 'tags');			
					$this->output('</div>');
				}
		}
	}
	
    function question_view($content)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
			$q_view = @$content['q_view'];
			$this->output('<div class="lr-table row"><div class="left-content question-main col-md-8">');
					$this->main_parts($content);
					$this->qw_position('Content Bottom');					
				$this->output('</div>');
			
				$this->output('<div class="question-side col-md-4">');
					$this->output('<div class="question-share-invite"><i class="icon-group">Ask your friends for help</i>');
					$this->q_social_share();
					$link = qa_q_path(@$q_view['raw']['postid'], @$q_view['raw']['title'] , true);
					$this->fb_ask_your_friend($link);
					$this->output('</div>');
					$this->qw_position('Question Right');			
				$this->output('</div>');
			$this->output('</div>');
		
    }
	
    function q_list_item($q_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $status = qw_get_post_status($q_item);
        if (qa_opt('styling_' . $status . '_question'))
            $status_class = ' qa-q-status-' . $status;
        else
            $status_class = '';
        $this->output('<div class="qa-q-list-item' . rtrim(' ' . @$q_item['classes']) . $status_class . (is_featured($q_item['raw']['postid']) ? ' featured' : '') . ' clearfix" ' . @$q_item['tags'] . '>');
        
        
        $this->q_item_main($q_item);
        
        $this->output('</div> <!-- END question list item -->', '');
    }
    
    
    function q_item_stats($q_item) // add view count to question list
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="qa-q-item-stats">');
        $this->a_count($q_item);
        qa_html_theme_base::view_count($q_item);
        $this->output('</div>');
    }
    
    function q_item_main($q_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $avatar_size =  35;
        $timeCode    = $q_item['when'];
        $when        = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
        
       // if (isset($q_item['avatar'])) {
            $this->output('<div class="asker-avatar">');
            $this->output(qw_get_post_avatar($q_item, $avatar_size, true));		
            $this->output('</div>');
       // }
        $this->output(
			'<div class="qa-q-item-main">',
			'<div class="ans-count ra-tip" title="'.qa_lang_sub('cleanstrap/x_answers', $q_item['raw']['acount']).'">'. qw_format_num($q_item['raw']['acount']) .'</div>',
			'<div class="qa-q-item-main-line">'
		);
		
        $this->output('<div class="q-item-head">');
		
		$this->output('<div class="q-item-right pull-right">');
			
			if ((qa_opt('qw_show_tags_list') && !empty($q_item['q_tags'])) || isset($q_item['raw']['categoryname']) ) {
				$this->output('<div class="cat-tag-drop">');
				
				
				$this->output('</div>');
			}
			
		$this->output('</div>');
		
        $this->q_item_title($q_item);
		$this->post_meta($q_item, 'qa-q-item');
		
        $this->output('</div>');
		$this->q_item_content($q_item);
		$this->q_item_main_stats($q_item);
        
        $this->q_item_buttons($q_item);
        
        $this->output('</div></div>');
    }
    
    function attribution()
    {
    }
	
	function what_1($post, $class ='post-meta'){
		$order=explode('^', @$post['meta_order']);
		$this->output('<div class="what-1">');	
		foreach ($order as $element)
			switch ($element) {
				case 'what':
					$this->post_meta_what($post, $class);
					break;
					
				case 'when':
					$this->post_meta_when($post, $class);
					break;
					
				case 'where':
					$this->post_meta_where($post, $class);
					break;
					
				case 'who':
					$this->post_meta_who($post, $class);
					break;
			}
			
		$this->post_meta_flags($post, $class);
		$this->output('</div>');
	}
	
	function what_2($post){
		
		if (!empty($post['what_2'])) {
			$order=explode('^', @$post['meta_order']);
			$this->output('<div class="what-2 '.qw_what_icon($post['what_2']).'">');
			foreach ($order as $element)
				switch ($element) {
					case 'what':
						$this->output('<span class="post-what">'.$post['what_2'].'</span>');
						break;
					
					case 'when':
						$this->output_split(@$post['when_2'], 'post-when');
						break;
					
					case 'who':
						$this->output_split(@$post['who_2'], 'post-who');
						break;
				}
			$this->output('</div>');
		}
	}
    
	function q_item_main_stats($q_item){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		if (isset($q_item['raw']['categoryname']) && !empty($q_item['raw']['categoryname'])) {
			$cat = '<a class="cat-in icon-folder" href="' . qw_cat_path($q_item['raw']['categorybackpath']) . '">' . $q_item['raw']['categoryname'] . '</a>	';
		}

				
		$this->output(			
			'<div class="q-item-footer clearfix">',				
				'<div class="q-item-status">',
					'<span class="status-c">' . qw_post_status($q_item) . '</span>',
					'<span class="icon-thumb-up">'.qa_lang_sub('cleanstrap/x_votes', $q_item['raw']['netvotes']).'</span>',
					'<span class="icon-eye">'.qa_lang_sub('cleanstrap/x_views', qw_format_num($q_item['raw']['views'])).'</span>',
					@$cat);
					if (!empty($q_item['q_tags'])){
						$this->output('<span class="icon-tags"></span>');
						$this->post_tag_list($q_item, 'q-item');
					}
				$this->output('</div>',
			'</div>'
		);
	}
	
    function footer()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		if($this->qw_position_active('Site Counts')){
			$this->output('<div class="site-counts-positions">');
				$this->output('<div class="container">');
					$this->qw_position('Site Counts');
				$this->output('</div>');
			$this->output('</div>');
		}
		if($this->qw_position_active('Footer 1') || $this->qw_position_active('Footer 1 Right')){
			$this->output('<div class="footer-positions">');
				$this->output('<div class="container">');
					$this->output('<div class="row">');
						$this->output('<div class="col-md-8">');					
							$this->qw_position('Footer 1');
						$this->output('</div>');
						$this->output('<div class="col-md-4">');					
							$this->qw_position('Footer 1 Right');
						$this->output('</div>');
					$this->output('</div>');
				$this->output('</div>');
			$this->output('</div>');
		}
			
        $this->output('<footer id="site-footer">');
        $this->output('<div class="container">');
			$this->nav('footer');
        
			$this->output('<div class="qa-attribution-right">');
				if ((bool) qa_opt('qw_footer_copyright'))
					$this->output(qa_opt('qw_footer_copyright'));
				$this->output('<span class="developer">Crafted by <a href="http://rahularyan.com">RA Team</a></span>');
			$this->output('</div>');
		$this->output('</div>');        
        $this->output('</footer>');
		
		$this->output(qw_do_action('footer_bottom', $this));
    }
    
    function get_social_links()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
        if (!! qa_opt('qw_social_enable')) {
            $links = json_decode(qa_opt('qw_social_list'));
            
			if(!empty($links)){
				$this->output('<ul class="ra-social-links">');
				foreach ($links as $link) {
					$icon  = ($link->social_icon != '1' ? ' ' . $link->social_icon . '' : '');
					$image = ($link->social_icon == '1' ? '<img src="' . $link->social_icon_file . '" />' : '');
					$this->output('<li><a class="' . @$icon . '" href="' . $link->social_link . '" title="Link to ' . $link->social_title . '" >' . @$image . '</a></li>');
				}
				$this->output('</ul>');
			}
        }
    }
    
    function nav_item($key, $navlink, $class, $level = null)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $suffix = strtr($key, array( // map special character in navigation key
            '$' => '',
            '/' => '-'
        ));
        
        $this->output('<li class="qa-' . $class . '-item' . (@$navlink['opposite'] ? '-opp' : '') . (@$navlink['state'] ? (' qa-' . $class . '-' . $navlink['state']) : '') . ' qa-' . $class . '-' . $suffix . '">');
        $this->nav_link($navlink, $class);
        
        if (count(@$navlink['subnav']))
            $this->nav_list($navlink['subnav'], $class, 1 + $level);
        
        $this->output('</li>');
    }
    function nav_link($navlink, $class)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$icon = isset($navlink['icon']) ? $navlink['icon'] : ($this->template == 'admin' ? 'icon-cog' : '');
        if (isset($navlink['url']))
            $this->output('<a href="' . $navlink['url'] . '" class="' . $icon . ' qa-' . $class . '-link' . (@$navlink['selected'] ? (' qa-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' qa-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . (isset($navlink['target']) ? (' target="' . $navlink['target'] . '"') : '') . '>' . $navlink['label'] . (strlen(@$navlink['note']) ? '<span class="qa-' . $class . '-note">' . filter_var($navlink['note'], FILTER_SANITIZE_NUMBER_INT) . '</span>' : '') . '</a>');
        
        else
            $this->output('<span class="qa-' . $class . '-nolink' . (@$navlink['selected'] ? (' qa-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' qa-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . '>' . @$navlink['label'] . (strlen(@$navlink['note']) ? '<span class="qa-' . $class . '-note">' . filter_var($navlink['note'], FILTER_SANITIZE_NUMBER_INT) . '</span>' : '') . '</span>');
    }
    function page_title_error()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $favorite = @$this->content['favorite'];
        
        if (isset($favorite))
            $this->output('<form ' . $favorite['form_tags'] . '>');
        
        $this->output('<h1 class="main-title">');
        $this->favorite();
        $this->title();
        $this->output('</h1>');
        
        if (isset($this->content['error']))
            $this->error(@$this->content['error']);
        
        if (isset($favorite)) {
            $this->form_hidden_elements(@$favorite['form_hidden']);
            $this->output('</form>');
        }
    }
    
    function post_avatar_meta($post, $class, $avatarprefix = null, $metaprefix = null, $metaseparator = '<br/>')
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="' . $class . '-avatar-meta">');
        //$this->post_avatar($post, $class, $avatarprefix);
        $this->post_meta($post, $class, $metaprefix, $metaseparator);
        $this->output('</div>');
    }
    
    function feed()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $feed = @$this->content['feed'];
        
        if (!empty($feed)) {
            $this->output('<a href="' . $feed['url'] . '" class="feed-link icon-rss" title="' . @$feed['label'] . '"></a>');
        }
    }
    
    function page_links()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $page_links = @$this->content['page_links'];
        
        if (!empty($page_links)) {
            $this->page_links_list(@$page_links['items']);
        }
    }
    function page_links_list($page_items)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (!empty($page_items)) {
            $this->output('<ul class="pagination clearfix">');
            
            $index = 0;
            
            foreach ($page_items as $page_link) {
                $this->set_context('page_index', $index++);
                $this->page_links_item($page_link);
                
                if ($page_link['ellipsis'])
                    $this->page_links_item(array(
                        'type' => 'ellipsis'
                    ));
            }
            
            $this->clear_context('page_index');
            
            $this->output('</ul>');
        }
    }
    
    function voting($post)
    {
        if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $code = @$post['voting_form_hidden']['code'];
        if (isset($post['vote_view'])) {
            $state = @$post['vote_state'];
            $this->output('<div class="' . $state . ' ' . (isset($this->content['q_list']) ? 'list-' : '') . 'voting clearfix ' . (qa_opt('qw_horizontal_voting_btns') ? 'voting-horizontal ' : 'voting-vertical ') . (($post['vote_view'] == 'updown') ? 'qa-voting-updown' : 'qa-voting-net') . (($post['raw']['netvotes'] < (0)) ? ' negative' : '') . (($post['raw']['netvotes'] > (0)) ? ' positive' : '') . '" ' . @$post['vote_tags'] . '>');
            $this->voting_inner_html($post);
            $this->output('</div>');
        }
    }
    
    
    function voting_inner_html($post)
    {
        if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $up_tags   = preg_replace('/onclick="([^"]+)"/', '', str_replace('name', 'data-id', @$post['vote_up_tags']));
        $down_tags = preg_replace('/onclick="([^"]+)"/', '', str_replace('name', 'data-id', @$post['vote_down_tags']));
        if (qa_is_logged_in()) {
            $user_point = qa_get_logged_in_points();
            if ($post['raw']['type'] == 'Q') {
                if ((qa_opt('permit_vote_q') == '106')) {
                    $need    = (qa_opt('permit_vote_q_points') - $user_point);
                    $up_tags = str_replace(qa_lang_html('main/vote_disabled_level'), 'You need ' . $need . ' more points to vote', $up_tags);
                }
                
                if ((qa_opt('permit_vote_q') == '106') && (qa_opt('permit_vote_down') == '106')) {
                    $max       = max(qa_opt('permit_vote_down_points'), qa_opt('permit_vote_q_points'));
                    $need      = ($max - $user_point);
                    $down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
                    
                } elseif (qa_opt('permit_vote_q') == '106') {
                    $need      = (qa_opt('permit_vote_q_points') - $user_point);
                    $down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
                } elseif (qa_opt('permit_vote_down') == '106') {
                    $need      = (qa_opt('permit_vote_down_points') - $user_point);
                    $down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
                }
            }
            if ($post['raw']['type'] == 'A') {
                if ((qa_opt('permit_vote_a') == '106')) {
                    $need    = (qa_opt('permit_vote_a_points') - $user_point);
                    $up_tags = str_replace(qa_lang_html('main/vote_disabled_level'), 'You need ' . $need . ' more points to vote', $up_tags);
                }
                if ((qa_opt('permit_vote_a') == '106') && (qa_opt('permit_vote_down') == '106')) {
                    $max       = max(qa_opt('permit_vote_down_points'), qa_opt('permit_vote_a_points'));
                    $need      = ($max - $user_point);
                    $down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
                    
                } elseif (qa_opt('permit_vote_a') == '106') {
                    $need      = (qa_opt('permit_vote_a_points') - $user_point);
                    $down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
                } elseif (qa_opt('permit_vote_down') == '106') {
                    $need      = (qa_opt('permit_vote_down_points') - $user_point);
                    $down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
                }
            }
        }
        
        $state     = @$post['vote_state'];
        $code      = qa_get_form_security_code('vote');
        $vote_text = ($post['raw']['netvotes'] > 1 || $post['raw']['netvotes'] < (-1)) ? qa_lang('cleanstrap/votes') : qa_lang('cleanstrap/vote');
        
        if (isset($post['vote_up_tags']))
            $this->output('<a ' . @$up_tags . ' href="#" data-code="' . $code . '" class=" icon-thumb-up enabled vote-up ' . $state . '"></a>');
        $this->output('<span class="count">' . $post['raw']['netvotes'] . '</span>');
        if (isset($post['vote_down_tags']))
            $this->output('<a ' . @$down_tags . ' href="#" data-code="' . $code . '" class=" icon-thumb-down enabled vote-down ' . $state . '"></a>');
        
    }
    
    
    function vote_count($post)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        // You can also use $post['upvotes_raw'], $post['downvotes_raw'], $post['netvotes_raw'] to get
        // raw integer vote counts, for graphing or showing in other non-textual ways
        
        $this->output('<div class="qa-vote-count ' . (($post['vote_view'] == 'updown') ? 'qa-vote-count-updown' : 'qa-vote-count-net') . '"' . @$post['vote_count_tags'] . '>');
        
        if ($post['vote_view'] == 'updown') {
            $this->output_split($post['upvotes_view'], 'qa-upvote-count');
            $this->output_split($post['downvotes_view'], 'qa-downvote-count');
            
        } else
            $this->output($post['raw']['netvotes']);
        
        $this->output('</div>');
    }
    function vote_hover_button($post, $element, $value, $class)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (isset($post[$element]))
            $this->output('<button ' . $post[$element] . ' type="submit" class="' . $class . '-button btn">' . $value . '</button>');
    }
    function vote_disabled_button($post, $element, $value, $class)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (isset($post[$element]))
            $this->output('<button ' . $post[$element] . ' type="submit" class="btn ' . $class . '-disabled" disabled="disabled">' . $value . '</button>');
    }
    function q_view($q_view)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (!empty($q_view)) {
            $this->output('<div class="qa-q-view' . (@$q_view['hidden'] ? ' qa-q-view-hidden' : '') . rtrim(' ' . @$q_view['classes']) . '"' . rtrim(' ' . @$q_view['tags']) . '>');
            
            $this->q_view_main($q_view);
            
            $this->output('</div>', '');
        }
    }
    function q_view_main($q_view)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
		$this->output(base64_decode(qa_opt('qw_ads_below_question_title')));
		
        $this->output('<div class="q-view-body">');
		
		$this->output('<div class="big-s-avatar avatar">');
			$this->output(qw_get_post_avatar($q_view, 40, true));
			$this->voting($q_view);
		$this->output('</div>');
		
        $this->output('<div class="no-overflow">');            
            
            $this->output('<div class="qa-q-view-main">');
            
            if (isset($q_view['main_form_tags']))
                $this->output('<form ' . $q_view['main_form_tags'] . '>'); // form for buttons on question	
			
            $this->output('<div class="q-cont-right">');
            
            $this->output('<div class="qa-q-view-wrap">');
            $this->output('<div class="qa-q-view-inner"><div class="qa-q-view-inner-line">');
            $this->output('<div class="clearfix">');
			
			if(isset($q_view['raw']['handle']) ){
				$this->output('<div class="user-info no-overflow">');
					$this->what_1($q_view);
				$this->output('</div>');
			}
			
			if(isset($this->content['form_q_edit']))
				$this->form($this->content['form_q_edit']);
				
			$this->q_view_content($q_view);
			if(isset($q_view['raw']['postid'])){
				$this->output(qw_do_action('after_question', $q_view['raw']['postid']));
			}
			
			/// for showing custom fields
			if(qw_hook_exist('show_custom_question_fields') && !isset($this->content['form_q_edit'])){
				$fields = qw_get_all_post_metas($q_view['raw']['postid']);
				$this->output(
					'<div class="custom-fields">', 
					qw_do_action('show_custom_question_fields',$fields, $q_view['raw']),
					'</div>'
				);
			}
			
            $this->output('</div>');
						
			$this->q_view_extra($q_view);
			$this->ra_post_buttons($q_view, true);
			
            $this->output('</div></div>');
            $this->output('<div class="post-footer">');
			$this->what_2($q_view);
            $this->c_list(@$q_view['c_list'], 'qa-q-view');
            $this->output('</div></div>');
            if (isset($q_view['main_form_tags'])) {
                $this->form_hidden_elements(@$q_view['buttons_form_hidden']);
                $this->output('</form>');
            }
            $this->output('</div>');
			
            $this->c_form(@$q_view['c_form']);
            
            $this->output('</div>');			
            $this->output('</div>');
            $this->output('</div>');
			
			$this->q_view_follows($q_view);
            $this->q_view_closed($q_view);
			
			$this->output(base64_decode(qa_opt('qw_ads_after_question_content')));
   
    }
	function q_view_follows($q_view)
	{
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if (!empty($q_view['follows']))
			$this->output(
				'<div class="qa-q-view-follows">',
				'<span class="icon-flow-children"></span>',
				'<strong>',
				$q_view['follows']['label'],
				'<a href="'.$q_view['follows']['url'].'" class="qa-q-view-follows-link">'.$q_view['follows']['title'].'</a></strong>',
				'</div>'
			);
	}
		
	function q_view_closed($q_view)
	{
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if (!empty($q_view['closed'])) {
			$haslink=isset($q_view['closed']['url']);
			
			$this->output(
				'<div class="qa-q-view-closed question-status">',
					'<div class="question-status-icon icon-times"></div>',
					'<div class="question-status-message">',
						'<div class="question-status-message-inner">',
						'<h3 class="status-heading">'.qa_lang_html('cleanstrap/this_question_is_closed').'</h3>',
						'<p>',
						$q_view['closed']['label'],
						($haslink ? ('<a href="'.$q_view['closed']['url'].'" class="qa-q-view-closed-content">') : ''),
						$q_view['closed']['content'],
						$haslink ? '</a>' : '',
						'</p>',
						'</div>',
					'</div>',
				'</div>'
			);
		}
	}
    function ra_post_buttons($q_view, $show_feat_img=false)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if(isset($q_view['form']['buttons'])){
			$buttons = $q_view['form']['buttons'];
			
			if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) && (!empty($q_view)) && $show_feat_img)
				$buttons['featured'] = array(
					'tags' => 'id="set_featured"',
					'label' => !is_featured($q_view['raw']['postid']) ? qa_lang_html('cleanstrap/featured') : qa_lang_html('cleanstrap/unfeatured'),
					'popup' => !is_featured($q_view['raw']['postid']) ? qa_lang_html('cleanstrap/set_featured') : qa_lang_html('cleanstrap/remove_featured'),
					'class' => 'icon-star'
				);
        }
        $ans_button = @$buttons['answer']['tags'];
        if (isset($ans_button)) {
            $onclick                   = preg_replace('/onclick="([^"]+)"/', '', $ans_button);
            $buttons['answer']['tags'] = $onclick;
        }
        if(isset($buttons)){
			$this->output('<div class="post-button clearfix">');
			foreach ($buttons as $k => $btn) {
				if ($k == 'edit')
					$btn['class'] = 'icon-edit';
				if ($k == 'flag')
					$btn['class'] = 'icon-flag';
				if ($k == 'unflag')
					$btn['class'] = 'icon-flag';
				if ($k == 'close')
					$btn['class'] = 'icon-cancel';
				if ($k == 'hide')
					$btn['class'] = 'icon-hide';
				if ($k == 'answer')
					$btn['class'] = 'icon-answer';
				if ($k == 'comment')
					$btn['class'] = 'icon-comment';
				if ($k == 'follow')
					$btn['class'] = 'icon-answer';
				
				$this->output('<button ' . @$btn['tags'] . ' class="ra-tip btn ' . @$btn['class'] . '" title="' . @$btn['popup'] . '" type="submit"><span>' . @$btn['label'] . '</span></button>');
			}

			//register a hook
			$this->output(qw_do_action('ra_post_buttons_hook', $this, $q_view));
			
			$this->output('</div>');
		}
    }
    function post_tags($post, $class)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (!empty($post['q_tags'])) {
            $this->output('<div class="' . $class . '-tags clearfix">');
            $this->post_tag_list($post, $class);
            $this->output('</div>');
        }
    }

     function c_list_item($c_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $extraclass = @$c_item['classes'] . (@$c_item['hidden'] ? ' qa-c-item-hidden' : '');
        
        $this->output('<div class="clearfix qa-c-list-item ' . $extraclass . '" ' . @$c_item['tags'] . '>');
        $this->output('<span class="toggle-comment">..</span>');
        $this->output('<div class="asker-avatar">');
        
        if (isset($c_item['raw']['handle']))
            $this->output(qw_get_post_avatar($c_item, 20, true));
        
        $this->output('</div>');
        $this->output('<div class="qa-c-wrap"><div class="qa-c-wrap-inner-height">');
		$this->c_item_main($c_item);
		$this->post_meta($c_item, 'qa-c-item');
        $this->ra_comment_buttons($c_item);
        $this->output('</div></div>');
        $this->output('</div> <!-- END qa-c-item -->');
    }
    
    function c_item_main($c_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->error(@$c_item['error']);
        
        if (isset($c_item['expand_tags']))
            $this->c_item_expand($c_item);
        elseif (isset($c_item['url']))
            $this->c_item_link($c_item);
        else
            $this->c_item_content($c_item);
    }
    function ra_comment_buttons($c_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if(isset($c_item['form']['buttons'])){
			$buttons = $c_item['form']['buttons'];
			
			$this->output('<div class="post-button">');
			foreach ($buttons as $k => $btn) {
				if ($k == 'edit')
					$btn['class'] = 'icon-edit';
				if ($k == 'flag')
					$btn['class'] = 'icon-flag';
				if ($k == 'unflag')
					$btn['class'] = 'icon-flag';
				if ($k == 'hide')
					$btn['class'] = 'icon-hide';
				if ($k == 'reshow')
					$btn['class'] = 'icon-eye';
				if ($k == 'comment')
					$btn['class'] = 'icon-answer';
				
				$this->output('<button ' . $btn['tags'] . ' class="btn ' . @$btn['class'] . '" title="' . @$btn['popup'] . '" type="submit">' . @$btn['label'] . '</button>');
			}
			$this->output('</div>');
		}
    }
    function a_list($a_list)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if (isset($a_list['title']) && (strlen(@$a_list['title']) || strlen(@$a_list['title_tags'])))
                $this->output('<h3 class="answers-label icon-answer">' . @$a_list['title'] . '</h3>');
		
		if (!empty($a_list)) {
            
            $this->output('<div class="qa-a-list' . ($this->list_vote_disabled($a_list['as']) ? ' qa-a-list-vote-disabled' : '') . '" ' . @$a_list['tags'] . '>', '');
           
            $this->a_list_items($a_list['as']);
            $this->output('</div> <!-- END qa-a-list -->', '');
        }
        $this->page_links();
        $this->answer_form();	
    }
    function a_list_item($a_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $extraclass = @$a_item['classes'] . ($a_item['hidden'] ? ' qa-a-list-item-hidden' : ($a_item['selected'] ? ' qa-a-list-item-selected' : ''));
        
        $this->output('<div class="qa-a-list-item ' . $extraclass . '" ' . @$a_item['tags'] . '>');
        
        $this->a_item_main($a_item);
        
        $this->output('</div> <!-- END qa-a-list-item -->', '');
    }
    function a_item_main($a_item)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }

        $avatar_size =  40;
		$this->output('<div class="big-s-avatar avatar">');
		$this->output(qw_get_post_avatar($a_item, $avatar_size, true));
		$this->voting($a_item);
		$this->output('</div>');			
        
		$this->output('<div class="q-cont-right">');
        $this->output('<div class="qa-a-item-main">');
		
		$timeCode    = $a_item['when'];
        		
        $this->output('<div class="a-item-inner-wrap">');
        
        if (isset($a_item['main_form_tags']))
            $this->output('<form ' . $a_item['main_form_tags'] . '>'); // form for buttons on answer
        
        /* if ($a_item['hidden'])
        $this->output('<div class="qa-a-item-hidden">');
        elseif ($a_item['selected'])
        $this->output('<div class="qa-a-item-selected">');	 */
        
        
        $this->output('<div class="a-item-wrap"><div class="a-item-inner-line">');
        $this->output('<div class="user-info no-overflow">');
			$this->a_selection($a_item);
			$this->what_1($a_item);
			$this->output('</div>');
        $this->error(@$a_item['error']);
        $this->a_item_content($a_item);
        
        //$this->post_meta($a_item, 'qa-a-item');
        //if ($a_item['hidden'] || $a_item['selected'])
        
		if(isset($a_item['raw']['postid'])){
			$this->output(qw_do_action('after_answer', $a_item['raw']['postid']));
		}
        
        $this->ra_post_buttons($a_item);
        $this->output('</div></div>');
		
		$this->output('<div class="post-footer">');
			$this->what_2($a_item);
			$this->c_list(@$a_item['c_list'], 'qa-a-item');
		$this->output('</div>');
        if (isset($a_item['main_form_tags'])) {
            $this->form_hidden_elements(@$a_item['buttons_form_hidden']);
            $this->output('</form>');
        }
        
        $this->c_form(@$a_item['c_form']);
        $this->output('</div>');
		$this->output('</div>');
        $this->output('</div> <!-- END qa-a-item-main -->');
    }
	
    function main_part($key, $part)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
        $partdiv = ((strpos($key, 'custom') === 0) || (strpos($key, 'form') === 0) || (strpos($key, 'q_list') === 0) || (strpos($key, 'q_view') === 0) || (strpos($key, 'a_form') === 0) || (strpos($key, 'a_list') === 0) || (strpos($key, 'ranking') === 0) || (strpos($key, 'message_list') === 0) || (strpos($key, 'nav_list') === 0));
        
        
        if ($partdiv)
            $this->output('<div class="qa-part-' . strtr($key, '_', '-') . '">'); // to help target CSS to page parts
		
        if (strpos($key, 'custom') === 0)
            $this->output_raw($part);
        
        elseif (strpos($key, 'form_q_edit') === 0)
            $this->output();
		
		elseif (strpos($key, 'form') === 0)
            $this->form($part);
        elseif (strpos($key, 'q_list') === 0)
            $this->q_list_and_form($part);
        elseif (strpos($key, 'q_view') === 0)
            $this->q_view($part); /* elseif (strpos($key, 'a_form')===0)
        $this->a_form($part); */ 
        elseif (strpos($key, 'a_list') === 0)
            $this->a_list($part);
        elseif (strpos($key, 'ranking') === 0)
            $this->ranking($part);
        elseif (strpos($key, 'message_list') === 0)
            $this->message_list_and_form($part);
        elseif (strpos($key, 'nav_list') === 0) {
            $this->part_title($part);
            $this->nav_list($part['nav'], $part['type'], 1);
        }
        
        if ($partdiv)
            $this->output('</div>');
    }
 
    
    function answer_form()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (isset($this->content['a_form'])) {
            $this->output('<div class="answer-form">');
            $this->a_form($this->content['a_form']);
            $this->output('</div>');
        }

    }
    
    function a_form($a_form)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="qa-a-form"' . (isset($a_form['id']) ? (' id="' . $a_form['id'] . '"') : '') . '>');
        
        if (isset($a_form)) {
			
			$this->output('<div class="big-s-avatar avatar">' . qw_get_avatar(qa_get_logged_in_handle(), 40) . '</div>');
    
				$this->output('<div class="q-cont-right">');
         
				$this->output('<div class="answer-f-wrap">');
					///$this->output('<h3 class="answers-label">'.$a_form['title'].'</h3>');
					//$a_form['title'] = '';
					$this->form($a_form);
					$this->c_list(@$a_form['c_list'], 'qa-a-item');
					$this->output('</div>');
            $this->output('</div>');
        } else {
            $this->output('<div class="login-to-answer">' . $a_form['title'] . '</div>');
        }
        $this->output('</div> <!-- END qa-a-form -->', '');
    }
    
    function favorite($post=false)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if($post)
			$favorite = @$post['favorite'];			
		else
			$favorite = @$this->content['favorite'];

        if (isset($favorite)) {
            $this->output('<div class="fav-parent">');
            $this->favorite_inner_html($favorite);
            $this->output('</div>');
        }
    }
    function favorite_inner_html($favorite)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$type 		= qa_post_text('entitytype');
		
		$icon_add	= 'icon-heart';
		$icon_remove	= 'icon-heart';
		$title = '';
		if($type == 'U' || $this->template == 'user'){
			$icon_add	= 'icon-user-add';
			$icon_remove	= 'icon-user-delete';

			$title = isset($favorite['favorite_add_tags']) ? qa_lang('cleanstrap/follow') : qa_lang('cleanstrap/unfollow');
		}
		
        $this->favorite_button(@$favorite['favorite_add_tags'], $icon_add.',' . @$favorite['form_hidden']['code'] . ',', $title);
        $this->favorite_button(@$favorite['favorite_remove_tags'], $icon_remove.' active remove,' . @$favorite['form_hidden']['code'] . ',', $title);
    }
    function favorite_button($tags, $class, $title = '')
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }

        if (isset($tags)) {            

            $code_icon = explode(',', $class);

			
            $data      = str_replace('name', 'data-id', @$tags);
            $data      = str_replace('onclick="return qa_favorite_click(this);"', '', @$data);
            
            $this->output('<a href="#" ' . @$favorite['favorite_tags'] . ' ' . $data . ' data-code="' . $code_icon[1] . '" class="btn fav-btn ' . $code_icon[0] . '">'.$title.'</a>');
        }
    }
    
    function c_form($c_form)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="qa-c-form"' . (isset($c_form['id']) ? (' id="' . $c_form['id'] . '"') : '') . (@$c_form['collapse'] ? ' style="display:none;"' : '') . '>');
        
        $this->output('<div class="asker-avatar no-radius">');
        $this->output(qw_get_avatar(qa_get_logged_in_handle(), 30));
        $this->output('</div>');
        if (!empty($c_form['title'])) {
            $this->output('<div class="comment-f-wrap">');
            $this->output('<h3>', $c_form['title'], '</h3>');
            $c_form['title'] = '';
            $this->form($c_form);
            $this->output('</div>', '');
        } else
            $this->form($c_form);
        $this->output('</div>', '');
    }
    
    function ranking($ranking)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->part_title($ranking);
        
        $class = (@$ranking['type'] == 'users') ? 'qa-top-users' : 'qa-top-tags';
        $rows  = min($ranking['rows'], count($ranking['items']));
        
        if (@$ranking['type'] == 'users') {
            $this->output('<div class="page-users-list clearfix"><div class="row">');
            
            if(isset($ranking['items']))
				$columns=ceil(count($ranking['items'])/$rows);
				
			
			
            if (isset($ranking['items']))
                foreach ($ranking['items'] as $user) {
					$this->output('<div class="user-box col-sm-' . ceil(12 / $columns) . '">');
					
                    if (isset($user['raw']))
                        $handle = $user['raw']['handle'];
                    else
                        $handle = ltrim(strip_tags($user['label']));
                    
                    $data   = qw_user_data($handle);
                    $profile  = qw_user_profile($handle);
                    $avatar = qw_get_avatar($handle, 50, false);
					
                    $this->output('

							<div class="user-box-inner">
								<div class="cover"'.qw_get_user_cover($profile, true, true).'>
									<div class="user-avatar">
										<a href="' . qa_path_html('user/' . $handle) . '" class="avatar">
											<img class="avatar" src="' . $avatar . '" />
										</a>
									</div>
								</div>
								
								<div class="box-container">
									<a class="user-name" href="' . qa_path_html('user/' . $handle) . '">' . $handle. '</a>
									<span class="user-level">'.qa_user_level_string($data['account']['level']).'</span>
									<div class="counts clearfix">
										<p class="score"><strong>'.$data['points']['points'].'</strong>'. qa_lang('cleanstrap/points') . ' </p>
										<p class="followers"><strong>'.$data['followers'].'</strong>' .  qa_lang('cleanstrap/followers') . ' </p>
									</div>
							</div>');
                    if (qa_opt('badge_active') && function_exists('qa_get_badge_list'))
                        $this->output('<div class="badge-list">' . qw_user_badge($handle) . '</div>');
                    
                    $this->output('</div>');
                    $this->output('</div>');

                } else
                $this->output('
							<div class="no-items">
								<h3 class="icon-warning">' . qa_lang_html('cleanstrap/no_users') . '</h3>
								<p>' . qa_lang_html('cleanstrap/edit_user_detail') . '</p>
							</div>');
            
            
            $this->output('</div>');
            $this->output('</div>');
            
        } elseif (@$ranking['type'] == 'tags') {
            
            if ($rows > 0) {
                $this->output('<div id="tags-list" class="row ' . $class . '">');
				
				$tags = array();
				foreach(@$ranking['items'] as $item)
					$tags[] = strip_tags($item['label']);
				
				
                $columns = ceil(count($ranking['items']) / $rows);
                
                for ($column = 0; $column < $columns; $column++) {
                    $this->set_context('ranking_column', $column);
                    $this->output('<div class="col-lg-' . ceil(12 / $columns) . '">');
                    $this->output('<ul>');
                    
                    for ($row = 0; $row < $rows; $row++) {
                        $this->set_context('ranking_row', $row);
                        $this->qw_tags_item(@$ranking['items'][$column * $rows + $row], $class, $column > 0);
                    }
                    
                    $this->clear_context('ranking_column');
                    
                    $this->output('</ul>');
                    $this->output('</div>');
                }
                
                $this->clear_context('ranking_row');
                
                $this->output('</div>');
            } else
                $this->output('
					<div class="no-items">
					<h3 class="icon-warning">' . qa_lang('cleanstrap/no_tags') . '</h3>
					<p>' . qa_lang('cleanstrap/no_results_detail') . '</p>
					</div>');
            
        } else {
            
            
            if ($rows > 0) {
                $this->output('<table class="' . $class . '-table">');
                
                $columns = ceil(count($ranking['items']) / $rows);
                
                for ($row = 0; $row < $rows; $row++) {
                    $this->set_context('ranking_row', $row);
                    $this->output('<tr>');
                    
                    for ($column = 0; $column < $columns; $column++) {
                        $this->set_context('ranking_column', $column);
                        $this->ranking_item(@$ranking['items'][$column * $rows + $row], $class, $column > 0);
                    }
                    
                    $this->clear_context('ranking_column');
                    
                    $this->output('</tr>');
                }
                
                $this->clear_context('ranking_row');
                
                $this->output('</table>');
            } else
                $this->output('
						<div class="no-items">
							<h3 class="icon-warning">' . qa_lang_html('cleanstrap/no_results') . '</h3>
							<p>' . qa_lang_html('cleanstrap/no_results_detail') . '</p>
						</div>');
        }
    }
    function qw_tags_item($item, $class, $spacer)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $content = qa_db_read_one_value( qa_db_query_sub("SELECT ^tagmetas.content FROM ^tagmetas WHERE ^tagmetas.tag =$ ", strip_tags($item['label'])), true);
		
        if (isset($item))
            $this->output(
				'<li class="tag-item">',
					'<div class="panel">',
						'<p class="tag-head">',
							$item['label'] . '<span> &#215; ' . $item['count'] . '</span>',
						 '</p><p class="desc">',
						 qw_truncate($content, 150),
						 '</p>',
					 '</div>',
				 '</li>'
			);
    }
    function message_list_form($list)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (!empty($list['form'])) {
            $this->output('<div class="qa-message-list-form">');
				$this->output('<div class="asker-avatar">');
					$this->output(qw_get_avatar(qa_get_logged_in_handle(), 35));
				$this->output('</div>');
				$this->output('<div class="qa-message-list-inner">');
					$this->form($list['form']);
				$this->output('</div>');
            $this->output('</div>');
        }
    }
    
    function message_item($message)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="qa-message-item" ' . @$message['tags'] . '>');
        $this->output('<div class="asker-avatar">');
        $this->output(qw_get_avatar(@$message['raw']['fromhandle'], 35));
        $this->output('</div>');
        $this->output('<div class="qa-message-item-inner">');
		$this->message_buttons($message);
			$this->output('<div class="no-overflow">');
				$this->post_meta($message, 'qa-message');
				$this->message_content($message);        
			$this->output('</div>');
        $this->output('</div>');
        $this->output('</div> <!-- END qa-message-item -->', '');
    }
	
    function message_content($message)
	{
		if (!empty($message['content'])) {
			$this->output('<div class="qa-message-content">');
			$this->output_raw($message['content']);
			$this->output('</div>');
		}
	}
	
        
    function nav_list($navigation, $class, $level = null)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }

        if ($class == 'browse-cat') {
            $row = ceil(count($navigation) / 2);
            $this->output('<div class="category-list-page"><div class="row">');
            if($level < 2)
    $this->output('<div class="col-lg-6"><ul class="page-cat-list">');
   else
    $this->output('<div class="col-lg-12"><ul class="page-cat-list">');

            $index = 0;
            $i = 1;
            foreach ($navigation as $key => $navlink) {
                $this->set_context('nav_key', $key);
                $this->set_context('nav_index', $index++);
                $this->qw_cat_items($key, $navlink, $class, $level);
                if ($row == $i){
                    $this->output('</ul></div>');
                     if($level < 2)
      $this->output('<div class="col-lg-6"><ul class="page-cat-list">');
     else
      $this->output('<div class="col-lg-12"><ul class="page-cat-list">');
    }

                $i++;
            }
            
            $this->clear_context('nav_key');
            $this->clear_context('nav_index');
            
            $this->output('</ul></div></div></div>');
            
        } else {
            
            $this->output('<ul class="qa-' . $class . '-list' . (isset($level) ? (' qa-' . $class . '-list-' . $level) : '') . '">');
            
            $index = 0;

            foreach ($navigation as $key => $navlink) {
				if (count($navlink)>1){
					$this->set_context('nav_key', $key);
					$this->set_context('nav_index', $index++);
					$this->nav_item($key, $navlink, $class, $level);
				}
            }
            
            $this->clear_context('nav_key');
            $this->clear_context('nav_index');
            
            $this->output('</ul>');
        }
    }

    function qw_cat_items($key, $navlink, $class, $level = null)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $suffix = strtr($key, array( // map special character in navigation key
            '$' => '',
            '/' => '-'
        ));
        
        $this->output('<li class="panel ra-cat-item' . (@$navlink['opposite'] ? '-opp' : '') . (@$navlink['state'] ? (' ra-cat-' . $navlink['state']) : '') . ' ra-cat-' . $suffix . '">');
        $this->qw_cat_item($navlink, 'cat');
        if (count(@$navlink['subnav']))
            $this->nav_list($navlink['subnav'], $class, 2);
        
        $this->output('</li>');
    }
    function qw_cat_item($navlink, $class)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (isset($navlink['url']))
            $this->output('<h4>' . (strlen(@$navlink['note']) ? '<span>' . qw_url_grabber($navlink['note']) . '</span>' : '') . '<a href="' . $navlink['url'] . '" class="ra-' . $class . '-link' . (@$navlink['selected'] ? (' ra-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' ra-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . (isset($navlink['target']) ? (' target="' . $navlink['target'] . '"') : '') . '>' . (@$navlink['favorited'] ? '<i class="icon-star" title="' . qa_lang_html('cleanstrap/category_favourited') . '"></i>' : '') . $navlink['label'] . '</a>' . '</h4>');
        
        else
            $this->output('<h4 class="ra-' . $class . '-nolink' . (@$navlink['selected'] ? (' ra-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' ra-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . '>' . (strlen(@$navlink['note']) ? '<span>' . qw_url_grabber($navlink['note']) . '</span>' : '') . (@$navlink['favorited'] ? '<i class="icon-star" title="' . qa_lang_html('cleanstrap/category_favourited') . '"></i>' : '') . $navlink['label'] . '</h4>');
        
        if (strlen(@$navlink['note']))
            $this->output('<span class="ra-' . $class . '-note">' . str_replace('-', '', preg_replace('/<a[^>]*>(.*)<\/a>/iU', '', $navlink['note'])) . '</span>');
    }
    
    function a_selection($post)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $this->output('<div class="qa-a-selection">');
        $code = qa_get_form_security_code('buttons-'.$post['raw']['postid']);
        if (isset($post['select_tags']))
			$this->output('<a class="icon-tick btn" href="#" onclick="return qw_select_answer('.$post['raw']['postid'].', '.$post['raw']['parentid'].', this, \''.$code.'\', \'a'.$post['raw']['postid'].'_doselect\');" title="Click to select as best answer">Select answer</a>');
           //$this->qw_hover_button($post, 'select_tags', qa_lang('cleanstrap/select_answer'), 'icon-input-checked qa-a-select');
        elseif (isset($post['unselect_tags']))
			$this->output('<a class="icon-tick btn btn-success" href="#" onclick="return qw_select_answer('.$post['raw']['postid'].', '.$post['raw']['parentid'].', this, \''.$code.'\', \'a'.$post['raw']['postid'].'_dounselect\');" title="Click to unselect this answer">Unselect</a>');
            //$this->qw_hover_button($post, 'unselect_tags', @$post['select_text'], 'icon-tick qa-a-unselect');
        elseif ($post['selected'])
            $this->output('<div class="qa-a-selected icon-tick">' . @$post['select_text'] . '</div>');
        
        
        $this->output('</div>');
    }
    function qw_hover_button($post, $element, $value, $class)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        if (isset($post[$element]))
            $this->output('<button ' . $post[$element] . ' type="submit" class="' . $class . '-button">'.$value.'</button>');
    }
    
    function qw_position($position)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $widgets         = $this->widgets;
        $position_active = multi_array_key_exists($position, $widgets);
        $template = (!empty($this->request) ? $this->template : 'home');
        if (isset($widgets) && $position_active && $this->qw_position_active($position)) {
			$this->output('<div id="' . str_replace(' ', '-', strtolower($position)) . '-position">');
            foreach ($widgets as $w) {
                
                if (($w['position'] == $position) && isset($w['param']['locations'][$template]) && (bool) $w['param']['locations'][$template]) {
					$new_opt = array();
					foreach($w['param']['options'] as $k => $d){
						$new_opt[$k] = urldecode($d);
					}
					$w['param']['options'] = $new_opt;
                    $this->current_widget = $w;
                    $this->qw_get_widget($w['name'], @$w['param']['locations']['show_title'], $position);
                }
            }
			$this->output('</div>');
        }
    }

    
	function qw_is_widget_active($name){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$template = (!empty($this->request) ? $this->template : 'home');
	    $widgets         = $this->widgets;
          
        if (isset($widgets)) {
            foreach ($widgets as $w) {                
                if ( isset($w['param']['locations'][$template]) && (bool) $w['param']['locations'][$template] && ($w['name'] == $name)) 
					return true;                
            }
        }
		return false;
	}
	
    function qw_get_widget($name, $show_title = false, $position)
    {
        if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $module = qa_load_module('widget', ltrim($name));
        if (is_object($module)) {
            ob_start();
			echo '<div class="widget '.strtolower(str_replace(' ', '_', $name)).'">';
            $module->output_widget('side', 'top', $this, $this->template, $this->request, $this->content);  
			echo '</div>';			
            $this->output(ob_get_clean());
        }
        return;
    }
    
    
    function qw_ajax_get_question_suggestion()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $query            = strip_tags($_REQUEST['start_with']);
        $relatedquestions = qa_db_select_with_pending(qa_db_search_posts_selectspec(null, qa_string_to_words($query), null, null, null, null, 0, false, 10));
        //print_r($relatedquestions);
        
        if (isset($relatedquestions) && !empty($relatedquestions)) {
            $data = array();
            foreach ($relatedquestions as $k => $q) {
                $data[$k]['title']   = $q['title'];
                $data[$k]['blob']    = qw_get_avatar($q['handle'], 30, false);
                $data[$k]['url']     = qa_q_path_html($q['postid'], $q['title']);
                $data[$k]['tags']    = $q['tags'];
                $data[$k]['answers'] = $q['acount'];
            }
            echo json_encode($data);
        }
        
        die();
    }
    function q_list($q_list)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$userid=qa_get_logged_in_userid();
        if (isset($q_list['qs'])) {
            if (qa_opt('qw_enable_except')) { // first check it is not an empty list and the feature is turned on
                //	Collect the question ids of all items in the question list (so we can do this in one DB query)
                $postids = array();
                foreach ($q_list['qs'] as $question)
                    if (isset($question['raw']['postid']))
                        $postids[] = $question['raw']['postid'];
                if (count($postids)) {
                    //	Retrieve the content for these questions from the database and put into an array
                    //$result         = qa_db_query_sub('SELECT postid, content, format FROM ^posts WHERE postid IN (#)', $postids);
                    //$postinfo       = qa_db_read_all_assoc($result, 'postid');
					//cache and apply keys to array now that I can't use array key argument in qa_db_read_all_assoc
					$posts = qw_get_cache('SELECT postid, content, format FROM ^posts WHERE postid IN (#)',50, $postids);
					$postinfo= array();
					foreach ($posts as $qitem) {
						$postinfo[$qitem['postid']] = $qitem;
					}
                    //	Get the regular expression fragment to use for blocked words and the maximum length of content to show
                    $blockwordspreg = qa_get_block_words_preg();
                    $maxlength      = qa_opt('qw_except_len');
                    //	Now add the popup to the title for each question
                    foreach ($q_list['qs'] as $index => $question) {
                        $thispost = @$postinfo[$question['raw']['postid']];
                        if (isset($thispost)) {
                            $text                            = qa_viewer_text($thispost['content'], $thispost['format'], array(
                                'blockwordspreg' => $blockwordspreg
                            ));
                            $text                            = qa_shorten_string_line($text, $maxlength);
                            $q_list['qs'][$index]['content'] = '<SPAN>' . qa_html($text) . '</SPAN>';
							if (isset($userid))
								$q_list['qs'][$index]['favorite']=qa_favorite_form(QA_ENTITY_QUESTION, $question['raw']['postid'], $question['raw']['userfavoriteq'], qa_lang($question['raw']['userfavoriteq'] ? 'question/remove_q_favorites' : 'question/add_q_favorites'));
                        }
                    }
                }
            }
            $this->output('<div class="qa-q-list' . ($this->list_vote_disabled($q_list['qs']) ? ' qa-q-list-vote-disabled' : '') . '">', '');
            $this->q_list_items($q_list['qs']);
            $this->output('</div> <!-- END qa-q-list -->', '');
        } else
            $this->output('
					<div class="no-items">
						<h3 class="icon-warning">' . qa_lang_html('cleanstrap/no_users') . '</h3>
						<p>' . qa_lang_html('cleanstrap/no_results_detail') . '.</p>
					</div>');
    }

    function qw_ajax_save_q_meta()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
        $postid = @$this->content["q_view"]["raw"]["postid"];
        @$featured_image = $_REQUEST['featured_image'];
        
        if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)) {
            if (!empty($featured_image)) {
                qa_db_postmeta_set($postid, 'featured_image', $featured_image);
            } else
                qa_db_postmeta_clear($postid, 'featured_image');
            
        }
        die(Q_THEME_URL . '/uploads/' . $featured_image);
    }
    function qw_position_active($name)
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        $widgets  = $this->widgets;
        $template = $this->template;
        $template = (!empty($this->request) ? $template : 'home');
        if (isset($widgets) && is_array($widgets)) {
            foreach ($widgets as $w) {
                
                if (($w['position'] == $name) && isset($w['param']['locations'][$template]) && (bool) $w['param']['locations'][$template])
                    return true;
            }
            
        }
        return false;
    }
    
    function qw_ajax_set_question_featured()
    {
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
        require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
        $postid = @$this->content["q_view"]["raw"]["postid"];
        
        if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)) {
            if (!is_featured($postid))
                qa_db_postmeta_set($postid, 'featured_question', true);
            else
                qa_db_postmeta_clear($postid, 'featured_question');
        }
        die();
    }
   
	
	function body_hidden()
	{
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if(qa_opt('qw_styling_rtl'))
			$this->output('<div style="position:absolute; left:9999px; bottom:9999px;">');
		else
			$this->output('<div style="position:absolute; left:-9999px; top:-9999px;">');
			
		$this->waiting_template();
		$this->output('</div>');
	}
	
	
	function c_list($c_list, $class){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		if (!empty($c_list)) {
			$this->output('', '<div class="'.$class.'-c-list"'.(@$c_list['hidden'] ? ' style="display:none;"' : '').' '.@$c_list['tags'].'>');
			$this->output('<div class="comment-count icon-comment">'.count($c_list['cs']).' '.qa_lang('cleanstrap/comments').'</div>');
			$this->output('<div class="comment-items">');
			$this->c_list_items($c_list['cs']);
			$this->output('</div>');
			$this->output('</div> <!-- END qa-c-list -->', '');
		}
	}
	function q_social_share(){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$this->output('
			<div class="btn-group social-share-btn">
				<a class="btn btn-default btn-lg dropdown-toggle share-btn" type="button" data-toggle="dropdown">'. qa_lang_html('cleanstrap/share') .'</a>
				<div class="dropdown-menu">
					<!-- AddThis Button BEGIN -->
					<div class="addthis_toolbox addthis_default_style">
					<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
					<a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:action="recommend"></a> 
					<a class="addthis_button_tweet"></a>
					<a class="addthis_button_pinterest_pinit" pi:pinit:layout="horizontal"  pi:pinit:media="http://www.addthis.com/cms-content/images/features/pinterest-lg.png"></a>
					<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
					<a class="addthis_counter addthis_pill_style"></a>
					</div>
					<!-- AddThis Button END -->
				</div>
			</div>
		');
		qw_do_action('question_share', $this);	
	}
	
	function fb_ask_your_friend($link, $label = 'Ask your friends'){
		if (function_exists('qw_get_fb_msg_button')) { /*make sure the social plugin is enabled */
			$this->output(qw_get_fb_msg_button($link, $label));
		}
	}
	function notfound_template($content){
		if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		$this->output('
			<div class="error-404">
				<span class="icon-broken"></span>
				<div class="message">
					<h1>'.qa_lang_html('cleanstrap/oopps_page_not_found').'</h1>
					<p class="desc">'.qa_lang_html('cleanstrap/mistyped_url').'</p>
					<div class="suggestion">');
					$this->nav('main');
				$this->output('</div></div>
			</div>
		');
	}
	
	function form_ok($form, $columns)
	{
		if (!empty($form['ok']))
			$this->output(
				'<tr>',
				'<td colspan="'.$columns.'" class="qa-form-'.$form['style'].'-ok">',
				'<div class="alert alert-success">'.$form['ok'].'</div>',
				'</td>',
				'</tr>'
			);
	}
	function part_title($part)
	{
		if (strlen(@$part['title']) || strlen(@$part['title_tags']))
			$this->output('<h3 class="form-title" '.rtrim(' '.@$part['title_tags']).'>'.@$part['title'].'</h3>');
	}

}
