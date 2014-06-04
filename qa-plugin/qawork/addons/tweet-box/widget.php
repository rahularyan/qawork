<?php
  class qw_tweet_box_widget {

    function qw_widget_form() {
      
      return array(
        'fields' => array(
          'qw_twitter_id' => array(
                  'label' => qa_lang('qw_tweet_box/qw_twitter_id_label'),
                  'type'  => 'text',
                  'tags'  => 'name="qw_twitter_id"',
                  'value' => '',
           ),
          
           'qw_twitter_t_count' => array(
                  'label' => qa_lang('qw_tweet_box/qw_twitter_t_count_label'),
                  'type'  => 'text',
                  'tags'  => 'name="qw_twitter_t_count"',
                  'value' => '',
           ),
          'qw_twitter_ck' => array(
                  'label' => qa_lang('qw_tweet_box/qw_twitter_ck_label'),
                  'type'  => 'text',
                  'tags'  => 'name="qw_twitter_ck"',
                  'value' => '',
           ),
            'qw_twitter_cs' => array(
                  'label' => qa_lang('qw_tweet_box/qw_twitter_qw_label'),
                  'type'  => 'text',
                  'tags'  => 'name="qw_twitter_cs"',
                  'value' => '',
           ),
            'qw_twitter_at' => array(
                  'label' => qa_lang('qw_tweet_box/qw_twitter_at_label'),
                  'type'  => 'text',
                  'tags'  => 'name="qw_twitter_at"',
                  'value' => '',
           ),
            'qw_twitter_ts' => array(
                  'label' => qa_lang('qw_tweet_box/qw_twitter_ts_label'),
                  'type'  => 'text',
                  'tags'  => 'name="qw_twitter_ts"',
                  'value' => '',
           ),
          
        ),
      );
    }

    
    function allow_template($template) {
      $allow=false;
      
      switch ($template)
      {
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
          $allow=true;
          break;
      }
      
      return $allow;
    }

    
    function allow_region($region) {
      $allow=false;
      
      switch ($region)
      {
        case 'main':
        case 'side':
        case 'full':
          $allow=true;
          break;
      }
      
      return $allow;
    }
    function twitter_api_error_html() {
      return 'To use twitter API you must register your application. to do this visit <a href="https://dev.twitter.com/">twitter development Page</a> and log in with your Twitter credential. then visit <a href="https://dev.twitter.com/apps/">My applications</a> and creat your application and fill these fields from your application API detail. <br /> if these fields are set correctly and your application has permission to work with this domain then you will get your recent tweets in this widget.'; 
    }
    function get_tweets()
    {
      global $cache;
      $age = 3600; //one hour
      if (isset($cache['twitter'])){
        if ( ((int)$cache['twitter']['age'] + $age) > time()) {
          $tweets = $cache['twitter'];
          unset($tweets['age']);
          return $tweets;
        }
      }

      $user = $this->get_tw_settings($widget_opt ,'qw_twitter_id');
      $count=(int)$this->get_tw_settings($widget_opt ,'qw_twitter_t_count');
      $title=$this->get_tw_settings($widget_opt ,'qw_twitter_title');
      
      // Setting our Authentication Variables that we got after creating an application
      $settings = array(
        'oauth_access_token' => $this->get_tw_settings($widget_opt ,'qw_twitter_at'),
        'oauth_access_token_secret' => $this->get_tw_settings($widget_opt ,'qw_twitter_ts'),
        'consumer_key' => $this->get_tw_settings($widget_opt ,'qw_twitter_ck'),
        'consumer_secret' => $this->get_tw_settings($widget_opt ,'qw_twitter_cs')
      );

      $url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
      $requestMethod = "GET";

      $getfield = "?screen_name=$user&count=$count";
      $twitter = new TwitterAPIExchange($settings);
      $tweets = json_decode($twitter->setGetfield($getfield)
        ->buildOauth($url, $requestMethod)
          ->performRequest(),$assoc = TRUE);
      //$tweets = array(array('text' => "hello @towhidn"));
      $cache['twitter'] =  $tweets;
      $cache['twitter']['age'] = time();
      $cache['changed'] = true; 
      return $tweets;
    }

    function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
      if(!function_exists('curl_version'))
        return;
      $widget_opt  = @$themeobject->current_widget['param']['options'];
        
      $user = $this->get_tw_settings($widget_opt ,'qa_twitter_id');
      $count=(int)$this->get_tw_settings($widget_opt ,'qa_twitter_t_count');
      $title=$this->get_tw_settings($widget_opt ,'qa_twitter_title');

      $themeobject->output('<div class="qa-tweeter-widget">');
      $themeobject->output('<h2 class="qa-tweeter-header">'.$title.'</h2>');
        
      $tweets=$this->get_tweets();

      if (empty($tweets)) return;     
      $themeobject->output('<ul class="qa-tweeter-list">');
      foreach($tweets as $items)
      {
        // links
        $items['text'] = preg_replace(
          '@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@',
           '<a href="$1">$1</a>',
          $items['text']);
        //users
        $items['text'] = preg_replace(
          '/@(\w+)/',
          '<a href="http://twitter.com/$1">@$1</a>',
          $items['text']);  
        // hashtags
        $items['text'] = preg_replace(
          '/\s+#(\w+)/',
          ' <a href="http://search.twitter.com/search?q=%23$1">#$1</a>',
          $items['text']);
          
        //echo "Time and Date of Tweet: ".$items['created_at']."<br />";
        $themeobject->output( '<li class="qa-tweeter-item">'. $items['text'].'</li>');
        //echo "Tweeted by: ". $items['user']['name']."<br />";
        //echo "Screen name: ". $items['user']['screen_name']."<br />";
        //echo "Followers: ". $items['user']['followers_count']."<br />";
        //echo "Friends: ". $items['user']['friends_count']."<br />";
        //echo "Listed: ". $items['user']['listed_count']."<br /><hr />";
      }
       $themeobject->output('</ul>');

       $themeobject->output('</div>');
    }
    function get_tw_settings($widget_opt , $opt ) {
            return isset($widget_opt['qw_fb_page_url']) ? $widget_opt['qw_fb_page_url'] : "" ;
    }
  }

  
/*
  Omit PHP closing tag to help avoid accidental output
*/