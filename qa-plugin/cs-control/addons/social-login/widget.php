<?php

class cs_social_login_widget {

      function cs_widget_form() {

            return array(
                'fields' =>  array(
                    'cs_breadcrumb_show_home' => array(
                                    'label' => 'Show the home link ',
                                    'type'  => 'select',
                                    'tags'  => 'name="cs_breadcrumb_show_home"',
                                    'value' => '1',
                                    'options' => array(
                                          '1'  => qa_lang_html('cleanstrap/yes'),
                                          '0'  => qa_lang_html('cleanstrap/no'),
                                    )
                    ),
                    'cs_breadcrumb_trunc_len' => array(
                                    'label' => 'Truncate title in breadcrumb if No category exists',
                                    'type'  => 'text',
                                    'tags'  => 'name="cs_breadcrumb_trunc_len"',
                                    'value' => '0',
                    ),
                ),
            );
      }

      function allow_template($template) {
            $allow = false;
            switch ($template) {
                  case 'account':
                  case 'favorites':
                  case 'register':
                  case 'login':
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
            $widget_opt = @$themeobject->current_widget['param']['options'];
            
       }



}
/*

	Omit PHP closing tag to help avoid accidental output

*/
