<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../');
    exit;
}

class qw_blog_post_new {

    var $directory;
    var $urltoroot;
    var $page_url = 'blog/new';

    function load_module($directory, $urltoroot) {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    function match_request($request) {
        if ($request == $this->page_url) return true;

        return false;
    }

    function process_request($request) {

        require_once QA_INCLUDE_DIR . 'qa-app-format.php';
        require_once QA_INCLUDE_DIR . 'qa-app-limits.php';
        require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
        require_once QA_INCLUDE_DIR . 'qa-util-sort.php';


        //	Check whether this is a follow-on question and get some info we need from the database

        $in = array();

        $followpostid = qa_get('follow');
        $in['categoryid'] = qa_clicked('do_blog_post') ? qa_get_category_field_value('category') : qa_get('cat');
        $userid = qa_get_logged_in_userid();

        list($categories, $completetags) = qa_db_select_with_pending(
                qa_db_category_nav_selectspec($in['categoryid'], true), qa_db_popular_tags_selectspec(0, QA_DB_RETRIEVE_COMPLETE_TAGS)
        );

        if (!isset($categories[$in['categoryid']])) $in['categoryid'] = null;

        //	Check for permission error

        $permiterror = qa_user_maximum_permit_error('permit_post_q', QA_LIMIT_QUESTIONS);

        if ($permiterror) {
            $qa_content = qa_content_prepare();

            // The 'approve', 'login', 'confirm', 'limit', 'userblock', 'ipblock' permission errors are reported to the user here
            // The other option ('level') prevents the menu option being shown, in qa_content_prepare(...)

            switch ($permiterror) {
                case 'login':
                    $qa_content['error'] = qa_insert_login_links(qa_lang_html('qw_blog_post/blogger_must_login'), qa_request(), isset($followpostid) ? array('follow' => $followpostid) : null);
                    break;

                case 'confirm':
                    $qa_content['error'] = qa_insert_login_links(qa_lang_html('qw_blog_post/blogger_must_confirm'), qa_request(), isset($followpostid) ? array('follow' => $followpostid) : null);
                    break;

                case 'limit':
                    $qa_content['error'] = qa_lang_html('qw_blog_post/blogger_limit');
                    break;

                case 'approve':
                    $qa_content['error'] = qa_lang_html('qw_blog_post/blogger_must_be_approved');
                    break;

                default:
                    $qa_content['error'] = qa_lang_html('users/no_permission');
                    break;
            }

            return $qa_content;
        }


        //	Process input

        $captchareason = qa_user_captcha_reason();

        $in['title'] = qa_post_text('title'); // allow title and tags to be posted by an external form
        $in['extra'] = qa_opt('extra_field_active') ? qa_post_text('extra') : null;
        if (qa_using_tags()) $in['tags'] = qa_get_tags_field_value('tags');

        if (qa_clicked('do_publish_post') || qa_clicked('do_save_post')) {
            require_once QA_INCLUDE_DIR . 'qa-app-post-create.php';
            require_once QA_INCLUDE_DIR . 'qa-util-string.php';

            $categoryids = array_keys(qa_category_path($categories, @$in['categoryid']));
            $userlevel = qa_user_level_for_categories($categoryids);

            $in['name'] = qa_post_text('name');
            $in['notify'] = qa_post_text('notify') ? true : false;
            $in['email'] = qa_post_text('email');
            $in['queued'] = qa_user_moderation_reason($userlevel) ? true : false;

            qa_get_post_content('editor', 'content', $in['editor'], $in['content'], $in['format'], $in['text']);

            $errors = array();

            if (!qa_check_form_security_code('publish_new', qa_post_text('code'))) $errors['page'] = qa_lang_html('misc/form_security_again');

            else {
                $filtermodules = qa_load_modules_with('filter', 'filter_question');
                foreach ($filtermodules as $filtermodule) {
                    $oldin = $in;
                    $filtermodule->filter_question($in, $errors, null);
                    qa_update_post_text($in, $oldin);
                }

                if (qa_using_categories() && count($categories) && (!qa_opt('allow_no_category')) && !isset($in['categoryid'])) $errors['categoryid'] = qa_lang_html('qw_blog_post/category_required'); // check this here because we need to know count($categories)
                elseif (qa_user_permit_error('permit_post_q', null, $userlevel)) $errors['categoryid'] = qa_lang_html('qw_blog_post/category_ask_not_allowed');

                if ($captchareason) {
                    require_once 'qa-app-captcha.php';
                    qa_captcha_validate_post($errors);
                }

                if (empty($errors)) {
                    $cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create(); // create a new cookie if necessary
                    $flag = qa_clicked('do_publish_post') ? true : false ; /*true indicates publish and false indicates to save */
                    $questionid = qw_blog_create($userid, qa_get_logged_in_handle(), $cookieid, $in['title'], $in['content'], $in['format'], $in['text'], isset($in['tags']) ? qa_tags_to_tagstring($in['tags']) : '', $in['notify'], $in['email'], $in['categoryid'], $in['extra'], $in['queued'], $in['name'],$flag);

                    qa_redirect(qw_blog_request($questionid, $in['title'])); // our work is done here
                }
            }
        }

        //	Prepare content for theme

        $qa_content = qa_content_prepare(false, array_keys(qa_category_path($categories, @$in['categoryid'])));

        $qa_content['title'] = qa_lang_html('qw_blog_post/new_blog_title');
        $qa_content['error'] = @$errors['page'];

        $editorname = isset($in['editor']) ? $in['editor'] : qa_opt('editor_for_qs');
        $editor = qa_load_editor(@$in['content'], @$in['format'], $editorname);

        $field = qa_editor_load_field($editor, $qa_content, @$in['content'], @$in['format'], 'content', 12, false);
        $field['label'] = qa_lang_html('qw_blog_post/blog_content_label');
        $field['error'] = qa_html(@$errors['content']);

        $qa_content['form'] = array(
            'tags' => 'name="ask" method="post" action="' . qa_self_html() . '"',
            'style' => 'tall',
            'fields' => array(
                'title' => array(
                    'label' => qa_lang_html('qw_blog_post/new_blog_post_title'),
                    'tags' => 'name="title" id="title" autocomplete="off"',
                    'value' => qa_html(@$in['title']),
                    'error' => qa_html(@$errors['title']),
                ),
                'similar' => array(
                    'type' => 'custom',
                    'html' => '<span id="similar"></span>',
                ),
                'content' => $field,
            ),
            'buttons' => array(
                'publish' => array(
                    'tags' => 'name="do_publish_post" onclick="qa_show_waiting_after(this, false); ' .
                    (method_exists($editor, 'update_script') ? $editor->update_script('content') : '') . '"',
                    'type' => "button",
                    'label' => qa_lang_html('qw_blog_post/publish_article_button'),
                ),
                'save_article' => array(
                    'tags' => 'name="do_save_post" onclick="qa_show_waiting_after(this, false);"' ,
                    'type' => "button",
                    'label' => qa_lang_html('qw_blog_post/save_article_button'),
                ),

            ),
            'hidden' => array(
                'editor' => qa_html($editorname),
                'code' => qa_get_form_security_code('publish_new'),
                'do_publish_new' => '1',
            ),
        );

        if (qa_opt('do_ask_check_qs') || qa_opt('do_example_tags')) {
            $qa_content['script_rel'][] = 'qa-content/qa-ask.js?' . QA_VERSION;
            $qa_content['form']['fields']['title']['tags'].=' onchange="qa_title_change(this.value);"';

            if (strlen(@$in['title'])) $qa_content['script_onloads'][] = 'qa_title_change(' . qa_js($in['title']) . ');';
        }

        if (qa_using_categories() && count($categories)) {
            $field = array(
                'label' => qa_lang_html('qw_blog_post/blog_category_label'),
                'error' => qa_html(@$errors['categoryid']),
            );

            qa_set_up_category_field($qa_content, $field, 'category', $categories, $in['categoryid'], true, qa_opt('allow_no_sub_category'));

            if (!qa_opt('allow_no_category')) // don't auto-select a category even though one is required
                $field['options'][''] = '';

            qa_array_insert($qa_content['form']['fields'], 'content', array('category' => $field));
        }

        if (qa_using_tags()) {
            $field = array(
                'error' => qa_html(@$errors['tags']),
            );

            qa_set_up_tag_field($qa_content, $field, 'tags', isset($in['tags']) ? $in['tags'] : array(), array(), qa_opt('do_complete_tags') ? array_keys($completetags) : array(), qa_opt('page_size_ask_tags'));

            qa_array_insert($qa_content['form']['fields'], null, array('tags' => $field));
        }

        if (!isset($userid)) qa_set_up_name_field($qa_content, $qa_content['form']['fields'], @$in['name']);

        qa_set_up_notify_fields($qa_content, $qa_content['form']['fields'], 'Q', qa_get_logged_in_email(), isset($in['notify']) ? $in['notify'] : qa_opt('notify_users_default'), @$in['email'], @$errors['email']);

        if ($captchareason) {
            require_once 'qa-app-captcha.php';
            qa_set_up_captcha_field($qa_content, $qa_content['form']['fields'], @$errors, qa_captcha_reason_note($captchareason));
        }

        $qa_content['focusid'] = 'title';


        return $qa_content;
    }

}

/*
	Omit PHP closing tag to help avoid accidental output
*/