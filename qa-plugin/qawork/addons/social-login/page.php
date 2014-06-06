<?php

/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}
/*
  Some part of this code was taken from qa-openlogin plugin by alixandru
  https://github.com/alixandru/q2a-open-login
 */

class qw_social_login_page {

    var $directory;
    var $urltoroot;

    function load_module($directory, $urltoroot) {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    function match_request($request) {
        $parts = explode('/', $request);
		 return ((count($parts) == 1 && $parts[0] == 'logins'));
    }

    function process_request($request) {
        require_once QA_INCLUDE_DIR . 'qa-db-users.php';
        require_once QA_INCLUDE_DIR . 'qa-app-format.php';
        require_once QA_INCLUDE_DIR . 'qa-app-users.php';
        require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
        require_once QW_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';

        //	Check we're not using single-sign on integration, that we're logged in

        if (QA_FINAL_EXTERNAL_USERS) qa_fatal_error('User accounts are handled by external code');

        $userid = qa_get_logged_in_userid();
        if (!isset($userid)) {
            qa_redirect('login');
        }

        //	Get current information on user
        $useraccount = qw_db_user_find_by_id($userid);

        //  Check if settings were updated
        $this->check_settings();

        //  Check if we're unlinking an account
        $mylogins = $this->check_unlink($useraccount);

        //  Check if we need to associate another provider
        $tolink = $this->check_associate($useraccount);

        //  Check if we're merging multiple accounts
        $otherlogins = $this->check_merge($useraccount, $mylogins, $tolink);

        //	Prepare content for theme
        $disp_conf = qa_get('confirm') || !empty($tolink);
        $qa_content = qa_content_prepare();

        //  Build page
        if (!$disp_conf) {
            // just visiting the regular page
            $qa_content['title'] = qa_lang_html('qw_social_login/my_logins_title');
            $qa_content['navigation']['sub'] = qa_user_sub_navigation($useraccount['handle'], '', true);
            $qa_content['script_onloads'][] = '$(function(){ window.setTimeout(function() { qa_conceal(".form-notification-ok"); }, 1500); });';

            $this->display_summary($qa_content, $useraccount);
            $this->display_logins($qa_content, $useraccount, $mylogins);
            $this->display_duplicates($qa_content, $useraccount, $otherlogins);
            // get already connected identifiers 
            $already_connected_accounts = array();
            foreach ($mylogins as $i => $login) {
            	$already_connected_accounts[] = strtolower($login['source']) ;
            }

            $this->display_services($qa_content, !empty($mylogins) || !empty($otherlogins) , $already_connected_accounts );
        } else {
            // logged in and there are duplicates
            $qa_content['title'] = qa_lang_html('qw_social_login/other_logins_conf_title');

            if (!$this->display_duplicates($qa_content, $useraccount, $otherlogins)) {
                $tourl = qa_get('to');
                if (!empty($tourl)) {
                    qa_redirect($tourl);
                } else {
                    if ($tolink) {
                        // unable to link the login
                        $provider = ucfirst($tolink['source']);
                        qa_redirect('logins', array('provider' => $provider, 'code' => 99));
                    } else {
                        // no merge to confirm
                        qa_redirect('', array('provider' => '', 'code' => 98));
                    }
                }
            }
        }


        return $qa_content;
    }

    /*     * ** Form processing functions *** */

    function check_settings() {
        if (qa_clicked('dosaveprofile')) {
            qa_opt('open_login_remember', qa_post_text('remember') ? '1' : '0');
            qa_redirect('logins', array('state' => 'profile-saved'));
        }
    }

    function check_merge(&$useraccount, &$mylogins, $tolink) {
        global $qa_cached_logged_in_user, $qa_logged_in_userid_checked;

        $userid = $findid = $useraccount['userid'];
        $findemail = $useraccount['oemail']; // considering this is an openid user, so use the openid email
        if (empty($findemail)) {
            $findemail = $useraccount['email']; // fallback
        }

        if ($tolink) {
            // user is logged in with $userid but wants to merge $findid
            $findemail = null;
            $findid = $tolink['userid'];
        } else if (qa_get('confirm') == 2 || qa_post_text('confirm') == 2) {
            // bogus confirm page, stop right here
            qa_redirect('logins');
        }

        // find other un-linked accounts with the same email
        $otherlogins = qw_db_user_login_find_other($findid, $findemail, $userid);

        if (qa_clicked('domerge') && !empty($otherlogins)) {
            // if cancel was requested, just redirect
            if ($_POST['domerge'] == 0) {
                $tourl = qa_post_text('to');
                if (!empty($tourl)) {
                    qa_redirect($tourl);
                } else {
                    qa_redirect($tolink ? 'logins' : '');
                }
            }

            // a request to merge (link) multiple accounts was made
            require_once QA_INCLUDE_DIR . 'qa-app-users-edit.php';
            $recompute = false;
            $email = null;
            $baseid = $_POST["base{$_POST['domerge']}"]; // POST[base1] or POST[base2]
            // see which account was selected, if any
            if ($baseid != 0) // just in case
                foreach ($otherlogins as $login) {
                    // see if this is the currently logged in account
                    $loginid = $login['details']['userid'];
                    $is_current = $loginid == $userid;

                    // see if this user was selected for merge
                    if (isset($_POST["user_$loginid"]) || $is_current) {
                        if ($baseid != $loginid) {
                            // this account should be deleted as it's different from the selected base id
                            if (!empty($login['logins'])) {
                                // update all associated logins
                                qa_db_user_login_sync(true);
                                qw_db_user_login_replace_userid($loginid, $baseid);
                                qa_db_user_login_sync(false);
                            }

                            // delete old user but keep the email
                            qa_delete_user($loginid);
                            $recompute = true;
                            if (empty($email)) $email = $login['details']['email'];
                            if (empty($email)) $email = $login['details']['oemail'];
                        }
                    }
                }

            // recompute the stats, if needed
            if ($recompute) {
                require_once QA_INCLUDE_DIR . 'qa-db-points.php';
                qa_db_userpointscount_update();

                // check if the current account has been deleted
                if ($userid != $baseid) {
                    $oldsrc = $useraccount['sessionsource'];
                    qa_set_logged_in_user($baseid, $useraccount['handle'], false, $oldsrc);
                    $useraccount = qw_db_user_find_by_id($baseid);
                    $userid = $baseid;

                    // clear some cached data
                    qa_db_flush_pending_result('loggedinuser');
                    $qa_logged_in_userid_checked = false;
                    unset($qa_cached_logged_in_user);
                }

                // also check the email address on the remaining user account
                if (empty($useraccount['email']) && !empty($email)) {
                    // update the account if the email address is not used anymore
                    $emailusers = qa_db_user_find_by_email($email);
                    if (count($emailusers) == 0) {
                        qa_db_user_set($userid, 'email', $email);
                        $useraccount['email'] = $email; // to show on the page
                    }
                }
            }

            $conf = qa_post_text('confirm');
            $tourl = qa_post_text('to');
            if ($conf) {
                $tourl = qa_post_text('to');
                if (!empty($tourl)) {
                    qa_redirect($tourl);
                } else {
                    qa_redirect($tolink ? 'logins' : '');
                }
            }

            // update the arrays
            $otherlogins = qw_db_user_login_find_other($userid, $findemail);
            $mylogins = qw_db_user_login_find_mine($userid);
        }

        // remove the current user id
        unset($otherlogins[$userid]);

        return $otherlogins;
    }

    function check_unlink(&$useraccount) {
        $userid = $useraccount['userid'];

        //	Get more information on user, including accounts already linked 
        $mylogins = qw_db_user_login_find_mine($userid);

        if (qa_clicked('dosplit') && !empty($mylogins)) {
            // a request to split (un-link) some accounts was made
            $unlink = $_POST['dosplit'];
            foreach ($mylogins as $login) {
                // see which account was selected, if any
                $key = "{$login['source']}_" . md5($login['identifier']);
                if ($key == $unlink) {
                    // account found, but don't unlink if currently in use
                    if ($useraccount['sessionsource'] != qw_open_login_get_new_source($login['source'], $login['identifier'])) {
                        // ok, we need to delete this one
                        qa_db_user_login_sync(true);
                        qw_db_user_login_delete($login['source'], $login['identifier'], $userid);
                        qa_db_user_login_sync(false);
                    }
                }
            }

            // update the array
            $mylogins = qw_db_user_login_find_mine($userid);
        }

        return $mylogins;
    }

    function check_associate($useraccount) {
        $userid = $useraccount['userid'];
        $action = null;
        $key = null;

        if (!empty($_REQUEST['hauth_start'])) {
            $key = trim(strip_tags($_REQUEST['hauth_start']));
            $action = 'process';
        } else if (!empty($_REQUEST['hauth_done'])) {
            $key = trim(strip_tags($_REQUEST['hauth_done']));
            $action = 'process';
        } else if (!empty($_GET['link'])) {
            $key = trim(strip_tags($_GET['link']));
            $action = 'login';
        }

        if ($key == null) {
            return false;
        }

        $provider = $this->get_ha_provider($key);
        $source = strtolower($provider);

        if ($action == 'login') {
            // handle the login
            // after login come back to the same page
            $loginCallback = qa_path('', array(), qa_opt('site_url')."/logins/");

            require_once QW_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
            require_once QW_CONTROL_DIR . '/addons/social-login/cs-social-login-utils.php';

            // prepare the configuration of HybridAuth
            $config = $this->get_ha_config($provider, $loginCallback);

            try {
                // try to login
                $hybridauth = new Hybrid_Auth($config);
                $adapter = $hybridauth->authenticate($provider);

                // if ok, create/refresh the user account
                $user = $adapter->getUserProfile();

                $duplicates = 0;
                if (!empty($user))
                // prepare some data
                    $ohandle = null;
                $oemail = null;

                if (empty($user->displayName)) {
                    $ohandle = $provider;
                } else {
                    $ohandle = preg_replace('/[\\@\\+\\/]/', ' ', $user->displayName);
                }
                if (strlen(@$user->email) && $user->emailVerified) { // only if email is confirmed
                    $oemail = $user->email;
                }

                $duplicate = qw_db_user_login_find_duplicate($source, $user->identifier);
                if ($duplicate == null) {
                    // simply create a new login
                    qa_db_user_login_sync(true);
                    qa_db_user_login_add($userid, $source, $user->identifier);
                    if ($oemail) qw_db_user_login_set($source, $user->identifier, 'oemail', $oemail);
                    qw_db_user_login_set($source, $user->identifier, 'ohandle', $ohandle);
                    qa_db_user_login_sync(false);

                    // now that everything was added, log out to allow for multiple accounts
                    $adapter->logout();

                    // redirect to get rid of parameters
                    qa_redirect('logins');
                } else if ($duplicate['userid'] == $userid) {
                    // trying to add the same account, just update the email/handle
                    qa_db_user_login_sync(true);
                    if ($oemail) qw_db_user_login_set($source, $user->identifier, 'oemail', $oemail);
                    qw_db_user_login_set($source, $user->identifier, 'ohandle', $ohandle);
                    qa_db_user_login_sync(false);

                    // log out to allow for multiple accounts
                    $adapter->logout();

                    // redirect to get rid of parameters
                    qa_redirect('logins');
                } else {
                    if (qa_get('confirm') == 2) {

                        return $duplicate;
                    } else {
                        if (!!$this->provider && strlen(@$hybridauth->getSessionData())) {
                            qw_save_user_hauth_session( qa_get_logged_in_userid() , $this->provider  , @$hybridauth->getSessionData() );
                        }
                        qa_redirect('logins', array('link' => qa_get('link'), 'confirm' => 2));
                    }
                }
            } catch (Exception $e) {
            	qw_log(print_r($e , true ));
                qa_redirect('logins', array('provider' => $provider, 'code' => $e->getCode()));
            }
        }

        if ($action == 'process') {
            require_once QW_CONTROL_DIR . '/inc/hybridauth/Hybrid/Auth.php';
            require_once QW_CONTROL_DIR . '/inc/hybridauth/Hybrid/Endpoint.php';
            Hybrid_Endpoint::process();
        }

        return false;
    }

    /*     * ** Display functions *** */

    function display_summary(&$qa_content, $useraccount) {
        // display some summary about the user
        $qa_content['form_profile'] = array(
            'title' => qa_lang_html('qw_social_login/my_current_user'),
            'tags' => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="' . qa_self_html() . '" CLASS="open-login-profile"',
            'style' => 'wide',
            'fields' => array(
                'handle' => array(
                    'label' => qa_lang_html('users/handle_label'),
                    'value' => qa_html($useraccount['handle']),
                    'type' => 'static',
                ),
                'email' => array(
                    'label' => qa_lang_html('users/email_label'),
                    'value' => qa_html($useraccount['email']),
                    'type' => 'static',
                ),
                'remember' => array(
                    'type' => 'checkbox',
                    'label' => qa_lang_html('users/remember_label'),
                    'note' => qa_lang_html('qw_social_login/remember_me'),
                    'tags' => 'NAME="remember"',
                    'value' => qa_opt('open_login_remember') ? true : false,
                ),
            ),
            'buttons' => array(
                'save' => array(
                    'tags' => 'onClick="qa_show_waiting_after(this, false);"',
                    'label' => qa_lang_html('users/save_profile'),
                ),
            ),
            'hidden' => array(
                'dosaveprofile' => '1'
            ),
        );

        if (qa_get_state() == 'profile-saved') {
            $qa_content['form_profile']['ok'] = qa_lang_html('users/profile_saved');
        }
    }

    function display_logins(&$qa_content, $useraccount, $mylogins) {
        if (!empty($mylogins)) {
            require_once QW_CONTROL_DIR . '/addons/social-login/cs-social-login-module.php';

            // display the logins already linked to this user account
            $qa_content['custom_mylogins'] = '<h2>' . qa_lang_html('qw_social_login/associated_logins') . '</h2><p>' . qa_lang_html('qw_social_login/split_accounts_note') . '</p>';
            $qa_content['form_mylogins'] = array(
                'tags' => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="' . qa_self_html() . '" CLASS="open-login-accounts"',
                'style' => 'wide',
                'hidden' => array(
                    'dosplit' => '1',
                ),
            );

            $data = array();
            foreach ($mylogins as $i => $login) {
                $del_html = '';

                $s = qw_open_login_get_new_source($login['source'], $login['identifier']);
                if ($useraccount['sessionsource'] != $s) {
                    $del_html = '<a class="btn btn-sm icon-trash" href="javascript://" onclick="OP_unlink(\'' . $login['source'] . '_' . md5($login['identifier']) . '\')"  title="' . qa_lang_html('qw_social_login/unlink_this_account') . '"> '.qa_lang_html('qw_social_login/unlink_this_account').' </a>';
                }

                $data["f$i"] = array(
                    'label' => qw_open_login::qw_social_print_code(ucfirst($login['source']), empty($login['ohandle']) ? ucfirst($login['source']) : $login['ohandle'], 'menu', 'view', false) . $del_html,
                    'type' => 'static',
                    'style' => 'tall'
                );
            }
            $qa_content['form_mylogins']['fields'] = $data;
            $qa_content['customscriptu'] = '<script type="text/javascript">
				function OP_unlink(id) {
					$(".qa-main form.open-login-accounts>input[name=dosplit]").attr("value", id);
					$(".qa-main form.open-login-accounts").submit();
				}
			</script>';
        }
    }

    function display_duplicates(&$qa_content, $useraccount, $otherlogins) {
        $userid = $useraccount['userid'];
        $disp_conf = qa_get('confirm');

        if (!empty($otherlogins)) {
            // display other logins which could be linked to this user account
            if ($disp_conf) {
                $title = '';
                $p = '<br />' . ($disp_conf == 1 ? qa_lang_html('qw_social_login/other_logins_conf_text') : qa_lang_html_sub('qw_social_login/link_exists', '<strong>' . ucfirst(qa_get('link')) . '</strong>') );
            } else {
                $title = '<h2>' . qa_lang_html('qw_social_login/other_logins') . '</h2>';
                $p = qa_lang_html('qw_social_login/other_logins_conf_text');
            }

            $qa_content['custom_merge'] = "$title <p>$p</p>";
            $qa_content['form_merge'] = array(
                'tags' => 'ENCTYPE="multipart/form-data" METHOD="POST" ACTION="' . qa_self_html() . '" CLASS="open-login-others"',
                'style' => 'wide',
                'buttons' => array(
                    'save' => array(
                        'tags' => 'onClick="qa_show_waiting_after(this, false);" style="display: none"',
                        'note' => qa_lang_html('qw_social_login/action_info_1'),
                        'label' => qa_lang_html('qw_social_login/continue'),
                    ),
                ),
                'hidden' => array(
                    'domerge' => '1',
                    'confirm' => $disp_conf,
                    'to' => qa_get('to'),
                ),
            );

            $data = array();
            $select = '<option value="0">' . qa_lang_html('qw_social_login/select_base') . '</option>' .
                      '<option value="' . $userid . '" title="' . qa_html($useraccount['handle']) . '">' . qa_html($useraccount['handle']) . ' (' . $useraccount['points'] . ' ' . qa_lang_html('admin/points') . ' - ' . qa_lang_html('qw_social_login/current_account') . ')</option>';
            foreach ($otherlogins as $i => $login) {
                $type = 'login';
                $name = qa_html($login['details']['handle']);
                $points = $login['details']['points'];

                if (count($login['logins']) == 0) { // this is a regular site login, not an openid login
                    $type = 'user';
                }
                $login_providers = ($type == 'user' ? strtolower(qa_lang_html('qw_social_login/password')) : '<strong>' . implode(', ', $login['logins']) . '</strong>' );

                $data["f$i"] = array(
                    'label' => '<strong>' . $name . '</strong> (' . $points . ' ' . qa_lang_html('admin/points') . ', ' .
                    strtolower(qa_lang_html_sub('qw_social_login/login_using', '')) . $login_providers . ')',
                    'tags' => 'name="user_' . $login['details']['userid'] . '" value="' . $login['details']['userid'] . '" style="visibility: hidden" checked="checked" rel="' . $i . '" onchange="OP_checkClicked(this)"',
                    'type' => 'checkbox',
                    'style' => 'tall'
                );
                $select .= '<option value="' . $login['details']['userid'] . '" title="' . $name . '">' . $name . ' (' . $points . ' ' . qa_lang_html('admin/points') . ')</option>';
            }
            $data['space'] = array(
                'label' => '<br>' . qa_lang_html('qw_social_login/choose_action'),
                'type' => 'static',
                'style' => 'tall'
            );

            $ac1html = '<div class="opacxhtml qa-form-tall-buttons" id="ac1html" style="display: none;">' .
                    qa_lang('qw_social_login/merge_all_first') . ' <br/> ' . qa_lang_html('qw_social_login/merge_note') . '<br /><br />' .
                    qa_lang_html('qw_social_login/select_base_note') . '<br /><select name="base1" onchange="OP_baseSelected(this)">' . $select . '</select></div>';
            $ac2html = '<div class="opacxhtml qa-form-tall-buttons" id="ac2html" style="display: none;">' .
                    qa_lang('qw_social_login/select_merge_first') . ' <br/> ' . qa_lang_html('qw_social_login/merge_note') . '<br /><br />' .
                    qa_lang_html('qw_social_login/select_base_note') . '<br /><select name="base2" onchange="OP_baseSelected(this)">' . $select . '</select></div>';
            $ac3html = '<div class="opacxhtml qa-form-tall-buttons" id="ac3html" style="display: none;">' . qa_lang_html('qw_social_login/cancel_merge_note') . '</div>';

            if ($disp_conf == null || $disp_conf == 1) {
                $data['actions1'] = array(
                    'label' => '<div class="page-header"><h4><a href="javascript:;" onclick="OP_actionSelected(1, \'#ac1html\' , this )" class="icon-plus"> ' . qa_lang_html('qw_social_login/merge_all') . '</a></h4></div>' . $ac1html,
                    'type' => 'static',
                    'style' => 'tall'
                );
                $data['actions2'] = array(
                    'label' => '<div class="page-header"><h4><a href="javascript:;" onclick="OP_actionSelected(2, \'#ac2html\' , this )" class="icon-plus"> ' . qa_lang_html('qw_social_login/select_merge') . '</a></h4></div>' . $ac2html,
                    'type' => 'static',
                    'style' => 'tall'
                );
                if ($disp_conf == 1) {
                    $data['actions3'] = array(
                        'label' => '<div class="page-header"><h4><a href="javascript:;" onclick="OP_actionSelected(0, \'#ac3html\' , this )" class="icon-trash"> ' . qa_lang_html('qw_social_login/cancel_merge') . '</a></h4></div>' . $ac3html,
                        'type' => 'static',
                        'style' => 'tall'
                    );
                }
            } else if ($disp_conf == 2) {
                $data['actions1'] = array(
                    'label' => '<div class="page-header"><h4><a href="javascript:;" onclick="OP_actionSelected(1, \'#ac1html\' , this )" class="icon-plus"> ' . qa_lang_html('qw_social_login/link_all') . '</a></h4></div>' . $ac1html,
                    'type' => 'static',
                    'style' => 'tall'
                );
                $data['actions3'] = array(
                    'label' => '<div class="page-header"><h4><a href="javascript:;" onclick="OP_actionSelected(0, \'#ac3html\' , this )" class="icon-plus"> ' . qa_lang_html('qw_social_login/cancel_link') . '</a></h4></div>' . $ac3html,
                    'type' => 'static',
                    'style' => 'tall'
                );
            }
            $qa_content['form_merge']['fields'] = $data;
            $qa_content['customscript'] = '<script type="text/javascript">
				function OP_actionSelected(i, divid , link ) {
					$(".opacxhtml").slideUp();
                    $link = $(link);
                    $(".opacxhtml").prev("div.page-header").children("h4").children("a.icon-minus").not($link).removeClass("icon-minus").addClass("icon-plus");

                    $link.toggleClass("icon-plus icon-minus");
					if(i != 2 || op_last_action == 2) {
						$(".qa-main form.open-login-others input[type=checkbox]").css("visibility", "hidden");
					}
					
					if(op_last_action == i) {
						OP_baseSelected();
						op_last_action = -1;
						return;
					}
					op_last_action = i;
					$(divid).slideDown();
					$(".qa-main form.open-login-others>input[name=domerge]").attr("value", i);
					
					if(i > 0) {
						$(".qa-main form.open-login-others input[type=checkbox]").attr("checked", "checked");
						if(i == 2) {
							$(".qa-main form.open-login-others input[type=checkbox]").css("visibility", "visible");
							$(".qa-main form.open-login-others select[name=base2]").html( $(".qa-main form.open-login-others select[name=base1]").html() );
						}
						sel = $(".qa-main form.open-login-others select[name=base" + i +"]").get(0);
						sel.selectedIndex = 0;
						OP_baseSelected(sel);
					} else {
						$(".qa-main form.open-login-others span.qa-form-wide-note").html("' . qa_lang_html('qw_social_login/action_info_2') . '"); 
						$(".qa-main form.open-login-others input[type=submit]").show(); 
						$(".qa-main form.open-login-others input[type=submit]").attr("disabled", false);
					}
				}
				
				function OP_baseSelected(sel) {
					if(!sel || sel.selectedIndex == 0) {
						if(sel) {
							$(".qa-main form.open-login-others span.qa-form-wide-note").html("' . qa_lang_html('qw_social_login/action_info_3') . '")
						} else {
							$(".qa-main form.open-login-others span.qa-form-wide-note").html("' . qa_lang_html('qw_social_login/action_info_1') . '")
						}
						$(".qa-main form.open-login-others input[type=submit]").hide()
						$(".qa-main form.open-login-others input[type=submit]").attr("disabled", "disabled")
					} else {
						if(OP_accValid()) {
							nam = $("option:selected", sel).attr("title")
							$(".qa-main form.open-login-others span.qa-form-wide-note").html("<strong>" + nam + "</strong> ' . qa_lang_html('qw_social_login/action_info_4') . '")
							$(".qa-main form.open-login-others input[type=submit]").show()
							$(".qa-main form.open-login-others input[type=submit]").attr("disabled", false)
						}
					}
				}
				
				function OP_checkClicked(check) {
					' . ($disp_conf == 2 ? '/* empty */' : '
					var rel = $(check).attr("rel")
					var id = $(check).attr("value")
					var chk = $(check).attr("checked")
					$(".qa-main form.open-login-others select[name=base2]").get(0).selectedIndex = 0
					if(chk) {
						$(".qa-main form.open-login-others select[name=base2] option[value=" + id + "]").show()
					} else {
						$(".qa-main form.open-login-others select[name=base2] option[value=" + id + "]").hide()
					}
					OP_accValid() ') . '
				}
				
				function OP_accValid() {
					if(op_last_action != 2) {
						$(".qa-main form.open-login-others input[type=checkbox]").attr("checked", "checked")
						return true
					}
					
					someSel = false 
					$(".qa-main form.open-login-others input[type=checkbox]").each(function(i, o) { 
						someSel = someSel || $(o).attr("checked") == "checked"
					});
					
					$(".qa-main form.open-login-others input[type=submit]").hide();
					$(".qa-main form.open-login-others input[type=submit]").attr("disabled", "disabled");
					if(!someSel) { // nothing selected
						$(".qa-main form.open-login-others span.qa-form-wide-note").html("' . qa_lang_html('qw_social_login/action_info_5') . '");
						return false;
					} else {
						$(".qa-main form.open-login-others span.qa-form-wide-note").html("' . qa_lang_html('qw_social_login/action_info_3') . '");
						return true;
					}
				}
			</script>';
            $qa_content['script_onloads'][] = '$(function(){ OP_baseSelected() });';
            $qa_content['script_var']['op_last_action'] = -1;
            return true;
        }
        return false;
    }

    function display_services(&$qa_content, $has_content , $already_connected_accounts) {
        if (!$has_content) {
            // no linked logins
            $qa_content['form_nodata'] = array(
                'title' => '<br>' . qa_lang_html('qw_social_login/no_logins_title'),
                'style' => 'light',
                'fields' => array(
                    'note' => array(
                        'note' => qa_lang_html('qw_social_login/no_logins_text'),
                        'type' => 'static'
                    )
                ),
            );
        } else {
            $qa_content['custom'] = '<h2>' . qa_lang_html('qw_social_login/link_with_account') . '</h2><p>' . qa_lang_html('qw_social_login/no_logins_text') . '</p>';
        }

        // output login providers
        $loginmodules = qa_load_modules_with('login', 'qw_social_print_code');

        foreach ($loginmodules as $module) {
        	if (in_array( strtolower($module->provider), $already_connected_accounts)) {
        		continue ;
        	}
            ob_start();
            qw_open_login::qw_social_print_code($module->provider, null, 'associate', 'link');
            $html = ob_get_clean();

            if (strlen($html)) @$qa_content['custom'].= $html . ' ';
        }
    }

    /*     * ** Utility functions *** */

    function get_ha_config($provider, $url) {
        $key = strtolower($provider);
        return array(
            'base_url' => $url,
            'providers' => array(
                $provider => array(
                    'enabled' => true,
                    'keys' => array(
                        'id' => qa_opt("{$key}_app_id"),
                        'key' => qa_opt("{$key}_app_id"),
                        'secret' => qa_opt("{$key}_app_secret")
                    ),
                    'scope' => $provider == 'Facebook' ? 'email,user_about_me,user_location,user_website' : null,
                )
            ),
            'debug_mode' => false,
            'debug_file' => ''
        );
    }

    function get_ha_provider($key) {
        $providers = @include QW_CONTROL_DIR . '/inc/hybridauth/providers.php';;
        if ($providers) {
            // loop through all active providers and register them
            $providerList = explode(',', $providers);
            foreach ($providerList as $provider) {
                if (strcasecmp($key, $provider) == 0) {
                    return $provider;
                }
            }
        }
    }
}
