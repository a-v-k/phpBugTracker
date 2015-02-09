<?php

// bug.php - All the interactions with a bug
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
// ------------------------------------------------------------------------
// This file is part of phpBugTracker
//
// phpBugTracker is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// phpBugTracker is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with phpBugTracker; if not, write to the Free Software Foundation,
// Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
// ------------------------------------------------------------------------
// $Id: bug.php,v 1.2 2007-02-08 23:25:00 avk Exp $

include 'include.php';

///
/// View the votes for a bug
function vote_view($bug_id) {
    global $u, $db, $t;

    if (isset($_REQUEST['pos']) && is_numeric($_REQUEST['pos'])) {
        $posinfo = "&pos={$_REQUEST['pos']}";
    } else {
        $posinfo = '';
    }
    $t->assign(array('posinfo' => $posinfo));

    $t->assign('votes', $db->getAll('select login, v.created_date ' . 'from ' . TBL_AUTH_USER . ' u, ' . TBL_BUG_VOTE . " v where u.user_id = v.user_id and bug_id = " . $db->quote($bug_id) . " order by v.created_date"));
    $t->render('bugvotes.html', translate("Bug Votes"));
}

///
/// Add a vote to a bug to (possibly) promote it
function vote_bug($bug_id) {
    global $u, $db, $now;

    // Check to see if the user already voted on this bug
    if ($db->getOne("select count(*) from " . TBL_BUG_VOTE . " where bug_id = " . $db->quote($bug_id) . " and user_id = $u")) {
        show_bug($bug_id, array('vote' => translate("You have already voted for this bug")));
        return;
    }
    // Check whether the user has used his allotment of votes (if there is a max)
    if (MAX_USER_VOTES and
            $db->getOne("select count(*) from " . TBL_BUG_VOTE . " where user_id = $u") >= MAX_USER_VOTES) {
        show_bug($bug_id, array('vote' => translate("You have reached the maximum number of votes per user")));
        return;
    }

    // Record the vote
    $db->query("insert into " . TBL_BUG_VOTE . " (user_id, bug_id, created_date) values ($u, " . $db->quote($bug_id) . ", $now)");

    // Proceed only if promoting by votes is turned on
    if (PROMOTE_VOTES) {
        // Has this bug already been promoted?
        $bug_is_new = $db->getOne("select count(*) from " . TBL_BUG . " b, " . TBL_STATUS . " s where bug_id = " . $db->quote($bug_id) . " and b.status_id = s.status_id and status_name = 'New'");

        // If a number of votes are required to promote a bug, check for promotion
        if (!$bug_is_new and $db->getOne("select count(*) from " . TBL_BUG_VOTE . " where bug_id = " . $db->quote($bug_id)) == PROMOTE_VOTES) {
            $status_id = BUG_PROMOTED;
            $buginfo = $db->getOne("select * from " . TBL_BUG . " where bug_id = " . $db->quote($bug_id));
            $changedfields = array('status_id' => $status_id);
            do_changedfields($u, $buginfo, $changedfields);
        }
    }
    if (isset($_REQUEST['pos']) && is_numeric($_REQUEST['pos'])) {
        $posinfo = "&pos={$_REQUEST['pos']}";
    } else {
        $posinfo = '';
    }
    header("Location: bug.php?op=show&bugid=$bug_id$posinfo");
}

///
/// Add (or remove) a bookmark for this bug
function bookmark_bug($bug_id, $add) {
    global $u, $db, $now;

    if ($add) {
        // Check that the user hasn't bookmarked this bug
        if (!$db->getOne("select count(*) from " . TBL_BOOKMARK . " where bug_id = $bug_id and user_id = $u")) {
            // Add bookmark
            $db->query("insert into " . TBL_BOOKMARK . " (user_id, bug_id) values ($u, $bug_id)");
        }
    } else {
        // Check that the user has bookmarked this bug
        if ($db->getOne("select count(*) from " . TBL_BOOKMARK . " where bug_id = $bug_id and user_id = $u")) {
            // Remove bookmark
            $db->query("delete from " . TBL_BOOKMARK . " where user_id=$u and bug_id=$bug_id");
        }
    }
    if (isset($_POST['pos'])) {
        $posinfo = "&pos={$_POST['pos']}";
    } else {
        $posinfo = '';
    }
    header("Location: bug.php?op=show&bugid=$bug_id$posinfo");
}

///
/// Beautify the bug comments
function format_comments($comments) {
    global $me;

    // Set up the regex replacements
    $patterns = array(
        '/\r/',
        '/</',
        '/>/',
        '/\n/',
        '/(bug)[[:space:]]?(#?)([0-9]+)/i', // matches bug #nn
        '/cvs:([^\.\s:,\?!]+(\.[^\.\s:#,\?!]+)*)([:#](rev|r)?)?(\d\.[\d\.]+)?([\W\s])?/i', // matches cvs:filename.php, cvs:filename.php:n.nn or cvs:filename.php#revn.nn
        '/&lt;pre&gt;/', // preformatted text
        '/&lt;\/pre&gt;/', // preformatted text
    );
    $replacements = array(
        '',
        '&lt;',
        '&gt;',
        '<br>',
        "<a href='$me?op=show&bugid=\\3'>\\1 #\\3</a>", // internal link to bug
        '<a href="' . CVS_WEB . '\\1#rev\\5" target="_blank">\\1</a>\\6', // external link to cvs web interface
        '<pre>',
        '</pre>',
    );

    return preg_replace($patterns, $replacements, $comments);
}

///
/// Show the activity for a bug
function show_history($bugid) {
    global $db, $t, $QUERY;

    if (!is_numeric($bugid)) {
        show_text(translate("There is no history for this bug"));
        return;
    }

    if (isset($_REQUEST['pos']) && is_numeric($_REQUEST['pos'])) {
        $posinfo = "&pos={$_REQUEST['pos']}";
    } else {
        $posinfo = '';
    }
    $t->assign(array('posinfo' => $posinfo));

    $t->assign('history', $db->getAll(sprintf($QUERY['bug-history'], $db->quote($bugid))));
    $t->render('bughistory.html', translate("Bug History"));
}

