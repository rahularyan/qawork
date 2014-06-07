<?php

class qw_fb_invite_frnds_widget {

      function qw_widget_form() {

            return array(
                'fields' => array(),
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
            $message = qa_opt("qw_fb_invite_message") ;
            if (!$message) {
                  $message = qa_lang_html('qw_social_posting/fb_invite_message_default') ; 
            }

            $message = strtr($message , array('{site_url}' => QW_BASE_URL ));
            // widget start 
            if(@$themeobject->current_widget['param']['locations']['show_title'])
                        $themeobject->output('<h3 class="widget-title">QW Facebook Invite</h3>');
            $themeobject->output('<div class="fb-invite-frnds clearfix">');
            if (!!qa_opt("facebook_app_id")) {
                  $on_click_event = qw_generate_facebook_invite_script(qa_opt("facebook_app_id"), array('url' => QW_BASE_URL , 'message' => $message))  ;
                  $button = '<button class="btn btn-block btn-facebook" onclick="'.$on_click_event.'">'.qa_lang_html('qw_social_posting/send_facebook_invite').'</button>' ;
                  $themeobject->output($button );
            }else {
                  $themeobject->output("Please provide Facebook application Id to enable this option in Theme Options -> Social Login ");
            }
            
            $themeobject->output('</div>');
            // widget end 
       }
}

/*

	Omit PHP closing tag to help avoid accidental output

*/
