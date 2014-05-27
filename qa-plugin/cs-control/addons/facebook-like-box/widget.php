<?php

class cs_fb_likebox_widget {

      function cs_widget_form() {

            return array(
                'fields' => array(
                    'cs_fb_page_url' => array(
                                    'label' => qa_lang('cs_fb_like_box/ur_fb_page_url'),
                                    'type'  => 'text',
                                    'tags'  => 'name="cs_fb_page_url"',
                                    'value' => 'http://',
                    ),
                    'cs_fb_like_box_colorscheme' => array(
                                    'label' => qa_lang('cs_fb_like_box/colorscheme_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="cs_fb_like_box_colorscheme"',
                                    'value' => 'light',
                                    'options' => array(
                                          'light' => qa_lang('cs_fb_like_box/light'),
                                          'dark'  => qa_lang('cs_fb_like_box/dark'),
                                    ),
                    ),
                   'cs_fb_like_box_header' => array(
                                    'label' => qa_lang('cs_fb_like_box/box_header_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="cs_fb_like_box_header"',
                                    'value' => 'light',
                                    'options' => array(
                                          'true'  => qa_lang('cs_fb_like_box/yes'),
                                          'false' => qa_lang('cs_fb_like_box/no'),
                                    ),
                    ),
                   'cs_fb_like_box_show_border' => array(
                                    'label' => qa_lang('cs_fb_like_box/show_border_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="cs_fb_like_box_show_border"',
                                    'value' => 'light',
                                    'options' => array(
                                          'true'  => qa_lang('cs_fb_like_box/yes'),
                                          'false' => qa_lang('cs_fb_like_box/no'),
                                    ),
                    ),
                    'cs_fb_like_box_show_faces' => array(
                                    'label' => qa_lang('cs_fb_like_box/show_faces_label'),
                                    'type'  => 'select',
                                    'tags'  => 'name="cs_fb_like_box_show_faces"',
                                    'value' => 'light',
                                    'options' => array(
                                          'true'  => qa_lang('cs_fb_like_box/yes'),
                                          'false' => qa_lang('cs_fb_like_box/no'),
                                    ),
                    ),
                   'cs_fb_like_box_data_stream' => array(
						'label' => qa_lang('cs_fb_like_box/show_stream_label'),
						'type'  => 'select',
						'tags'  => 'name="cs_fb_like_box_data_stream"',
						'value' => 'light',
						'options' => array(
                                          'true'  => qa_lang('cs_fb_like_box/yes'),
                                          'false' => qa_lang('cs_fb_like_box/no'),
						),
                    ),

                    'cs_fb_like_box_height' => array(
                                    'label' => qa_lang('cs_fb_like_box/like_box_height_label'),
                                    'type'  => 'text',
                                    'tags'  => 'name="cs_fb_like_box_height"',
                                    'value' => '320',
                    ),
                     'cs_fb_like_box_width' => array(
						'label' => qa_lang('cs_fb_like_box/like_box_width_label'),
						'type'  => 'text',
						'tags'  => 'name="cs_fb_like_box_width"',
						'value' => '320',
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

      function navigation() {
            $request = qa_request_parts();
            if (isset($request) && !empty($request) && is_array($request)) return $request;
      }

      function output_widget($region, $place, $themeobject, $template, $request, $qa_content) {
            $widget_opt  = @$themeobject->current_widget['param']['options'];
            $fb_page_url = $this->get_fb_settings($widget_opt , 'url') ;	
            
            if (!$fb_page_url) {
                  return false;
            }

            // get the other settings 
            $colorscheme =  $this->get_fb_settings($widget_opt , 'colorscheme') ; 
            $header      =  $this->get_fb_settings($widget_opt , 'header') ; 
            $show_border =  $this->get_fb_settings($widget_opt , 'show_border') ; 
            $show_faces  =  $this->get_fb_settings($widget_opt , 'show_faces') ; 
            $stream      =  $this->get_fb_settings($widget_opt , 'stream') ; 
            $height      =  $this->get_fb_settings($widget_opt , 'height') ; 
            $width       =  $this->get_fb_settings($widget_opt , 'width') ; 

            $data['href']        = 'data-href="'.$fb_page_url.'"' ;
            $data['force_wall']  = 'data-force-wall="false"' ;
            $data['colorscheme'] = 'data-colorscheme="'.$colorscheme.'"' ;
            $data['header']      = 'data-header="'.$header.'"' ;
            $data['show_border'] = 'data-show-border="'.$show_border.'"' ;
            $data['show_faces']  = 'data-show-faces="'.$show_faces.'"' ;
            $data['stream']      = 'data-stream="'.$stream.'"' ;
            $data['height']      = 'data-height="'.$height.'"' ;
            $data['width']       = 'data-width="'.$width.'"' ;
            $data_str = implode(' ', $data ) ;
            // widget start 
            $themeobject->output('<div class="fb-like-box clearfix">');
            
            $themeobject->output('<div id="fb-root"></div>');

            $fb_like_box =   '<div class="fb-like-box" '.$data_str.'> </div>'  ;
             $themeobject->output($fb_like_box );
            $themeobject->output('</div>');
            // widget end 
       }
       function get_fb_settings($widget_opt , $opt )
       {
            $value = "" ;
             switch ($opt) {
                   case 'url':
                   case 'href':
                         $value = isset($widget_opt['cs_fb_page_url']) ? $widget_opt['cs_fb_page_url'] : "" ;
                         break;
                   case 'colorscheme':
                         $value         = isset($widget_opt['cs_fb_like_box_colorscheme']) ? $widget_opt['cs_fb_like_box_colorscheme'] : "" ;
                         $allowed_value = array('light' , 'dark'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "light" ;
                         }
                         break;
                   case 'header':
                         $value         = isset($widget_opt['cs_fb_like_box_header']) ? $widget_opt['cs_fb_like_box_header'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "true" ;
                         }
                         break;
                   case 'show_border':
                         $value         = isset($widget_opt['cs_fb_like_box_show_border']) ? $widget_opt['cs_fb_like_box_show_border'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "true" ;
                         }
                         break;
                   case 'show_faces':
                         $value         = isset($widget_opt['cs_fb_like_box_show_faces']) ? $widget_opt['cs_fb_like_box_show_faces'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "true" ;
                         }
                         break;
                   case 'stream':
                         $value         = isset($widget_opt['cs_fb_like_box_data_stream']) ? $widget_opt['cs_fb_like_box_data_stream'] : "" ;
                         $allowed_value = array('true' , 'false'); /*allow only these values*/
                         if (!$value || !in_array($value , $allowed_value )) {
                               $value = "false" ;
                         }
                         break;
                   case 'height':
                         $value = isset($widget_opt['cs_fb_like_box_height']) ? $widget_opt['cs_fb_like_box_height'] : "" ;
                         if ($this->get_fb_settings($widget_opt , "data_stream") && $this->get_fb_settings($widget_opt , "show_faces")   ) {
                               // if both are true min height is 556px
                               $min_height = 556 ;
                         }elseif (!$this->get_fb_settings($widget_opt , "data_stream") && !$this->get_fb_settings($widget_opt , "show_faces") ) {
                               // if both are false min height is 63px
                               $min_height = 63 ;
                         }else {
                               // otherwise
                               $min_height = 300 ;
                         }

                         if (!$value || $value < $min_height ) {
                               $value = $min_height ;
                         }
                         break;
                   case 'width':
                         $value     = isset($widget_opt['cs_fb_like_box_width']) ? $widget_opt['cs_fb_like_box_width'] : "" ;
                         $min_width = 292 ; /*allow only these values*/
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