///
/// Send the email about changes to the bug and log the changes in the DB
function do_changedfields($userid, &$buginfo, $cf = array(), $comments = '') {
    global $db, $t, $u, $select, $now, $QUERY;

    // It's a new bug if the changedfields array is empty and there are no comments
    $newbug = (!count($cf) and ! $comments);

    $template_ext = false/* HTML_EMAIL */ ? 'html' : 'txt';
    $template = $newbug ? "bugemail-newbug.$template_ext" : "bugemail.$template_ext";
    foreach (array('title', 'url') as $field) {
        if (isset($cf[$field])) {
            $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values (' . join(', ', array($buginfo['bug_id'], $db->quote(translate($field)), $db->quote($buginfo[$field]), $db->quote($cf[$field]), $u, $now)) . ")");
            $t->assign(array(
                $field => $cf[$field],
                $field . '_stat' => '!'
            ));
        } else {
            $t->assign(array(
                $field => $buginfo[$field],
                $field . '_stat' => ' '
            ));
        }
    }

    // create array with tablenames for following loop
    $cfgDatabase = array(
        'project' => TBL_PROJECT,
        'component' => TBL_COMPONENT,
        'status' => TBL_STATUS,
        'resolution' => TBL_RESOLUTION,
        'database' => TBL_DATABASE,
        'severity' => TBL_SEVERITY,
        'priority' => TBL_PRIORITY,
        'os' => TBL_OS,
        'version' => TBL_VERSION,
        'database' => TBL_DATABASE,
        'site' => TBL_SITE
    );

    foreach ($cfgDatabase as $field => $table) {
        // fake _id for fields like priority
        if (isset($buginfo[$field]) && !isset($buginfo[$field . '_id'])) {
            $buginfo[$field . '_id'] = $buginfo[$field];
        }
        if (isset($cf[$field]) && !isset($cf[$field . '_id'])) {
            $cf[$field . '_id'] = $cf[$field];
        }
        if (isset($buginfo[$field . '_id'])) {
            $oldvalue = $db->getOne("select ${field}_name from $table" . " where ${field}_id = {$buginfo[$field . '_id']}");
        }
        if (empty($oldvalue)) {
            $oldvalue = 'None';
        }

        if (isset($cf[$field . '_id'])) {
            $newvalue = $db->getOne("select ${field}_name from $table where ${field}_id = {$cf[$field . '_id']}");
            if (empty($newvalue)) {
                $newvalue = 'None';
            }

            $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values (' . join(', ', array($buginfo['bug_id'], $db->quote(translate($field)), $db->quote($oldvalue), $db->quote($newvalue), $u, $now)) . ")");
            $t->assign(array(
                $field . '_id' => $newvalue,
                $field . '_id_stat' => '!'
            ));
        } else {
            $t->assign(array(
                $field . '_id' => $oldvalue,
                $field . '_id_stat' => ' '
            ));
        }
    }

    // Handle versions other than version
    $versions = array('to_be_closed_in_version' => 'tobeclosedinversion',
        'closed_in_version' => 'closedinversion');

    foreach ($versions as $field => $field_name) {
        if (isset($buginfo[$field . '_id'])) {
            $oldvalue = $db->getOne('select version_name from ' . $cfgDatabase['version'] . ' where version_id = ' . $buginfo[$field . '_id']);
        }
        if (empty($oldvalue)) {
            $oldvalue = 'None';
        }

        if (isset($cf[$field . '_id'])) {
            $newvalue = $db->getOne('select version_name from ' . $cfgDatabase['version'] . ' where version_id = ' . $cf[$field . '_id']);
            if (empty($newvalue)) {
                $newvalue = 'None';
            }
            $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values (' . join(', ', array($buginfo['bug_id'], $db->quote(translate($field_name)),
                        $db->quote($oldvalue),
                        $db->quote($newvalue), $u, $now)) . ")");
            $t->assign(array(
                $field . '_id' => ($newvalue),
                $field . '_id_stat' => '!'
            ));
        } else {
            $t->assign(array(
                $field . '_id' => ($oldvalue),
                $field . '_id_stat' => ' '
            ));
        }
    }

    // See if the assignment has changed -- grab the email for notifications either way
    list($assignedto, $emailassignedto) = $db->getRow('select email, email_notices from ' . TBL_AUTH_USER . " u, " . TBL_USER_PREF . ' p where u.user_id = ' . (!empty($cf['assigned_to']) ? $cf['assigned_to'] : $buginfo['assigned_to']) . " and u.user_id = p.user_id", null, DB_FETCHMODE_ORDERED);

    if (!empty($cf['assigned_to'])) {
        $assignedtostat = '!';
        $oldassignedto = $db->getOne('select email from ' . TBL_AUTH_USER . ' u where u.user_id = ' . $buginfo['assigned_to']);
        if (is_null($oldassignedto)) {
            $oldassignedto = '';
        }

        $oldassignedto_login = $db->getOne('select login from ' . TBL_AUTH_USER . ' u where u.user_id = ' . $buginfo['assigned_to']);
        if (is_null($oldassignedto_login)) {
            $oldassignedto_login = '';
        }

        $assignedto_login = $db->getOne('select login from ' . TBL_AUTH_USER . ' u where u.user_id = ' . $cf['assigned_to']);
        if (is_null($assignedto_login)) {
            $assignedto_login = '';
        }

        $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values (' . join(', ', array($buginfo['bug_id'], $db->quote(translate("Assigned To")), $db->quote($oldassignedto_login), $db->quote($assignedto_login), $u, $now)) . ")");
    } else {
        $assignedtostat = ' ';
    }

    if (!empty($cf['created_by'])) {
        $reporterstat = '!';
        $oldreporter_login = $db->getOne('select login from ' . TBL_AUTH_USER . ' u where u.user_id = ' . $buginfo['created_by']);
        if (is_null($oldreporter_login)) {
            $oldreporter_login = '';
        }

        $reporter_login = $db->getOne('select login from ' . TBL_AUTH_USER . ' u where u.user_id = ' . $cf['created_by']);
        if (is_null($reporter_login)) {
            $reporter_login = '';
        }

        $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values (' . join(', ', array($buginfo['bug_id'], $db->quote(translate("Reporter")), $db->quote($oldreporter_login), $db->quote($reporter_login), $u, $now)) . ")");
    } else {
        $reporterstat = ' ';
    }

    if (!empty($_POST['suppress_email'])) {
        return; // Don't send email if silent update requested.
    }
    if (defined('EMAIL_DISABLED') and EMAIL_DISABLED) {
        return;
    }
    if (isset($perm) and $perm->have_perm_proj($project_id) and is_numeric($created_by)) {
        $reporter = $db->getOne('select email from ' . TBL_AUTH_USER . " u, " . TBL_USER_PREF . " p where u.user_id = {$buginfo['created_by']} and u.user_id = p.user_id and email_notices = 1");
        $reporterstat = '!';
    } else {
        $reporter = $db->getOne('select email from ' . TBL_AUTH_USER . " u, " . TBL_USER_PREF . " p where u.user_id = {$buginfo['created_by']} and u.user_id = p.user_id and email_notices = 1");
        $reporterstat = ' ';
    }

    // If there are new comments grab the comments immediately before the latest
    if ($comments or $newbug) {
        $TBL_COMMENT = TBL_COMMENT;
        $commentCount = $db->getOne("select count(*) cou from $TBL_COMMENT c where bug_id = {$buginfo['bug_id']}");
        $rs = $db->limitQuery('select u.login, c.comment_text, c.created_date from ' . TBL_COMMENT . ' c, ' . TBL_AUTH_USER . " u where bug_id = {$buginfo['bug_id']} and c.created_by = u.user_id order by created_date desc", 0, 2);
        $rs->fetchInto($row);
        //var_dump($row);
        if (!$newbug) {
            $t->assign(array(
                'newpostedby' => $row['login'],
                'newpostedon' => date(TIME_FORMAT, $row['created_date']) . ' on ' .
                date(DATE_FORMAT, $row['created_date']),
                'newcomments' => $row['comment_text'] // textwrap('+ '.$row['comment_text'],72,"\n+ ")
            ));
        }

        // If this comment is the first additional comment after the creation of the
        // bug then we need to grab the bug's description as the previous comment
        if ($commentCount < 2) {
            list($by, $on, $comments) = $db->getRow('select u.login, b.created_date, b.description from ' . TBL_BUG . ' b, ' . TBL_AUTH_USER . " u where b.created_by = u.user_id and bug_id = {$buginfo['bug_id']}", null, DB_FETCHMODE_ORDERED);
            $t->assign(array(
                'oldpostedby' => $by,
                'oldpostedon' => date(TIME_FORMAT, $on) . ' on ' . date(DATE_FORMAT, $on),
                'oldcomments' => $comments // textwrap($comments,72)
            ));
        } else {
            $rs->fetchInto($row);
            $t->assign(array(
                'oldpostedby' => $row['login'],
                'oldpostedon' => date(TIME_FORMAT, $row['created_date']) . ' on ' .
                date(DATE_FORMAT, $row['created_date']),
                'oldcomments' => $row['comment_text'] // textwrap($row['comment_text'],72)
            ));
        }
        $t->assign('showcomments', true);
    } else {
        $t->assign('showcomments', false);
    }

    $maillist = array();

    // Don't email the person who just made the changes (later, make this
    // behavior toggable by the user)
    $maillist[] = $reporter;
    if ($userid != $buginfo['created_by'] and ! empty($reporter)) {
        $maillist[] = $reporter;
    }
    if ($userid != (!empty($cf['assigned_to']) ? $cf['assigned_to'] : $buginfo['assigned_to']) and ! empty($assignedto) and $emailassignedto) {
        $maillist[] = $assignedto;
    }

    // Collect the CCs
    if ($ccs = $db->getCol(sprintf($QUERY['bug-cc-list'], $buginfo['bug_id']))) {
        $maillist = array_merge($maillist, $ccs);
    }

    // Later add a watcher (such as QA person) check here
    if (count($maillist)) {

        $t->assign(array(
            'bugid' => $buginfo['bug_id'],
            'siteroot' => INSTALL_URL,
            'bugurl' => INSTALL_URL . "/bug.php?op=show&bugid={$buginfo['bug_id']}",
            'reporter' => $reporter,
            'reporter_stat' => $reporterstat,
            'assignedto' => $assignedto,
            'assignedto_stat' => $assignedtostat
        ));

        $maillistStr = implode(';', $maillist);
        mass_mail4($maillistStr, "[Bug {$buginfo['bug_id']}] " . ((!empty($cf['title']) ? $cf['title'] : $buginfo['title'])), $t->fetch($template));

//            require_once('./inc/class.phpmailer-lite.php');
//
//            foreach($maillist as $toitem) {
//                if (trim($toitem) <> '') {
//
//                    $mail = new PHPMailerLite(true); // the true param means it will throw exceptions on errors, which we need to catch
//                    //$mail->IsSendmail(); // telling the class to use SendMail transport
//                    $mail->IsMail(); // telling the class to use mail() transport
//                    $mail->CharSet = 'utf-8';
//                    try {
//                        $mail->SetFrom(RETURN_PATH, 'Digicraft Bug Tracker');
//                        $mail->AddAddress(trim($toitem));
//                        $mail->Subject = "[Bug {$buginfo['bug_id']}] ".
//			//($newbug ? 'New' : 'Changed').' - '.
//				stripslashes((!empty($cf['title']) ? $cf['title'] : $buginfo['title']));
//                        //$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
//                        //$mail->MsgHTML(file_get_contents('contents.html'));
//                        //$mail->AddAttachment('images/phpmailer.gif');      // attachment
//                        //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
//                        $mail->Body = $t->fetch($template);
//                        //$mail->Priority = $priority;
//                        //if ($attach != null) {
//                        //    throw new Exception('Attach not supported');
//                        //}
//                        $mail->Send();
//                        //echo "Message Sent OK</p>\n";
//                    } catch (phpmailerException $e) {
//                        //echo $e->errorMessage(); //Pretty error messages from PHPMailer
//                        $res = false;
//                        error_log($e->errorMessage());
//                    } catch (Exception $e) {
//                        //echo $e->getMessage(); //Boring error messages from anything else!
//                        $res = false;
//                        error_log($e->errorMessage());
//                    }
//                }
//            }
//		require_once('./inc/htmlMimeMail/htmlMimeMail.php');
//		$mail = new htmlMimeMail();
//
//    	$mail->setTextCharset('utf-8');
//    	$mail->setHtmlCharset('utf-8');
//    	$mail->setHeadCharset('utf-8');
//
//		$mail->setText($t->fetch($template));
//		$mail->setFrom(ADMIN_EMAIL);
//		$mail->setReturnPath(RETURN_PATH);
//		$mail->setSubject("[Bug {$buginfo['bug_id']}] ".
//			//($newbug ? 'New' : 'Changed').' - '.
//				stripslashes((!empty($cf['title']) ? $cf['title'] : $buginfo['title'])));
//		if (SMTP_EMAIL) {
//			$mail->setSMTPParams(SMTP_HOST, SMTP_PORT, SMTP_HELO, SMTP_AUTH, SMTP_AUTH_USER, SMTP_AUTH_PASS);
//		}
//		$mail->send($maillist, SMTP_EMAIL ? 'smtp' : 'mail');
    }
}

function update_bug($bugid = 0) {
    global $db, $t, $u, $perm, $now;

    // Pull bug from database to determine changed fields and for user validation
    $buginfo = $db->getRow("select * from " . TBL_BUG . " where bug_id = $bugid");
    $changedfields = array();

    if (isset($_POST)) {
        foreach ($_POST as $k => $v) {
            if ($v == -1 && isset($buginfo[$k])) {
                $v = $buginfo[$k];
            }
            //TODO: fix it! filter input array!
            $$k = $v;
            if ($k == 'url') {
                if (($v == 'http://') || ($v == 'https://')) {
                    $v = '';
                } elseif (($v) && (strtolower(substr($v, 0, 7)) != 'http://') && (strtolower(substr($v, 0, 8)) != 'https://') && (strtolower(substr($v, 0, 6)) != 'ftp://')) {
                    $v = 'http://' . $v;
                }
                $url = $v;
            }
        }
    }

    // Should we allow changes to be made to this bug by this user?
    if (STRICT_UPDATING and ! ($u == $buginfo['assigned_to'] or
            $u == $buginfo['created_by'] or $perm->have_perm_proj($buginfo['project_id']))) {
        if ($perm->have_perm('CommentBug', $buginfo['project_id']) && !empty($comments)) {
            $db->query("insert into " . TBL_COMMENT . " (comment_id, bug_id, comment_text, created_by, created_date) values (" . $db->nextId(TBL_COMMENT) . ", $bugid, " . $db->quote($comments) . ", $u, $now)");
            //do_changedfields($u, $buginfo, $changedfields, $comments);
            return;
        } else {
            return (array('status' => translate("You can not change this bug")));
        }
    }

    // Check for more than one person modifying the bug at the same time
    if ($last_modified_date < $buginfo['last_modified_date']) {
        return (array('status' => translate("Someone has updated this bug since you viewed it. The bug info has been reloaded with the latest changes.")));
    }

    $is_reporter = false;
    $is_assignee = false;
    $is_owner = false;

    if (!empty($u) && $u == $buginfo['created_by']) {
        $perm->add_role('Reporter');
        $is_reporter = true;
    }
    if (!empty($u) && $u == $buginfo['assigned_to']) {
        $perm->add_role('Assignee');
        $is_assignee = true;
    }
    if (!empty($u) && $u == $db->getOne("select owner from " . TBL_COMPONENT . " where owner = " . $u . " and component_id = " . $buginfo['component_id'])) {
        $perm->add_role('Owner');
        $is_owner = true;
    }

    $is_user = (isset($_SESSION['uid']) && !empty($_SESSION['uid']));
    $is_admin = ($is_user && isset($perm) && $perm->have_perm_proj($buginfo['project_id']));
    $may_edit = (isset($perm) && $perm->have_perm('EditBug', $buginfo['project_id']));
    $may_manage = ($may_edit && $perm->have_perm('ManageBug', $buginfo['project_id']));
    $may_change_project = ($may_edit && $perm->have_perm('EditProject', $buginfo['project_id']));
    $may_change_component = ($may_edit && $perm->have_perm('EditComponent', $buginfo['project_id']));
    $may_change_assignment = ($may_edit && $perm->have_perm('EditAssignment', $buginfo['project_id']));
    $may_change_status = ($may_edit && $perm->have_perm('EditStatus', $buginfo['project_id']));
    $may_close = ($may_edit && $perm->have_perm('CloseBug', $buginfo['project_id']));
    $may_change_resolution = ($may_edit && $perm->have_perm('EditResolution', $buginfo['project_id']));
    $may_change_priority = ($may_edit && $perm->have_perm('EditPriority', $buginfo['project_id']));
    $may_change_severity = ($may_edit && $perm->have_perm('EditSeverity', $buginfo['project_id']));
    $may_add_comment = (isset($perm) && $perm->have_perm('CommentBug', $buginfo['project_id']));

    $project_id = isset($project_id) && $may_change_project ? (int) $project_id : $buginfo['project_id'];
    $title = isset($title) && $may_edit ? $title : $buginfo['title'];
    $url = isset($url) && $may_edit ? $url : $buginfo['url'];
    $severity_id = isset($severity_id) && ($may_change_severity or $may_manage) ? (int) $severity_id : $buginfo['severity_id'];
    $priority = isset($priority) && ($may_change_priority or $may_manage) ? (int) $priority : $buginfo['priority'];
    $resolution_id = isset($resolution_id) && ($may_close or $may_change_resolution or $may_manage) ? (int) $resolution_id : $buginfo['resolution_id'];
    $status_id = isset($status_id) && ($may_change_status or $may_manage) ? (int) $status_id : $buginfo['status_id'];
    if (!$may_close && !$may_manage) {
        if (is_closed($status_id)) {
            $status_id = $buginfo['status_id'];
        } else if (is_closed($buginfo['status_id'])) {
            $resolution_id = 0;
            $changedfields['resolution_id'] = 0;
        }
    }
    $database_id = isset($database_id) && $may_edit ? (int) $database_id : $buginfo['database_id'];
    $to_be_closed_in_version_id = isset($to_be_closed_in_version_id) && ($may_close or $may_manage) ? (int) $to_be_closed_in_version_id : $buginfo['to_be_closed_in_version_id'];
    $closed_in_version_id = isset($closed_in_version_id) && ($may_close or $may_manage) ? (int) $closed_in_version_id : $buginfo['closed_in_version_id'];
    $site_id = isset($site_id) && $may_edit ? (int) $site_id : $buginfo['site_id'];
    $assigned_to = isset($assigned_to) && ($may_change_assignment or $may_manage) ? (int) $assigned_to : $buginfo['assigned_to'];
    if (isset($perm) and $perm->have_perm_proj($project_id) and isset($created_by) && is_numeric($created_by)) {
        $created_by = (int) $created_by;
    } else {
        $created_by = $buginfo['created_by'];
    }
    $version_id = isset($version_id) && $may_edit ? (int) $version_id : $buginfo['version_id'];
    $component_id = isset($component_id) && $may_change_component ? (int) $component_id : $buginfo['component_id'];
    $os_id = isset($os_id) && $may_edit ? (int) $os_id : $buginfo['os_id'];
    $comments = isset($comments) && $may_add_comment ? $comments : null;
    $add_cc = isset($add_cc) ? $add_cc : null;
    $remove_cc = isset($remove_cc) ? $remove_cc : null;
    if (isset($remove_cc) && !$is_admin && !$is_owner && !$may_manage) {
        if (in_array($u, $remove_cc)) {
            $remove_cc = array($u);
        } else {
            $remove_cc = null;
        }
    }
    $add_dependency = isset($add_dependency) && ($is_admin or $is_owner or $is_assignee or $may_manage) ? (int) $add_dependency : null;
    $remove_dependency = isset($remove_dependency) && ($is_admin or $is_owner or $is_assignee or $may_manage) ? (int) $remove_dependency : null;
    $add_duplicate = isset($add_duplicate) && ($is_admin or $is_owner or $is_assignee or $may_manage) ? (int) $add_duplicate : null;
    $del_duplicate = isset($del_duplicate) && ($is_admin or $is_owner or $is_assignee or $may_manage) ? (int) $del_duplicate : null;

    if (isset($_POST)) {
        foreach ($_POST as $k => $v) {
            if (isset($buginfo[$k]) && ( $buginfo[$k] != $$k)) {
                $changedfields[$k] = $v;
            }
        }
    }

    // Add CC if specified
    if (isset($add_cc) and $add_cc) {
        $cc_uid = $add_cc;

        // This code allows free entry of a cc email address:
        // $cc_uid = $db->getOne("select user_id from ".TBL_AUTH_USER." where login = ".$db->quote(stripslashes($add_cc)));

        if ($cc_uid != $u and ! $is_admin && !$is_owner && !$may_manage) {
            return (array('status' => translate("You may only add yourself to the CC list")));
        }
        if (!$cc_uid) {
            return (array('status' => translate("That user does not exist")));
        }
        $cc_already = $db->getOne('select user_id from ' . TBL_BUG_CC . " where bug_id = $bugid and user_id = $cc_uid");
        if (!$cc_already && $cc_uid != $buginfo['created_by']) {
            $db->query("insert into " . TBL_BUG_CC . " (bug_id, user_id, created_by, created_date)  values ($bugid, $cc_uid, $u, $now)");
        }
    }

    // Remove CCs if requested
    if (isset($remove_cc) and $remove_cc[0]) {
        $db->query('delete from ' . TBL_BUG_CC . " where bug_id = $bugid and user_id in (" . @join(',', $remove_cc) . ')');
    }


    $old_duplicates = $db->getCol("select b.bug_id from " . TBL_BUG_DEPENDENCY . " d1, " . TBL_BUG_DEPENDENCY . " d2, " . TBL_BUG . " b, " . TBL_STATUS . " s where d1.bug_id = $bugid and d2.bug_id = b.bug_id and d2.bug_id = d1.depends_on and d2.depends_on = d1.bug_id group by b.bug_id");

    $no_dupes = !empty($old_duplicates) ? ' and depends_on <> ' . join(' and depends_on <> ', $old_duplicates) : '';

    // Add dependency if requested
    if (!empty($add_dependency) && $add_dependency != $bugid && !in_array($add_dependency, $old_duplicates)) {
        $add_dependency = preg_replace('/\D/', '', $add_dependency);

        // Validate the bug number
        if (!is_numeric($add_dependency)) {
            return (array('add_dep' => translate("That bug does not exist")));
        }
        if (!$db->getOne('select count(*) from ' . TBL_BUG . " where bug_id = $add_dependency")) {
            return (array('add_dep' => translate("That bug does not exist")));
        }

        // Check if the dependency has already been added
        if ($db->getOne('select count(*) from ' . TBL_BUG_DEPENDENCY . " where bug_id = $bugid and depends_on = $add_dependency")) {
            return (array('add_dep' => translate("That bug dependency has already been added")));
        }

        $old_dependencies = delimit_list(', ', $db->getCol("select depends_on from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid $no_dupes"));
        // Add it
        $db->query("insert into " . TBL_BUG_DEPENDENCY . " (bug_id, depends_on) values($bugid, $add_dependency)");
        $new_dependencies = delimit_list(', ', $db->getCol("select depends_on from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid $no_dupes"));

        $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values(' . join(', ', array($bugid, $db->quote(translate("dependency")), $db->quote($old_dependencies), $db->quote($new_dependencies), $u, $now)) . ")");
    }

    // Remove dependency if requested
    if (!empty($del_dependency) && !in_array($del_dependency, $old_duplicates)) {
        $del_dependency = preg_replace('/\D/', '', $del_dependency);
        if (is_numeric($del_dependency)) {
            // Check if the dependency has already been added
            if ($db->getOne('select count(*) from ' . TBL_BUG_DEPENDENCY . " where bug_id = $bugid and depends_on = $del_dependency")) {
                $old_dependencies = delimit_list(', ', $db->getCol("select depends_on from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid $no_dupes"));
                $db->query("delete from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid and depends_on = $del_dependency");
                $new_dependencies = delimit_list(', ', $db->getCol("select depends_on from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid $no_dupes"));

                $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values(' . join(', ', array($bugid, $db->quote(translate("dependency")), $db->quote($old_dependencies), $db->quote($new_dependencies), $u, $now)) . ")");
            }
        }
    }

    // Add duplicate if requested
    if (!empty($add_duplicate) && $add_duplicate != $bugid) {
        $add_duplicate = preg_replace('/\D/', '', $add_duplicate);

        // Validate the bug number
        if (!is_numeric($add_duplicate)) {
            return (array('add_dep' => translate("That bug does not exist")));
        }
        if (!$db->getOne('select count(*) from ' . TBL_BUG . " where bug_id = $add_duplicate")) {
            return (array('add_dep' => translate("That bug does not exist")));
        }

        // Check if the dependency has already been added
        if ($db->getOne('select count(*) from ' . TBL_BUG_DEPENDENCY . " where bug_id = $bugid and depends_on = $add_duplicate") || $db->getOne('select count(*) from ' . TBL_BUG_DEPENDENCY . " where bug_id = $add_duplicate and depends_on = $bugid") || $bugid == $add_duplicate) {
            return (array('add_dep' => translate("That bug dependency has already been added")));
        }

        $db->query("insert into " . TBL_BUG_DEPENDENCY . " (bug_id, depends_on) values($bugid, $add_duplicate)");
        $db->query("insert into " . TBL_BUG_DEPENDENCY . " (bug_id, depends_on) values($add_duplicate, $bugid)");
        $new_duplicates = $db->getCol("select b.bug_id from " . TBL_BUG_DEPENDENCY . " d1, " . TBL_BUG_DEPENDENCY . " d2, " . TBL_BUG . " b, " . TBL_STATUS . " s where d1.bug_id = $bugid and d2.bug_id = b.bug_id and d2.bug_id = d1.depends_on and d2.depends_on = d1.bug_id group by b.bug_id");
        $old_dupes = delimit_list(', ', $old_duplicates);
        $new_dupes = delimit_list(', ', $new_duplicates);

        $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values(' . join(', ', array($bugid, $db->quote(translate("duplicates")), $db->quote($old_dupes), $db->quote($new_dupes), $u, $now)) . ")");
    }

    // Remove duplicate if requested
    if (!empty($del_duplicate)) {
        $del_duplicate = preg_replace('/\D/', '', $del_duplicate);
        if (is_numeric($del_duplicate)) {
            // Check if the dependency has already been added
            if ($db->getOne('select count(*) from ' . TBL_BUG_DEPENDENCY . " where bug_id = $bugid and depends_on = $del_duplicate")) {
                // both ways
                if ($db->getOne('select count(*) from ' . TBL_BUG_DEPENDENCY . " where bug_id = $del_duplicate and depends_on = $bugid")) {
                    $old_dependencies = delimit_list(', ', $db->getCol("select depends_on from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid"));
                    $db->query("delete from " . TBL_BUG_DEPENDENCY . " where bug_id = $bugid and depends_on = $del_duplicate");
                    $db->query("delete from " . TBL_BUG_DEPENDENCY . " where bug_id = $del_duplicate and depends_on = $bugid");
                    $new_duplicates = $db->getCol("select b.bug_id from " . TBL_BUG_DEPENDENCY . " d1, " . TBL_BUG_DEPENDENCY . " d2, " . TBL_BUG . " b, " . TBL_STATUS . " s where d1.bug_id = $bugid and d2.bug_id = b.bug_id and d2.bug_id = d1.depends_on and d2.depends_on = d1.bug_id group by b.bug_id");
                    $old_dupes = delimit_list(', ', $old_duplicates);
                    $new_dupes = delimit_list(', ', $new_duplicates);

                    $db->query('insert into ' . TBL_BUG_HISTORY . ' (bug_id, changed_field, old_value, new_value, created_by, created_date) values(' . join(', ', array($bugid, $db->quote(translate("duplicates")), $db->quote($old_dupes), $db->quote($new_dupes), $u, $now)) . ")");
                }
            }
        }
    }

    if ($comments) {
        // $comments = strip_tags($comments); -- Uncomment this if you want no <> content in the comments
        $db->query("insert into " . TBL_COMMENT . " (comment_id, bug_id, comment_text, created_by, created_date) values (" . $db->nextId(TBL_COMMENT) . ", $bugid, " . $db->quote($comments) . ", $u, $now)");
    }

    if (is_closed($status_id)) {
        $closed_query = ", close_date = $now";
    } else {
        $closed_query = '';
    }
    $db->query("update " . TBL_BUG . " set title = " . $db->quote($title) . ', url = ' . $db->quote($url) . ", severity_id = " . (int) $severity_id . ", priority = " . (int) $priority . ", status_id = " . (int) $status_id . ", database_id = " . (int) $database_id . ", to_be_closed_in_version_id = " . (int) $to_be_closed_in_version_id . ", closed_in_version_id = " . (int) $closed_in_version_id . ', site_id =' . (int) $site_id . ", resolution_id = " . (int) $resolution_id . ", assigned_to = " . (int) $assigned_to . ", created_by = $created_by, project_id = $project_id, version_id = $version_id, component_id = " . (int) $component_id . ", os_id = " . (int) $os_id . ", last_modified_by = $u, last_modified_date = $now $closed_query where bug_id = $bugid");

    // If the project has changed, move any attachments	
    if (!empty($changedfields['project_id'])) {
        move_attachments($bugid, $buginfo['project_id'], $project_id);
    }

    if (count($changedfields) && !empty($comments)) {
        do_changedfields($u, $buginfo, $changedfields, $comments);
    }
}

function update_bugs($bugs) {
    if (!empty($bugs) && is_array($bugs)) {
        foreach ($bugs as $bug) {
            if (is_numeric($bug)) {
                update_bug((int) $bug);
            }
        }
    }

    header("Location: query.php");
}

function add_attachment($bugid, $description) {
    global $db, $now, $u, $t;

    if (!isset($_FILES['attachment']) ||
            $_FILES['attachment']['tmp_name'] == 'none') {
        return;
    }

    // Check the upload size.  If the size was greater than the max in
    // php.ini, the file won't even be set and will fail at the check above
    if ($_FILES['attachment']['size'] > ATTACHMENT_MAX_SIZE) {
        return;
    }

    $projectid = $db->getOne("select project_id from " . TBL_BUG . " where bug_id = $bugid");
    if (!$projectid) {
        return;
    }

    // Check for a previously-uploaded attachment with the same name, bug, and project
    $rs = $db->query("select a.bug_id, project_id from " . TBL_ATTACHMENT . " a, " . TBL_BUG . " b where file_name = '{$_FILES['attachment']['name']}' and a.bug_id = b.bug_id");
    while ($rs->fetchInto($ainfo)) {
        if ($bugid == $ainfo['bug_id'] && $projectid == $ainfo['project_id']) {
            return;
        }
    }

    $filepath = ATTACHMENT_PATH;
    $tmpfilename = $_FILES['attachment']['tmp_name'];
    $filename = "$bugid-{$_FILES['attachment']['name']}";

    if (!is_dir($filepath)) {
        return;
    }

    if (!is_writeable($filepath)) {
        return;
    }

    if (!is_dir("$filepath/$projectid")) {
        @mkdir("$filepath/$projectid", 0775);
    }

    if (!@move_uploaded_file($_FILES['attachment']['tmp_name'], "$filepath/$projectid/$filename")) {
        return;
    }

    @chmod("$filepath/$projectid/$filename", 0766);
    $db->query("insert into " . TBL_ATTACHMENT . " (attachment_id, bug_id, file_name, description, file_size, mime_type, created_by, created_date) values (" . join(', ', array($db->nextId(TBL_ATTACHMENT), $bugid, $db->quote($_FILES['attachment']['name']), $db->quote($description), $_FILES['attachment']['size'], $db->quote($_FILES['attachment']['type']), $u, $now)) . ")");
}

///
/// Move attachments from one project directory to another
function move_attachments($bug_id, $old_project, $new_project) {
    global $db;

    $filepath = ATTACHMENT_PATH;
    if (!is_dir("$filepath/$new_project")) {
        @mkdir("$filepath/$new_project", 0775);
    }

    $rs = $db->query("select attachment_id, file_name from " . TBL_ATTACHMENT . " where bug_id = $bug_id");
    while ($row = $rs->fetchRow()) {
        @rename("$filepath/$old_project/$bug_id-{$row['file_name']}", "$filepath/$new_project/$bug_id-{$row['file_name']}");
    }
}

function do_form($bugid = 0) {
    global $db, $me, $u, $now;

    $error = '';
    // Validation
    $_POST['title'] = trim($_POST['title']);
    $_POST['description'] = trim($_POST['description']);
    if ($_POST['title'] == '') {
        $error = translate("Please enter a summary");
    } elseif ($_POST['description'] == '') {
        $error = translate("Please enter a description");
    }
    if ($error) {
        $_GET['project'] = $_POST['project'];
        show_form($bugid, $error);
        return;
    }

    extract($_POST);
    if ($url == 'http://') {
        $url = '';
    }

    // Use the selected reporter, if specified
    $reporter = (isset($reporter) and is_numeric($reporter)) ? $reporter : $u;

    // Check to see if this bug's component has an owner and should be assigned
    // If we aren't using voting to promote, then auto-promote to New
    if ($owner = $db->getOne("select owner from " . TBL_COMPONENT . " c where component_id = $component")) {
        //$status = BUG_ASSIGNED;
        $status = PROMOTE_VOTES ? BUG_UNCONFIRMED : BUG_ASSIGNED;
    } else {
        $owner = 0;
        $status = PROMOTE_VOTES ? BUG_UNCONFIRMED : BUG_PROMOTED;
    }

    $bugid = $db->nextId(TBL_BUG);

    $db->query('insert into ' . TBL_BUG . ' (bug_id, title, description, url, severity_id, priority, status_id, assigned_to, created_by, created_date, last_modified_by, last_modified_date, project_id, site_id, database_id, version_id, component_id, os_id, browser_string) values (' . $bugid . ', ' . join(', ', array($db->quote($title), $db->quote($description), $db->quote($url))) . ', ' . (int) $severity . ', ' . (int) $priority . ', ' . (int) $status . ', ' . $owner . ', ' . $reporter . ', ' . $now . ', ' . $u . ', ' . $now . ', ' . $project . ', ' . (int) $site . ', ' . (int) $database . ', ' . (int) $version . ', ' . (int) $component . ', ' . (int) $os . ', ' . $db->quote($_SERVER['HTTP_USER_AGENT']) . ')');
    $buginfo = $db->getRow('select * from ' . TBL_BUG . " where bug_id = $bugid");
    do_changedfields($u, $buginfo);

    if (isset($_POST['at_description'])) {
        add_attachment($bugid, $_POST['at_description']); //attachment (initial)
    }
//	if (isset($another)) {
//		header("Location: $me?op=add&project=$project");
//	} else {
//		header("Location: query.php");
//	}
    //  Go directly to view the bug we just submitted
    header("Location: bug.php?op=show&bugid=$bugid");
}

function show_form($bugId = 0, $error = '') {
    global $db, $t, $u;
    $projectId = check_numeric_die(get_request_value('project'));
    $projectname = $db->getOne(
            "select project_name from " . TBL_PROJECT . " where project_id = :project_id", array(':project_id' => $projectId));
    if ($bugId && !$error) {
        $t->assign($db->getRow("select * from " . TBL_BUG . " where bug_id = :bug_id", array(':bug_id' => $bugId)));
    } else {
        $t->assign($_POST);
        $t->assign(array(
            'u' => $u,
            'error' => $error,
            'project' => $projectId,
            'projectname' => $projectname
        ));
    }
    $t->render('bugform.html.php', translate("Create Bug"));
}

function show_bug_printable($bugid) {
    global $db, $me, $t, $QUERY, $restricted_projects;

    if (!is_numeric($bugid) or ! $row = $db->getRow(sprintf($QUERY['bug-printable'], $bugid, $restricted_projects))) {
        show_text(translate("That bug does not exist, or you don't have permission to view it."), true);
        exit;
    }

    $t->assign($row);

    $bug_duplicates = $db->getAll("select b.bug_id, s.bug_open from " . TBL_BUG_DEPENDENCY . " d1, " . TBL_BUG_DEPENDENCY . " d2, " . TBL_BUG . " b, " . TBL_STATUS . " s where d1.bug_id = $bugid and d2.bug_id = b.bug_id and d2.bug_id = d1.depends_on and d2.depends_on = d1.bug_id and b.status_id = s.status_id");

    $no_dupes = "";
    for ($i = 0, $count = count($bug_duplicates); $i < $count; $i++) {
        $no_dupes .= " and b.bug_id <> " . $bug_duplicates[$i]['bug_id'];
    }

    $bug_dependencies = $db->getAll("select b.bug_id, s.bug_open from " . TBL_BUG_DEPENDENCY . " d, " . TBL_BUG . " b, " . TBL_STATUS . " s where d.bug_id = $bugid and d.depends_on = b.bug_id and b.status_id = s.status_id $no_dupes");

    $bug_blocks = $db->getAll("select b.bug_id, s.bug_open from " . TBL_BUG_DEPENDENCY . " d, " . TBL_BUG . " b, " . TBL_STATUS . " s where d.depends_on = $bugid and d.bug_id = b.bug_id and b.status_id = s.status_id $no_dupes");

    $t->assign(array(
        'bug_dependencies' => $bug_dependencies,
        'bug_blocks' => $bug_blocks,
        'bug_duplicates' => $bug_duplicates
    ));

    // Show the comments
    $t->assign('comments', $db->getAll('select comment_text, c.created_date, login from ' . TBL_COMMENT . ' c, ' . TBL_AUTH_USER . " where bug_id = $bugid and c.created_by = user_id order by c.created_date"));
    $t->render('bugdisplay-printable.html', translate("View Bug"), 'wrap-popup.php');
}

///
/// Grab the links for the previous and next bugs in the list
function prev_next_links($bugid, $pos) {
    global $t, $db;

//    // Create a new db connection because of the limit query affecting later queries
//    $db = DB::Connect($dsn);
//    if (DB::isError($db)) {
//        die($db->message . '<br>' . $db->userinfo);
//    }
//    $db->setOption('optimize', 'portability');
//    $db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_db_error");

    if (!isset($_SESSION['queryinfo']['query']) || !$_SESSION['queryinfo']['query']) {
        #syslog(LOG_DEBUG,"no query in session");
        return array('', '');
    }

    if ($pos) {
        $offset = $pos - 1;
        $limit = 3;
    } else {
        $offset = 1;
        $limit = 1;
    }

    #old code:
    #$rs = $db->limitQuery(sprintf($QUERY['bug-prev-next'],
    #	$_SESSION['queryinfo']['query'], $bugid, $_SESSION['queryinfo']['order'],
    #	$_SESSION['queryinfo']['sort']), $offset, $limit);
    # Use the exact sql from the query for the next/previous calculation
    # This helps prevent any errors creeping in, and is way faster with 
    # the mysql cache.
    #
	# syslog(LOG_DEBUG,"prev-next=".$_SESSION['queryinfo']['full_query_sql']);
    # syslog(LOG_DEBUG,"offset=$offset limit=$limit pos=$pos");
    $rs = $db->limitQuery($_SESSION['queryinfo']['full_query_sql'], $offset, $limit);

    $fRow = $rs->fetchRow(DB_FETCHMODE_ORDERED);
    if ($fRow !== false) {
        //var_dump($fRow);
        $firstid = $fRow[0];
    }
    $rs->fetchRow();  // skip one
    $nRow = $rs->fetchRow(DB_FETCHMODE_ORDERED);
    if ($nRow !== false) {
        //var_dump($nRow);
        $secondid = $nRow[0];
    }

    if ($pos) {
        if (isset($firstid)) {
            $t->assign(array('prevbug' => $firstid, 'prevpos' => $pos - 1));
        }
        if (isset($secondid)) {
            $t->assign(array('nextbug' => $secondid, 'nextpos' => $pos + 1));
        }
    } else {
        if (isset($firstid)) {
            $t->assign(array('nextbug' => $firstid, 'nextpos' => $pos + 1));
        }
    }
}

function show_bug($bugid = 0, $error = array()) {
    global $db, $me, $t, $u, $QUERY, $restricted_projects, $auth, $perm;

    if (!is_numeric($bugid) or ! $row = $db->getRow(sprintf($QUERY['bug-show-bug'], $bugid, $restricted_projects))) {
        show_text(translate("That bug does not exist, or you don't have permission to view it."), true);
        return;
    }

    if (isset($_GET['pos'])) { // Skip intensive select for direct bug_id queries
        prev_next_links($bugid, $_GET['pos']);
    }

    $is_reporter = false;
    $is_assignee = false;
    $is_owner = false;
    $is_admin = false;

    if (!empty($u) && $u == $row['created_by']) {
        $perm->add_role('Reporter');
        $is_reporter = true;
    }
    if (!empty($u) && $u == $row['assigned_to']) {
        $perm->add_role('Assignee');
        $is_assignee = true;
    }
    if (!empty($u) && $u == $db->getOne("select owner from " . TBL_COMPONENT . " where owner = " . $u . " and component_id = " . $row['component_id'])) {
        $perm->add_role('Owner');
        $is_owner = true;
    }
    if (!empty($u) && $u == $db->getOne("select user_id from " . TBL_PROJECT_PERM . " where user_id = " . $u . " and project_id = " . $row['project_id'])) {
        $perm->add_role('Admin');
        $is_admin = true;
    }

    $t->assign($row);
    // Override the database values with posted values if there were errors
    if (count($error)) {
        $t->assign($_POST);
    }

    $t->assign(array(
        'is_reporter' => $is_reporter,
        'is_assignee' => $is_assignee,
        'is_owner' => $is_owner,
        'is_admin' => $is_admin
    ));

    $bug_duplicates = $db->getAll("select b.bug_id, s.bug_open from " . TBL_BUG_DEPENDENCY . " d1, " . TBL_BUG_DEPENDENCY . " d2, " . TBL_BUG . " b, " . TBL_STATUS . " s where d1.bug_id = $bugid and d2.bug_id = b.bug_id and d2.bug_id = d1.depends_on and d2.depends_on = d1.bug_id and b.status_id = s.status_id");

    $no_dupes = "";
    for ($i = 0, $count = count($bug_duplicates); $i < $count; $i++) {
        $no_dupes .= " and b.bug_id <> " . $bug_duplicates[$i]['bug_id'];
    }

    $bug_dependencies = $db->getAll("select b.bug_id, s.bug_open from " . TBL_BUG_DEPENDENCY . " d, " . TBL_BUG . " b, " . TBL_STATUS . " s where d.bug_id = $bugid and d.depends_on = b.bug_id and b.status_id = s.status_id $no_dupes");

    $bug_blocks = $db->getAll("select b.bug_id, s.bug_open from " . TBL_BUG_DEPENDENCY . " d, " . TBL_BUG . " b, " . TBL_STATUS . " s where d.depends_on = $bugid and d.bug_id = b.bug_id and b.status_id = s.status_id $no_dupes");

    $t->assign(array(
        'error' => $error,
        'already_voted' => $db->getOne("select count(*) from " . TBL_BUG_VOTE . " where bug_id = $bugid and user_id = $u"),
        'already_bookmarked' => $db->getOne("select count(*) from " . TBL_BOOKMARK . " where bug_id = $bugid and user_id = $u"),
        'num_votes' => $db->getOne("select count(*) from " . TBL_BUG_VOTE . " where bug_id = $bugid"),
        'bug_dependencies' => $bug_dependencies,
        'bug_blocks' => $bug_blocks,
        'bug_duplicates' => $bug_duplicates,
    ));

    // Show the attachments
    $attachments = array();
    $att = array();
    $rs = $db->query("select * from " . TBL_ATTACHMENT . " where bug_id = $bugid");
    while ($rs->fetchInto($att)) {
        //if (@is_readable(ATTACHMENT_PATH . "/{$row['project_id']}/$bugid-{$att['file_name']}")) {
        $attachments[] = $att;
        //}
    }

    // Show the comments
    $t->assign(array(
        'attachments' => $attachments,
        'comments' => $db->getAll('select comment_text, c.created_date, login' . ' from ' . TBL_COMMENT . ' c, ' . TBL_AUTH_USER . " where bug_id = $bugid and c.created_by = user_id order by c.created_date")
    ));

    if (isset($_REQUEST['pos']) && is_numeric($_REQUEST['pos'])) {
        $posinfo = "&pos={$_REQUEST['pos']}";
    } else {
        $posinfo = '';
    }
    $t->assign(array('posinfo' => $posinfo));

    $t->assign(array('perm' => $perm));
    $t->render('bugdisplay.html.php', "#" . $bugid . " - " . $row['title']);
}

function show_projects() {
    global $db, $t, $perm, $restricted_projects;

    // Show only active projects with at least one component
    if ($perm->have_perm('Admin')) { // Show admins all projects
        $p_query = '';
    } else { // Filter out projects that can't be seen by this user
        $p_query = " and p.project_id not in ($restricted_projects)";
    }
    //$projects = array();
    $projects = $db->getAll('select p.project_id, p.project_name, p.project_desc, p.created_date from ' . TBL_PROJECT . ' p, ' . TBL_COMPONENT . ' c where p.active = 1 and p.project_id = c.project_id' . $p_query . ' group by p.project_id, p.project_name, p.project_desc, p.created_date order by project_name');

    switch (count($projects)) {
        case 0 :
            show_text(translate("No projects found"), true);
            return;
        case 1 :
            $_GET['project'] = $projects[0]['project_id'];
            show_form();
            break;
        default :
            $t->assign('projects', $projects);
            $t->render('projectlist.html', translate("Select Project"));
    }
}

if (!empty($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'history':
            show_history(check_id($_GET['bugid']));
            break;
        case 'add':
            $perm->check('AddBug');
            if (isset($_GET['project'])) {
                show_form();
            } else {
                show_projects();
            }
            break;
        case 'del':
            if (check_action_key_die()) {
                $perm->check_proj();
                delete_bug(check_id($_GET['bugid']));
                header("Location: query.php");
            }
            break;
        case 'show':
            show_bug(check_id($_GET['bugid']));
            break;
        case 'update':
            $error = update_bug(check_id($_POST['bugid']));
            if (!empty($error)) {
                show_bug(check_id($_POST['bugid']), $error);
            } else {
                if (defined('DIGICRAFT_TRACKER')) {
                    header('Location: query.php');
                } else {
                    if (isset($_POST['nextbug'])) {
                        header("Location: bug.php?op=show&bugid=" . $_POST['nextbug'] . "&pos=" . $_POST['nextpos']);
                    } else {
                        header("Location: bug.php?op=show&bugid=" . $_POST['bugid']);
                    }
                }
            }
            break;
        case 'mass_update':
            $bugs = array();
            if (!empty($_POST['bugids'])) {
                $bugs = $_POST['bugids'];
            }
            update_bugs($bugs);
            break;
        case 'do':
            do_form(check_id($_POST['bugid']));
            break;
        case 'print':
            show_bug_printable(check_id($_GET['bugid']));
            break;
        case 'vote':
            if (check_action_key_die()) {
                vote_bug(check_id($_GET['bugid']));
            }
            break;
        case 'viewvotes':
            vote_view(check_id($_GET['bugid']));
            break;
        case 'addbookmark':
            if (check_action_key_die()) {
                bookmark_bug(check_id($_GET['bugid']), true);
            }
            break;
        case 'delbookmark':
            if (check_action_key_die()) {
                bookmark_bug(check_id($_GET['bugid']), false);
            }
            break;
    }
} else {
    header("Location: query.php");
}


//