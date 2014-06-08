<?php
class qw_user_posts_widget
{
    
    function qw_widget_form()
    {
        
        return array(
            'style' => 'wide',
            'fields' => array(
				'title' => array(
                    'label' 	=> 'Title',
                    'type' 		=> 'text',
                    'tags' 		=> 'name="title" class="form-control"',
                    'value' 	=> 'QW Post list widget'
                ),
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
				'post_status' => array(
                    'label' 	=> 'Post Type',
                    'type' 		=> 'select',
                    'tags' 		=> 'name="post_status" class="form-control"',
                    'options' 	=> array('solved' => 'Solved','open' => 'Open','closed' => 'Closed')
                ),
				'order_by' => array(
                    'label' 	=> 'Order by',
                    'type' 		=> 'select',
                    'tags' 		=> 'name="order_by" class="form-control"',
                    'options' 	=> array('created' => 'Created','views' => 'Views','answers' => 'Answers', 'votes' => 'Votes', 'hotness' => 'Hotness')
                ),
				'order' => array(
                    'label' 	=> 'Order',
                    'type' 		=> 'select',
                    'tags' 		=> 'name="order" class="form-control"',
                    'options' 	=> array('ASC' => 'ASC','DESC' => 'DESC')
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
    function qw_post_list($handle, $type, $limit, $show_content, $content_limt = 80, $post_status = false, $order_by = 'created', $order = 'DESC', $return = false)
    {
		
		$status = '';
		if(!!$post_status && $type =='Q'){
			if($post_status == 'solved')
				$status = ' AND selchildid is NOT NULL';
			elseif($post_status == 'open')
				$status = ' AND selchildid is NULL AND closedbyid is NULL';
			elseif($post_status == 'closed')
				$status = ' AND closedbyid is NOT NULL';
		}
		
		if(!!$order_by){
			if($order_by == 'created')
				$order_by = ' ^posts.created ';
			elseif($order_by == 'views')
				$order_by = ' ^posts.views ';
			elseif($order_by == 'answers')
				$order_by = ' ^posts.acount ';
			elseif($order_by == 'votes')
				$order_by = ' ^posts.netvotes ';
			elseif($order_by == 'hotness')
				$order_by = ' ^posts.hotness ';
			
		}

		$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT UNIX_TIMESTAMP(^posts.created) as unix_time, ^posts.* , ^users.* FROM ^posts, ^users WHERE ^users.handle = $ AND ^posts.userid=^users.userid AND ^posts.type=$ '.$status.' ORDER BY '.$order_by.' '.$order.' LIMIT #', $handle, $type, $limit));
        
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
			
			$when = qa_when_to_html($p['unix_time'] ,7);
            $output .= '<li>';
            $output .= '<div class="post-content">';
            if ($type == 'Q') {
                $output .= '<a class="title question" href="' . qa_q_path_html($p['postid'], $p['title']) . '" title="' . $p['title'] . '">' . qa_html($p['title']) . '</a>';
				
				if(!!$show_content)
					$output .= '<p class="content question">' . qw_truncate(strip_tags($p['content']), $content_limt) . '</p>';
            } elseif ($type == 'A') {
                $output .= '<a class="title" href="' . qw_post_link($p['parentid']) . '#a' . $p['postid'] . '">' . qw_truncate(strip_tags($p['content']), $content_limt) . '</a>';
            } else {
                $output .= '<a class="title" href="' . qw_post_link($p['parentid']) . '#c' . $p['postid'] . '">' . qw_truncate(strip_tags($p['content']), $content_limt) . '</a>';
            }
			$output .= '<div class="meta">';
				if ($type == 'Q'){
					$s['raw']['selchildid'] = $p['selchildid'];
					$s['raw']['closedbyid'] = $p['closedbyid'];
					$output .=qw_post_status($s);
				}
				$output .= '<span><a href="' . qa_path_html('user/' . $handle) . '">' . $handle . '</a> ' . $what . '</span>';
				
				
				$output .= '<span class="time icon-time">' .  implode(' ', $when) . '</span>';
				
				if ($type == 'Q')
					$output .= '<span>' . qa_lang_sub('cleanstrap/x_answers', $p['acount']) . '</span>';
				
									
				$output .= '<span class="vote-count icon-thumbs-up">' . qa_lang_sub('cleanstrap/x_votes', $p['netvotes']) . '</span>';	
				
			$output .= '</div>';
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
			
		if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
			$userid = $qa_content['raw']['userid'];
			$user_info = get_userdata( $userid );
			$handle = $user_info->user_login;
			
		}else
			$handle = $qa_content['raw']['account']['handle'];
				
        if (@$themeobject->current_widget['param']['locations']['show_title'])
            $themeobject->output('<h3 class="widget-title">'.$handle.'\'s ' . $widget_opt['title'] . '</h3>');
        
        $themeobject->output('<div class="ra-post-list-widget widget-'.@$widget_opt['class'].'">');
        $themeobject->output($this->qw_post_list($handle, $widget_opt['post_type'], (int)$widget_opt['qw_qa_count'], (int)$widget_opt['show_content'], (int)$widget_opt['content_size'],$widget_opt['post_status'], $widget_opt['order_by'], $widget_opt['order']));
        $themeobject->output('</div>');
    }
}
/*
Omit PHP closing tag to help avoid accidental output
*/