<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../');
	exit;
}
class qw_blogs {

    var $directory;
    var $urltoroot;
    var $page_url = 'blogs';

    function load_module($directory, $urltoroot) {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    function match_request($request) {
        if ($request == $this->page_url) return true;

        return false;
    }

    function process_request($request) {
    		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
    		require_once QA_INCLUDE_DIR.'qa-app-format.php';
    		require_once QA_INCLUDE_DIR.'qa-app-q-list.php';

    		$categoryslugs=qa_request_parts(1);
    		$countslugs=count($categoryslugs);
    		
    		$sort=($countslugs && !QA_ALLOW_UNINDEXED_QUERIES) ? null : qa_get('sort');
    		$start=qa_get_start();
    		$userid=qa_get_logged_in_userid();


    	//	Get list of questions, plus category information

    		switch ($sort) {
    			case 'hot':
    				$selectsort='hotness';
    				break;
    			
    			case 'votes':
    				$selectsort='netvotes';
    				break;
    				
    			case 'answers':
    				$selectsort='acount';
    				break;
    				
    			case 'views':
    				$selectsort='views';
    				break;
    			
    			default:
    				$selectsort='created';
    				break;
    		}
    		
    		list($questions, $categories, $categoryid)=qa_db_select_with_pending(
    			qw_db_blog_selectspec($userid, $selectsort, $start, $categoryslugs, null, false , false, qa_opt_if_loaded('page_size_qs')),
    			qa_db_category_nav_selectspec($categoryslugs, false, false, true),
    			$countslugs ? qa_db_slugs_to_category_id_selectspec($categoryslugs) : null
    		);
    		if ($countslugs) {
    			if (!isset($categoryid))
    				return include QA_INCLUDE_DIR.'qa-page-not-found.php';
    		
    			$categorytitlehtml=qa_html($categories[$categoryid]['title']);
    			$nonetitle=qa_lang_html_sub('main/no_questions_in_x', $categorytitlehtml);

    		} else
    			$nonetitle=qa_lang_html('main/no_questions_found');
    		

    		$categorypathprefix=QA_ALLOW_UNINDEXED_QUERIES ? 'blogs/' : null; // this default is applied if sorted not by recent
    		$feedpathprefix=null;
    		$linkparams=array('sort' => $sort);
    		
    		switch ($sort) {
    			case 'hot':
    				$sometitle=$countslugs ? qa_lang_html_sub('qw_blog_post/hot_ps_in_x', $categorytitlehtml) : qa_lang_html('qw_blog_post/hot_ps_title');
    				$feedpathprefix=qa_opt('feed_for_hot') ? 'hot' : null;
    				break;
    				
    			case 'votes':
    				$sometitle=$countslugs ? qa_lang_html_sub('qw_blog_post/voted_ps_in_x', $categorytitlehtml) : qa_lang_html('qw_blog_post/voted_ps_title');
    				break;
    				
    			case 'answers':
    				$sometitle=$countslugs ? qa_lang_html_sub('qw_blog_post/commented_ps_in_x', $categorytitlehtml) : qa_lang_html('qw_blog_post/commented_ps_title');
    				break;
    			
    			case 'views':
    				$sometitle=$countslugs ? qa_lang_html_sub('qw_blog_post/viewed_ps_in_x', $categorytitlehtml) : qa_lang_html('qw_blog_post/viewed_ps_title');
    				break;
    			
    			default:
    				$linkparams=array();
    				$sometitle=$countslugs ? qa_lang_html_sub('qw_blog_post/recent_ps_in_x', $categorytitlehtml) : qa_lang_html('qw_blog_post/recent_ps_title');
    				$categorypathprefix='blogs/';
    				$feedpathprefix=qa_opt('feed_for_questions') ? 'blogs' : null;
    				break;
    		}

    		
    	//	Prepare and return content for theme

    		$qa_content=qw_blog_list_page_content(
    			$questions, // questions
    			qa_opt('page_size_qs'), // questions per page
    			$start, // start offset
    			$countslugs ? $categories[$categoryid]['pcount'] : qa_opt('cache_pcount'), // total count
    			$sometitle, // title if some questions
    			$nonetitle, // title if no questions
    			$categories, // categories for navigation
    			$categoryid, // selected category id
    			true, // show question counts in category navigation
    			$categorypathprefix, // prefix for links in category navigation
    			$feedpathprefix, // prefix for RSS feed paths
    			$countslugs ? qa_html_suggest_qs_tags(qa_using_tags()) : qa_html_suggest_ask($categoryid), // suggest what to do next
    			$linkparams, // extra parameters for page links
    			$linkparams // category nav params
    		);
    		
    		if (QA_ALLOW_UNINDEXED_QUERIES || !$countslugs)
    			$qa_content['navigation']['sub']=qw_blogs_sub_navigation($sort, $categoryslugs);

    		
    		return $qa_content;
    }
}
	


/*
	Omit PHP closing tag to help avoid accidental output
*/