<?php
class qw_widget_posts
{
    
    function qw_widget_form()
    {
        
        return array(
            'style' => 'wide',
            'fields' => array(
                'qw_qa_count' => array(
                    'label' => 'Numbers of questions',
                    'type' => 'number',
                    'tags' => 'name="qw_qa_count" class="form-control"',
                    'value' => '10'
                ),
				'post_type' => array(
                    'label' 	=> 'Post Type',
                    'type' 		=> 'select',
                    'tags' 		=> 'name="post_type" class="form-control"',
                    'options' 	=> array('Q' => 'Questions','A' => 'Answers','C' => 'Comments')
                ),
				'avatar_size' => array(
                    'label' 	=> 'Avatar size',
                    'type' 		=> 'number',
                    'tags' 		=> 'name="avatar_size" class="form-control"',
                    'value' 	=> '40'
                ),
				'class' => array(
                    'label' 	=> 'CSS class',
                    'type' 		=> 'text',
                    'tags' 		=> 'name="class" class="form-control"',
                    'value' 	=> 'custom'
                ),
				'show_content' => array(
                    'label' 	=> 'Show content',
                    'type' 		=> 'checkbox',
                    'tags' 		=> 'name="show_content"',
                    'value' 	=> '0'
                ),
				'content_size' => array(
                    'label' 	=> 'Content length',
                    'type' 		=> 'number',
                    'tags' 		=> 'name="content_size" class="form-control"',
                    'value' 	=> '80'
                )
            )
            
        );
    }
    
    
    function allow_template($template)
    {
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
    
    
    function allow_region($region)
    {
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
    function qw_post_list($type, $limit, $size = 40, $show_content, $content_limt = 80, $return = false)
    {

        if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
			global $wpdb;
			$posts = qw_get_cache('SELECT ^posts.* , '.$wpdb->base_prefix.'users.* FROM ^posts, '.$wpdb->base_prefix.'users WHERE ^posts.userid='.$wpdb->base_prefix.'users.ID AND ^posts.type=$ ORDER BY ^posts.created DESC LIMIT #',60, $type, $limit);
		}else
			$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT ^posts.* , ^users.* FROM ^posts, ^users WHERE ^posts.userid=^users.userid AND ^posts.type=$ ORDER BY ^posts.created DESC LIMIT #', $type, $limit));
        
        $output = '<ul class="posts-list">';
        foreach($posts as $p) {

            if ($type == 'Q') {
                $what = qa_lang_html('cleanstrap/asked');
            } elseif ($type == 'A') {
                $what = qa_lang_html('cleanstrap/answered');
            } elseif ('C') {
                $what = qa_lang_html('cleanstrap/commented');
            }
			if(defined('QA_WORDPRESS_INTEGRATE_PATH'))
				$handle = $p['display_name'];
			else
				$handle = $p['handle'];
			
			$timeCode = qa_when_to_html(  strtotime( $p['created'] ) ,7);
			$when = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];

            $output .= '<li>';
            $output .= qw_get_post_avatar($p, $size, true);
            $output .= '<div class="post-content">';
            
			$output .= '<div class="meta">';
				$output .= '<span><a href="' . qa_path_html('user/' . $handle) . '">' . $handle . '</a> ' . $what . '</span>';
				
				if ($type == 'Q')
					$output .= '<span>' . qa_lang_sub('cleanstrap/x_answers', $p['acount']) . '</span>';
				
				$output .= '<span class="time icon-time">' .  $when . '</span>';
				
				if ($type != 'Q')
					$output .= '<span class="vote-count icon-thumbs-up">' . qa_lang_sub('cleanstrap/x_votes', $p['netvotes']) . '</span>';			
			$output .= '</div>';
			
            if ($type == 'Q') {
                $output .= '<a class="title question" href="' . qa_q_path_html($p['postid'], $p['title']) . '" title="' . $p['title'] . '">' . qa_html($p['title']) . '</a>';
				
				if(!!$show_content)
					$output .= '<p class="content question">' . qw_truncate(strip_tags($p['content']), $content_limt) . '</p>';
            } elseif ($type == 'A') {
                $output .= '<a class="title" href="' . qw_post_link($p['parentid']) . '#a' . $p['postid'] . '">' . qw_truncate(strip_tags($p['content']), $content_limt) . '</a>';
            } else {
                $output .= '<a class="title" href="' . qw_post_link($p['parentid']) . '#c' . $p['postid'] . '">' . qw_truncate(strip_tags($p['content']), $content_limt) . '</a>';
            }
            
            $output .= '</div>';
            $output .= '</li>';
        }
        $output .= '</ul>';
        if ($return)
            return $output;
        echo $output;
    }
    function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
        $widget_opt = @$themeobject->current_widget['param']['options'];
		
		
		if ($widget_opt['post_type'] == 'Q') {
			$what = qa_lang_html('cleanstrap/questions');
		} elseif ($widget_opt['post_type'] == 'A') {
			$what = qa_lang_html('cleanstrap/answers');
		} elseif ($widget_opt['post_type'] == 'C') {
			$what = qa_lang_html('cleanstrap/comments');
		}
        if (@$themeobject->current_widget['param']['locations']['show_title'])
            $themeobject->output('<h3 class="widget-title">' . qa_lang_sub('cleanstrap/recent_posts', $what) . '</h3>');
        
        $themeobject->output('<div class="ra-post-list-widget widget-'.@$widget_opt['class'].'">');
        $themeobject->output($this->qw_post_list($widget_opt['post_type'], (int)$widget_opt['qw_qa_count'], (int)$widget_opt['avatar_size'], (int)$widget_opt['show_content'], (int)$widget_opt['content_size']));
        $themeobject->output('</div>');
    }
}
/*
Omit PHP closing tag to help avoid accidental output
*/