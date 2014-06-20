<?php
/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
      header('Location: /');
      exit;
}

// for utility functions 

function qw_blog_create($userid, $handle, $cookieid, $title, $content, $format, $text, $tagstring, $notify, $email,
		$categoryid=null, $extravalue=null, $queued=false, $name=null)
/*
	Add a question (application level) - create record, update appropriate counts, index it, send notifications.
	If question is follow-on from an answer, $followanswer should contain answer database record, otherwise null.
	See qa-app-posts.php for a higher-level function which is easier to use.
*/
	{
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';

		$postid=qa_db_post_create($queued ? 'QW_BLOG_QUEUED' : 'QW_BLOG_P', null /*follow answer*/, $userid, isset($userid) ? null : $cookieid,
			qa_remote_ip_address(), $title, $content, $format, $tagstring, qa_combine_notify_email($userid, $notify, $email),
			$categoryid, isset($userid) ? null : $name);
		
		if (isset($extravalue))	{
			require_once QA_INCLUDE_DIR.'qa-db-metas.php';
			qa_db_postmeta_set($postid, 'qa_q_extra', $extravalue);
		}
		
		qa_db_posts_calc_category_path($postid);
		qa_db_hotness_update($postid);
		
		if ($queued) {
			qa_db_queuedcount_update();

		} else {
			qa_post_index($postid, 'Q', $postid, @$followanswer['postid'], $title, $content, $format, $text, $tagstring, $categoryid);
			qw_update_counts_for_p($postid);
			// qa_db_points_update_ifuser($userid, 'qposts');
		}
		
		qa_report_event($queued ? 'q_queue' : 'q_post', $userid, $handle, $cookieid, array(
			'postid' => $postid,
			'parentid' => @$followanswer['postid'],
			'parent' => $followanswer,
			'title' => $title,
			'content' => $content,
			'format' => $format,
			'text' => $text,
			'tags' => $tagstring,
			'categoryid' => $categoryid,
			'extra' => $extravalue,
			'name' => $name,
			'notify' => $notify,
			'email' => $email,
		));
		
		return $postid;
	}

	function qw_blog_request($blogid, $title)
	{
			if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }
			
			require_once QA_INCLUDE_DIR.'qa-app-options.php';
			require_once QA_INCLUDE_DIR.'qa-util-string.php';
		
			$title=qa_block_words_replace($title, qa_get_block_words_preg());
			
			$words=qa_string_to_words($title, true, false, false);

			$wordlength=array();
			foreach ($words as $index => $word)
				$wordlength[$index]=qa_strlen($word);

			$remaining=qa_opt('q_urls_title_length');
			
			if (array_sum($wordlength)>$remaining) {
				arsort($wordlength, SORT_NUMERIC); // sort with longest words first
				
				foreach ($wordlength as $index => $length) {
					if ($remaining>0)
						$remaining-=$length;
					else
						unset($words[$index]);
				}
			}
			
			$title=implode('-', $words);
			if (qa_opt('q_urls_remove_accents'))
				$title=qa_string_remove_accents($title);
			
			return 'blog/'.(int)$blogid.'/'.$title;
		}

		function qw_db_blog_selectspec($voteuserid, $sort, $start, $categoryslugs=null, $createip=null, $specialtype=false, $full=false, $count=null)
/*
	Return the selectspec to retrieve questions (of type $specialtype if provided, or 'Q' by default) sorted by $sort,
	restricted to $createip (if not null) and the category for $categoryslugs (if not null), with the corresponding vote
	made by $voteuserid (if not null) and including $full content or not. Return $count (if null, a default is used)
	questions starting from offset $start.
*/
	{
		

		if (($specialtype=='QW_BLOG_P') || ($specialtype=='QW_BLOG_QUEUED'))
			$type=$specialtype;
		else
			$type=$specialtype ? 'QW_BLOG_H' : 'QW_BLOG_P'; // for backwards compatibility
		
		$count=isset($count) ? min($count, QA_DB_RETRIEVE_QS_AS) : QA_DB_RETRIEVE_QS_AS;
		
		switch ($sort) {
			case 'acount':
			case 'flagcount':
			case 'netvotes':
			case 'views':
				$sortsql='ORDER BY ^posts.'.$sort.' DESC, ^posts.created DESC';
				break;
			
			case 'created':
			case 'hotness':
				$sortsql='ORDER BY ^posts.'.$sort.' DESC';
				break;
				
			default:
				qa_fatal_error('qa_db_qs_selectspec() called with illegal sort value');
				break;
		}
		
		$selectspec=qa_db_posts_basic_selectspec($voteuserid, $full);
		
		$selectspec['source'].=" JOIN (SELECT postid FROM ^posts WHERE ".
			qa_db_categoryslugs_sql_args($categoryslugs, $selectspec['arguments']).
			(isset($createip) ? "createip=INET_ATON($) AND " : "").
			"type=$ ".$sortsql." LIMIT #,#) y ON ^posts.postid=y.postid";

		if (isset($createip))
			$selectspec['arguments'][]=$createip;
		
		array_push($selectspec['arguments'], $type, $start, $count);

		$selectspec['sortdesc']=$sort;
		
		return $selectspec;
	}

	function qw_blog_list_page_content($questions, $pagesize, $start, $count, $sometitle, $nonetitle,
		$navcategories, $categoryid, $categoryqcount, $categorypathprefix, $feedpathprefix, $suggest,
		$pagelinkparams=null, $categoryparams=null, $dummy=null)
