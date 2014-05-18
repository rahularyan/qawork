<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}

function reset_all_notification_options() {
      qa_opt('cs_notify_cat_followers', false);
      qa_opt('cs_notify_tag_followers', false);
      qa_opt('cs_notify_user_followers', false);
      qa_opt('cs_notify_min_points_opt', false);
      qa_opt('cs_notify_min_points_val', false);
}

function reset_all_notification_points_options() {
      qa_opt('cs_notify_min_points_opt', false);
      qa_opt('cs_notify_min_points_opt', false);
}

function set_all_notification_options() {

      $error = array();
      //if plugin is enabled then atlest one option has to be enabled 
      if (options_selected()) {
            qa_opt('cs_email_notf_debug_mode', !!qa_post_text('cs_email_notf_debug_mode_field'));
            qa_opt('cs_notify_cat_followers', !!qa_post_text('cs_notify_cat_followers_field'));
            qa_opt('cs_notify_tag_followers', !!qa_post_text('cs_notify_tag_followers_field'));
            qa_opt('cs_notify_user_followers', !!qa_post_text('cs_notify_user_followers_field'));
            $minimum_user_point_option = !!qa_post_text('cs_notify_min_points_opt_field');
            if ($minimum_user_point_option) { //if minimum point option is checked 
                  $minimum_user_point_value = qa_post_text('cs_notify_min_points_val_field');
                  if (!!$minimum_user_point_value && is_numeric($minimum_user_point_value) && $minimum_user_point_value > 0) { //if the minimum point value is provided then only set else reset
                        qa_opt('cs_notify_min_points_opt', $minimum_user_point_option);
                        qa_opt('cs_notify_min_points_val', (int) $minimum_user_point_value);
                  } else if (!is_numeric($minimum_user_point_value) || $minimum_user_point_value <= 0) {
                        reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = "The points value should be a numeric and non-zero positive integer ";
                  } else {
                        reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = "The points value is required to enable the option ";
                  }
            } else {
                  reset_all_notification_points_options();
            }
      } else {
            //if none of the elements are selected disable the plugin and send a error message UI 
            qa_opt('cs_email_notf_enable', false);
            reset_all_notification_options();
            $error['no_options_selected'] = "Please choose atleast follower option to enable this plugin ";
      }
      return $error;
}

function options_selected() {
      return ((!!qa_post_text('cs_notify_cat_followers_field')) ||
              (!!qa_post_text('cs_notify_tag_followers_field')) ||
              (!!qa_post_text('cs_notify_user_followers_field')) );
}


