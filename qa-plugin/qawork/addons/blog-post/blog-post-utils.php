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
			qa_update_counts_for_q($postid);
			qa_db_points_update_ifuser($userid, 'qposts');
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

	function qw_blog_request($questionid, $title)
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
			
			return (int)$questionid.'/blog/'.$title;
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