/*
	Returns the $qa_content structure for a question list page showing $questions retrieved from the
	database. If $pagesize is not null, it sets the max number of questions to display. If $count is
	not null, pagination is determined by $start and $count. The page title is $sometitle unless
	there are no questions shown, in which case it's $nonetitle. $navcategories should contain the
	categories retrived from the database using qa_db_category_nav_selectspec(...) for $categoryid,
	which is the current category shown. If $categorypathprefix is set, category navigation will be
	shown, with per-category question counts if $categoryqcount is true. The nav links will have the
	prefix $categorypathprefix and possible extra $categoryparams. If $feedpathprefix is set, the
	page has an RSS feed whose URL uses that prefix. If there are no links to other pages, $suggest
	is used to suggest what the user should do. The $pagelinkparams are passed through to
	qa_html_page_links(...) which creates links for page 2, 3, etc..
*/
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }
		
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-updates.php';
	
		$userid=qa_get_logged_in_userid();
		
		
	//	Chop down to size, get user information for display

		if (isset($pagesize))
			$questions=array_slice($questions, 0, $pagesize);
	
		$usershtml=qa_userids_handles_html(qa_any_get_userids_handles($questions));


	//	Prepare content for theme
		
		$qa_content=qa_content_prepare(true, array_keys(qa_category_path($navcategories, $categoryid)));
	
		$qa_content['blog_list']['form']=array(
			'tags' => 'method="post" action="'.qa_self_html().'"',
			
			'hidden' => array(
				'code' => qa_get_form_security_code('vote'),
			),
		);
		
		$qa_content['q_list']['qs']=array();
		
		if (count($questions)) {
			$qa_content['title']=$sometitle;
		
			$defaults=qa_post_html_defaults('Q');
			if (isset($categorypathprefix))
				$defaults['categorypathprefix']=$categorypathprefix;
				
			foreach ($questions as $question){
				$qa_content['q_list']['qs'][]=qa_any_to_q_html_fields($question, $userid, qa_cookie_get(),
					$usershtml, null, qa_post_html_options($question, $defaults));
			}
			// for editing the correct url 
			foreach ($qa_content['q_list']['qs'] as &$question ) {
				$question['url'] =  qa_path(qw_blog_request($question['raw']['postid'], $question['title']), $params, $absolute ? qa_opt('site_url') : null, null, null) ;
			}
		} else
			$qa_content['title']=$nonetitle;
		
		if (isset($userid) && isset($categoryid)) {
			$favoritemap=qa_get_favorite_non_qs_map();
			$categoryisfavorite=@$favoritemap['category'][$navcategories[$categoryid]['backpath']] ? true : false;
			
			$qa_content['favorite']=qa_favorite_form(QA_ENTITY_CATEGORY, $categoryid, $categoryisfavorite,
				qa_lang_sub($categoryisfavorite ? 'main/remove_x_favorites' : 'main/add_category_x_favorites', $navcategories[$categoryid]['title']));
		}
			
		if (isset($count) && isset($pagesize))
			$qa_content['page_links']=qa_html_page_links(qa_request(), $start, $pagesize, $count, qa_opt('pages_prev_next'), $pagelinkparams);
		
		if (empty($qa_content['page_links']))
			$qa_content['suggest_next']=$suggest;
			
		if (qa_using_categories() && count($navcategories) && isset($categorypathprefix))
			$qa_content['navigation']['cat']=qa_category_navigation($navcategories, $categoryid, $categorypathprefix, $categoryqcount, $categoryparams);
		
		if (isset($feedpathprefix) && (qa_opt('feed_per_category') || !isset($categoryid)) )
			$qa_content['feed']=array(
				'url' => qa_path_html(qa_feed_request($feedpathprefix.(isset($categoryid) ? ('/'.qa_category_path_request($navcategories, $categoryid)) : ''))),
				'label' => strip_tags($sometitle),
			);
			
		return $qa_content;
	}

	function qw_blogs_sub_navigation($sort, $categoryslugs)
/*
	Return the sub navigation structure common to question listing pages
*/
	{
		$request='blogs';

		if (isset($categoryslugs))
			foreach ($categoryslugs as $slug)
				$request.='/'.$slug;

		$navigation=array(
			'recent' => array(
				'label' => qa_lang('main/nav_most_recent'),
				'url' => qa_path_html($request),
			),
			
			'hot' => array(
				'label' => qa_lang('main/nav_hot'),
				'url' => qa_path_html($request, array('sort' => 'hot')),
			),
			
			'votes' => array(
				'label' => qa_lang('main/nav_most_votes'),
				'url' => qa_path_html($request, array('sort' => 'votes')),
			),

			'answers' => array(
				'label' => qa_lang('main/nav_most_answers'),
				'url' => qa_path_html($request, array('sort' => 'answers')),
			),

			'views' => array(
				'label' => qa_lang('main/nav_most_views'),
				'url' => qa_path_html($request, array('sort' => 'views')),
			),
		);
		
		if (isset($navigation[$sort]))
			$navigation[$sort]['selected']=true;
		else
			$navigation['recent']['selected']=true;
		
		if (!qa_opt('do_count_q_views'))
			unset($navigation['views']);
		
		return $navigation;
	}

	function qw_update_counts_for_p($postid)
/*
	Perform various common cached count updating operations to reflect changes in the question whose id is $postid
*/
	{
		if (isset($postid)) // post might no longer exist
			qa_db_category_path_qcount_update(qa_db_post_get_category_path($postid));
		qw_db_pcount_update();
	}

	function qw_db_pcount_update()
	{
		if (qa_should_update_counts())
			qa_db_query_sub("REPLACE ^options (title, content) SELECT 'cache_pcount', COUNT(*) FROM ^posts WHERE type='QW_BLOG_P'");
	}

