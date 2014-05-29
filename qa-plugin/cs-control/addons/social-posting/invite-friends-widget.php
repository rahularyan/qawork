<?php

class cs_fb_invite_frnds_widget {

      function cs_widget_form() {

            return array(
                'fields' => array(
                ),
            );
      }

      function allow_template($template) {
            $allow = false;
            switch ($template) {
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
                        $allow = true;
                        break;
            }
            return $allow;
      }

      function allow_region($region) {
            $allow = false;
            switch ($region) {
                  case 'main':
                  case 'side':
                  case 'full':
                        $allow = true;
                        break;
            }
            return $allow;
      }

      function output_widget($region, $place, $themeobject, $template, $request, $qa_content) {
            $widget_opt  = @$themeobject->current_widget['param']['options'];
            require_once CS_CONTROL_DIR . '/addons/social-posting/cs-social-posting-utils.php';
           
            // widget start 
            if(@$themeobject->current_widget['param']['locations']['show_title'])
                        $themeobject->output('<h3 class="widget-title">CS Facebook Invite</h3>');

            $themeobject->output('<div class="fb-invite-frnds clearfix">');

            $on_click_event = cs_generate_facebook_invite_script(qa_opt("facebook_app_id"), array('url' => qa_opt("site_url")))  ;
            $button = '<button class="btn btn-block btn-facebook" onclick="'.$on_click_event.'">'.qa_lang_html('cs_social_posting/send_facebook_invite').'</button>' ;
            $themeobject->output($button );
            $themeobject->output('</div>');
            // widget end 
       }
}

/*

	Omit PHP closing tag to help avoid accidental output

*/
