<?php

class qw_google_plus_badge {

      function qw_widget_form() {

            return array(
                'fields' => array(
                    'qw_gp_badge_url' => array(
                                    'label' => qa_lang('qw_gp_badge/qw_gp_badge_url_lable'),
                                    'type'  => 'text',
                                    'tags'  => 'name="qw_gp_badge_url"',
                                    'value' => '//plus.google.com/u/0/+rahularyannerdaryan',
                    ),
                    'qw_gp_badge_type' => array(
                                    'label' => qa_lang('qw_gp_badge/qw_gp_badge_type_lable'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_type"',
                                    'value' => 'g-person',
                                    'options' => array(
                                          'g-person'    => qa_lang('qw_gp_badge/profile'),
                                          'g-page'      => qa_lang('qw_gp_badge/page'),
                                          'g-community' => qa_lang('qw_gp_badge/community'),
                                    ),
                    ),

                    'qw_gp_badge_layout' => array(
                                    'label' => qa_lang('qw_gp_badge/layout_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_layout"',
                                    'value' => 'portrait',
                                    'options' => array(
                                          'portrait' => qa_lang('qw_gp_badge/portrait'),
                                          'landscape'  => qa_lang('qw_gp_badge/landscape'),
                                    ),
                    ),
                    'qw_gp_badge_theme' => array(
                                    'label' => qa_lang('qw_gp_badge/theme_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_theme"',
                                    'value' => 'light',
                                    'options' => array(
                                          'light'  => qa_lang('qw_gp_badge/light'),
                                          'dark' => qa_lang('qw_gp_badge/dark'),
                                    ),
                    ),

                   'qw_gp_badge_showcoverphoto' => array(
                                    'label' => qa_lang('qw_gp_badge/qw_gp_showcoverphoto_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_showcoverphoto"',
                                    'value' => 'true',
                                    'options' => array(
                                          'true'  => qa_lang('qw_gp_badge/yes'),
                                          'false' => qa_lang('qw_gp_badge/no'),
                                    ),
                    ),
                   'qw_gp_badge_showphoto' => array(
                                    'label' => qa_lang('qw_gp_badge/qw_gp_showphoto_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_showphoto"',
                                    'value' => 'true',
                                    'options' => array(
                                          'true'  => qa_lang('qw_gp_badge/yes'),
                                          'false' => qa_lang('qw_gp_badge/no'),
                                    ),
                    ),

                  'qw_gp_badge_show_owners' => array(
                                    'label' => qa_lang('qw_gp_badge/qw_gp_show_owners_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_show_owners"',
                                    'value' => 'false',
                                    'options' => array(
                                          'true'  => qa_lang('qw_gp_badge/yes'),
                                          'false' => qa_lang('qw_gp_badge/no'),
                                    ),
                    ),

                   'qw_gp_badge_showtagline' => array(
                                    'label' => qa_lang('qw_gp_badge/showtagline_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="qw_gp_badge_showtagline"',
                                    'value' => 'true',
                                    'options' => array(
                                          'true'  => qa_lang('qw_gp_badge/yes'),
                                          'false' => qa_lang('qw_gp_badge/no'),
                                    ),
                    ),
                    
                   'qw_gp_badge_width' => array(
						'label' => qa_lang('qw_gp_badge/qw_gp_badge_width_label'),
						'type'  => 'text',
						'tags'  => 'name="qw_gp_badge_width"',
						'value' => '360',
                    ),

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

            $href        =  $this->get_google_plus($widget_opt , 'href') ;
            if (!$href) {
                  return false;
            }
            $data = array();
            // get the other settings 
            $class       =  $this->get_google_plus($widget_opt , 'class') ; 
            $layout      =  $this->get_google_plus($widget_opt , 'layout') ; 
            $showtagline =  $this->get_google_plus($widget_opt , 'showtagline') ; 
            $width       =  $this->get_google_plus($widget_opt , 'width') ; 
            $theme       =  $this->get_google_plus($widget_opt , 'theme') ; 

            $data['class']       = 'class="'.$class.'"' ;
            $data['href']        = 'data-href="'.$href.'"' ;
            $data['layout']      = 'data-layout="'.$layout.'"' ;
            $data['showtagline'] = 'data-showtagline="'.$showtagline.'"' ;
            $data['width']       = 'data-width="'.$width.'"' ;
            $data['theme']       = 'data-theme="'.$theme.'"' ;

            if ($class=="g-community") {
                   $showphoto          =  $this->get_google_plus($widget_opt , 'showphoto') ; 
                   $data['showphoto']  = 'data-showphoto="'.$showphoto.'"' ;
                   $showowners         =  $this->get_google_plus($widget_opt , 'showowners') ; 
                   $data['showowners'] = 'data-showowners="'.$showowners.'"' ;
            }else {
                   $show_coverphoto         =  $this->get_google_plus($widget_opt , 'show_coverphoto') ; 
                   $data['show_coverphoto'] = 'data-showcoverphoto="'.$show_coverphoto.'"' ;
            }

            if ($class = 'g-person') {
                  $data['rel'] = 'data-rel="author"' ;
            }

            $data_str = implode(' ', $data ) ;
            $gp_badge = '<div '.$data_str.'></div>'  ;

            // widget start 
            if(@$themeobject->current_widget['param']['locations']['show_title'])
                        $themeobject->output('<h3 class="widget-title">Google Plus Badge</h3>');

            $themeobject->output('<div class="google-plus clearfix">');
            $themeobject->output($gp_badge);
            $themeobject->output('</div>');
            // widget end 
       }

       function get_google_plus($widget_opt , $opt )
       {
            $value = "" ;
             switch ($opt) {
                  case 'class':
                         $value         = isset($widget_opt['qw_gp_badge_type']) ? $widget_opt['qw_gp_badge_type'] : "" ;
                         $allowed_value = array('g-person' , 'g-page' , 'g-community'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "g-person" ;
                         }
                         break;
                  case 'href':
                         $value = isset($widget_opt['qw_gp_badge_url']) ? $widget_opt['qw_gp_badge_url'] : "" ;
                         break;
                  case 'layout':
                         $value         = isset($widget_opt['qw_gp_badge_layout']) ? $widget_opt['qw_gp_badge_layout'] : "" ;
                         $allowed_value = array('landscape' , 'portrait'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "portrait" ;
                         }
                         break;
                  case 'showtagline':
                         $value         = isset($widget_opt['qw_gp_badge_showtagline']) ? $widget_opt['qw_gp_badge_showtagline'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "true" ;
                         }
                         break;
                  case 'theme':
                         $value         = isset($widget_opt['qw_gp_badge_theme']) ? $widget_opt['qw_gp_badge_theme'] : "" ;
                         $allowed_value = array('light' , 'dark'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "light" ;
                         }
                         break;
                  case 'showphoto':
                         $value         = isset($widget_opt['qw_gp_badge_showphoto']) ? $widget_opt['qw_gp_badge_showphoto'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "true" ;
                         }
                         break;
                  case 'showowners':
                         $value         = isset($widget_opt['qw_gp_badge_show_owners']) ? $widget_opt['qw_gp_badge_show_owners'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "false" ;
                         }
                         break;
                  case 'show_coverphoto':
                         $value         = isset($widget_opt['qw_gp_badge_showcoverphoto']) ? $widget_opt['qw_gp_badge_showcoverphoto'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "true" ;
                         }
                         break;
                  case 'width':
                         $value     = isset($widget_opt['qw_gp_badge_width']) ? $widget_opt['qw_gp_badge_width'] : "" ;
                         $min_width = 300 ; /*allow only these values*/
                         if (!$value || $value < $min_width) {
                               $value = $min_width ;
                         }
                         break;
                   default:
                         break;
             }
             return $value ;
       }
}

/*

	Omit PHP closing tag to help avoid accidental output

*/
