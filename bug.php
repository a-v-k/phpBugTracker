<?php

// bug.php - All the interactions with a bug
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
// $Id: bug.php,v 1.130 2003/06/01 18:18:46 kennyt Exp $

include 'include.php';

///
/// View the votes for a bug
function vote_view($bug_id) {
	global $u, $db, $t, $STRING;

	$t->assign('votes', $db->getAll('select login, v.created_date '.
		'from '.TBL_AUTH_USER.' u, '.TBL_BUG_VOTE." v ".
		"where u.user_id = v.user_id and bug_id = $bug_id ".
		'order by v.created_date'));
	$t->wrap('bugvotes.html', 'bugvotes');
}

///
/// Add a vote to a bug to (possibly) promote it
function vote_bug($bug_id) {
	global $u, $db, $now, $_pv, $STRING;

	// Check to see if the user already voted on this bug
	if ($db->getOne("select count(*) from ".TBL_BUG_VOTE.
		" where bug_id = $bug_id and user_id = $u")) {
		show_bug($bug_id, array('vote' => $STRING['already_voted']));
		return;
	}
	// Check whether the user has used his allotment of votes (if there is a max)
	if (MAX_USER_VOTES and $db->getOne("select count(*) from ".TBL_BUG_VOTE.
		" where user_id = $u") >= MAX_USER_VOTES) {
		show_bug($bug_id, array('vote' => $STRING['too_many_votes']));
		return;
	}

	// Record the vote
	$db->query("insert into ".TBL_BUG_VOTE." (user_id, bug_id, created_date)
		values ($u, $bug_id, $now)");

	// Proceed only if promoting by votes is turned on
	if (PROMOTE_VOTES) {
		// Has this bug already been promoted?
		$bug_is_new = $db->getOne("select count(*) from ".TBL_BUG." b, ".
			TBL_STATUS." s where bug_id = $bug_id and b.status_id = s.status_id and
			status_name = 'New'");

		// If a number of votes are required to promote a bug, check for promotion
		if (!$bug_is_new and $db->getOne("select count(*) from ".
			TBL_BUG_VOTE." where bug_id = $bug_id") == PROMOTE_VOTES) {
			$status_id = BUG_PROMOTED;
	  		$buginfo = $db->getOne("select * from ".TBL_BUG." where bug_id = $bug_id");
				$changedfields = array('status_id' => $status_id);
			do_changedfields($u, $buginfo, $changedfields);
		}
	}
	if (isset($_pv['pos'])) {
		$posinfo = "&pos={$_pv['pos']}";
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
		'/(bug)[[:space:]]*(#?)([0-9]+)/i', // matches bug #nn
		'/cvs:([^\.\s:,\?!]+(\.[^\.\s:,\?!]+)*)(:)?(\d\.[\d\.]+)?([\W\s])?/i' // matches cvs:filename.php or cvs:filename.php:n.nn
		);
	$replacements = array(
		"\\1 <a href='$me?op=show&bugid=\\3'>\\2\\3</a>", // internal link to bug
		'<a href="'.CVS_WEB.'\\1#rev\\4" target="_new">\\1</a>\\5' // external link to cvs web interface
		);

	return preg_replace($patterns, $replacements,
		stripslashes($comments));
}

///
/// Show the activity for a bug
function show_history($bugid) {
	global $db, $t, $STRING, $QUERY;

	if (!is_numeric($bugid)) {
		show_text($STRING['nobughistory']);
		return;
	}

	$t->assign('history', $db->getAll(sprintf($QUERY['bug-history'], $bugid)));
	$t->wrap('bughistory.html', 'bughistory');
}

///
/// Send the email about changes to the bug and log the changes in the DB
function do_changedfields($userid, &$buginfo, $cf = array(), $comments = '') {
	global $db, $t, $u, $select, $now, $STRING, $QUERY, $_pv;

	// It's a new bug if the changedfields array is empty and there are no comments
	$newbug = (!count($cf) and !$comments);

	$template_ext = false/*HTML_EMAIL*/ ? 'html' : 'txt';
	$template = $newbug ? "bugemail-newbug.$template_ext" : "bugemail.$template_ext";
	foreach(array('title','url','priority') as $field) {
		if (isset($cf[$field])) {
		    $db->query('insert into '.TBL_BUG_HISTORY.
			' (bug_id, changed_field, old_value, new_value, created_by, created_date)'.
			" values (". join(', ', array($buginfo['bug_id'], $db->quote($field),
			    $db->quote(stripslashes($buginfo[$field])),
			    $db->quote(stripslashes($cf[$field])), $u, $now)).")");
		    $t->assign(array(
				$field => stripslashes($cf[$field]),
				$field.'_stat' => '!'
			    ));
		} else {
		    $t->assign(array(
				$field => stripslashes($buginfo[$field]),
				$field.'_stat' => ' '
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
		'os' => TBL_OS,
		'version' => TBL_VERSION,
		'database' => TBL_DATABASE,
		'site' => TBL_SITE
	);

	foreach($cfgDatabase as $field => $table) {
		if (isset($buginfo[$field.'_id'])) {
			$oldvalue = $db->getOne("select ${field}_name from $table".
			    " where ${field}_id = {$buginfo[$field.'_id']}");
		}
		if (empty($oldvalue)) $oldvalue = 'None';

		if (isset($cf[$field.'_id'])) {
		    $newvalue = $db->getOne("select ${field}_name from $table".
				" where ${field}_id = {$cf[$field.'_id']}");
			if (empty($newvalue)) $newvalue = 'None';

		    $db->query('insert into '.TBL_BUG_HISTORY.
				' (bug_id, changed_field, old_value, new_value, created_by, created_date)'.
			    " values (". join(', ', array($buginfo['bug_id'], $db->quote($STRING['BUGDISPLAY'][$field]),
				$db->quote(stripslashes($oldvalue)),
				$db->quote(stripslashes($newvalue)), $u, $now)).")");
		    $t->assign(array(
				$field.'_id' => stripslashes($newvalue),
				$field.'_id_stat' => '!'
			    ));
		} else {
		    $t->assign(array(
				$field.'_id' => stripslashes($oldvalue),
				$field.'_id_stat' => ' '
			    ));
		}
	}

	// Handle versions other than version
	$versions = array ('to_be_closed_in_version' => 'tobeclosedinversion',
		'closed_in_version' => 'closedinversion');

	foreach($versions as $field => $field_name) {
		if (isset($buginfo[$field.'_id'])) {
		    $oldvalue = $db->getOne('select version_name from '.$cfgDatabase['version'].
			' where version_id = '.$buginfo[$field.'_id']);
		}
		if (empty($oldvalue)) $oldvalue = 'None';

		if (isset($cf[$field.'_id'])) {
		    $newvalue = $db->getOne('select version_name from '.$cfgDatabase['version'].
				' where version_id = '.$cf[$field.'_id']);
			if (empty($newvalue)) $newvalue = 'None';

		    $db->query('insert into '.TBL_BUG_HISTORY.
				' (bug_id, changed_field, old_value, new_value, created_by, created_date)'.
			    " values (". join(', ', array($buginfo['bug_id'], $db->quote($STRING['BUGDISPLAY'][$field_name]),
				$db->quote(stripslashes($oldvalue)),
				$db->quote(stripslashes($newvalue)), $u, $now)).")");
		    $t->assign(array(
				$field.'_id' => stripslashes($newvalue),
				$field.'_id_stat' => '!'
			    ));
		} else {
		    $t->assign(array(
				$field.'_id' => stripslashes($oldvalue),
				$field.'_id_stat' => ' '
			    ));
		}
	}

	// See if the assignment has changed -- grab the email for notifications either way
	list($assignedto, $emailassignedto) = $db->getRow('select email, email_notices from '.
		TBL_AUTH_USER." u, ".TBL_USER_PREF.' p where u.user_id = '.
		(!empty($cf['assigned_to']) ? $cf['assigned_to'] : $buginfo['assigned_to']).
		" and u.user_id = p.user_id", DB_FETCHMODE_ORDERED);

	if (!empty($cf['assigned_to'])) {
		$assignedtostat = '!';
		$oldassignedto = $db->getOne('select email from '.
			TBL_AUTH_USER.' u where u.user_id = '.$buginfo['assigned_to']);
		$db->query('insert into '.TBL_BUG_HISTORY.
			' (bug_id, changed_field, old_value, new_value, created_by, created_date)'.
			" values (". join(', ', array($buginfo['bug_id'],
			$db->quote($STRING['BUGDISPLAY']['assignedto']),
			$db->quote($oldassignedto), $db->quote($assignedto), $u, $now)).")");
	} else {
		$assignedtostat = ' ';
	}

	if (!empty($_pv['suppress_email'])) return; // Don't send email if silent update requested.

	// Reporter never changes
	$reporter = $db->getOne('select email from '.TBL_AUTH_USER.
		" u, ".TBL_USER_PREF." p where u.user_id = {$buginfo['created_by']} ".
		"and u.user_id = p.user_id and email_notices = 1");

	$reporterstat = ' ';

	// If there are new comments grab the comments immediately before the latest
	if ($comments or $newbug) {
		$rs = $db->limitQuery('select u.login, c.comment_text, c.created_date'.
		    ' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER.' u'.
		    " where bug_id = {$buginfo['bug_id']} and c.created_by = u.user_id".
		    ' order by created_date desc', 0, 2);
		$rs->fetchInto($row);
		$t->assign(array(
		    'newpostedby' => $row['login'],
		    'newpostedon' => date(TIME_FORMAT, $row['created_date']).' on '.
			date(DATE_FORMAT, $row['created_date']),
		    'newcomments' => textwrap('+ '.format_comments($row['comment_text']),72,"\n+ ")
		));

		// If this comment is the first additional comment after the creation of the
		// bug then we need to grab the bug's description as the previous comment
		if ($rs->numRows() < 2) {
		    list($by, $on, $comments) = $db->getRow('select u.login, b.created_date, b.description'.
			' from '.TBL_BUG.' b, '.TBL_AUTH_USER.' u'.
			" where b.created_by = u.user_id and bug_id = {$buginfo['bug_id']}",
			null, DB_FETCHMODE_ORDERED);
		    $t->assign(array(
			'oldpostedby' => $by,
			'oldpostedon' => date(TIME_FORMAT,$on).' on '.date(DATE_FORMAT,$on),
			'oldcomments' => textwrap(format_comments($comments),72)
		    ));
		} else {
		    $rs->fetchInto($row);
		    $t->assign(array(
			'oldpostedby' => $row['login'],
			'oldpostedon' => date(TIME_FORMAT,$row['created_date']).' on '.
			    date(DATE_FORMAT,$row['created_date']),
			'oldcomments' => textwrap(format_comments($row['comment_text']),72)
		    ));
		}
		$t->assign('showcomments', true);
	} else {
		$t->assign('showcomments', false);
	}

	$maillist = array();

	$maillist[] = $reporter;

	if (!empty($assignedto) and $emailassignedto) {
	    $maillist[] = $assignedto;
	}

	// Collect the CCs
	if ($ccs = $db->getCol(sprintf($QUERY['bug-cc-list'], $buginfo['bug_id']))) {
		$maillist = array_merge($maillist, $ccs);
	}

	// Later add a watcher (such as QA person) check here
	if (count($maillist)) {
		if ($toemail = delimit_list(', ',$maillist)) {
			$t->assign(array(
				'bugid' => $buginfo['bug_id'],
				'siteroot' => INSTALL_URL,
				'bugurl' => INSTALL_URL."/bug.php?op=show&bugid={$buginfo['bug_id']}",
				'priority' => $select['priority'][(!empty($cf['priority']) ? $cf['priority'] : $buginfo['priority'])],
				'priority_stat' => !empty($cf['priority']) ? '!' : ' ',
				'reporter' => $reporter,
				'reporter_stat' => $reporterstat,
				'assignedto' => $assignedto,
				'assignedto_stat' => $assignedtostat
				));

			qp_mail($toemail,"[Bug {$buginfo['bug_id']}] ".($newbug ? 'New' : 'Changed').' - '.
				stripslashes((!empty($cf['title']) ? $cf['title'] : $buginfo['title'])),
				$t->fetch($template),
				sprintf("From: %s\nReply-To: %s\nErrors-To: %s", ADMIN_EMAIL, ADMIN_EMAIL, ADMIN_EMAIL));
		}
	}
}

function update_bug($bugid = 0) {
	global $db, $t, $u, $STRING, $perm, $now, $_pv;

	// Pull bug from database to determine changed fields and for user validation
	$buginfo = $db->getRow("select * from ".TBL_BUG." where bug_id = $bugid");
	$changedfields = array();

	if (isset($_pv)) {
		foreach ($_pv as $k => $v) {
			$$k = $v;
			if ($k == 'url') {
				if (($v == 'http://') || ($v == 'https://')) {
					$v = '';
				} elseif (($v) && (strtolower(substr($v,0,7)) != 'http://') && (strtolower(substr($v,0,8)) != 'https://') && (strtolower(substr($v,0,6)) != 'ftp://')) {
					$v = 'http://'.$v;
				}
				$url = $v;
			}

			if (isset($buginfo[$k]) && stripslashes($buginfo[$k]) != stripslashes($v)) {
				$changedfields[$k] = $v;
			}
		}
	}

	// Should we allow changes to be made to this bug by this user?
	if (STRICT_UPDATING and !($u == $buginfo['assigned_to'] or
		$u == $buginfo['created_by'] or $perm->have_perm('Manager'))) {
	    show_bug($bugid,array('status' => $STRING['bugbadperm']));
	    return;
	}

	// Check for more than one person modifying the bug at the same time
	if ($last_modified_date != $buginfo['last_modified_date']) {
		show_bug($bugid, array('status' => $STRING['datecollision']));
		return;
	}

	// Add CC if specified
	if ($add_cc) {
		if (!$cc_uid = $db->getOne("select user_id from ".TBL_AUTH_USER.
		    " where login = ".$db->quote(stripslashes($add_cc)))) {
			show_bug($bugid,array('status' => $STRING['nouser']));
			return;
		}
		$cc_already = $db->getOne('select user_id from '.TBL_BUG_CC.
		    " where bug_id = $bugid and user_id = $cc_uid");
		if (!$cc_already && $cc_uid != $buginfo['created_by']) {
		    $db->query("insert into ".TBL_BUG_CC." (bug_id, user_id, created_by,
				created_date)  values ($bugid, $cc_uid, $u, $now)");
		}
	}

	// Remove CCs if requested
	if (isset($remove_cc) and $remove_cc[0]) {
		$db->query('delete from '.TBL_BUG_CC." where bug_id = $bugid and user_id in (".
		    delimit_list(',', $remove_cc).')');
	}

	// Add dependency if requested
	if (!empty($add_dependency)) {
		$add_dependency = preg_replace('/\D/', '', $add_dependency);

		// Validate the bug number
		if (!is_numeric($add_dependency)) {
		    show_bug($bugid, array('add_dep' => $STRING['nobug']));
		    return;
		}
		if (!$db->getOne('select count(*) from '.TBL_BUG." where bug_id = $add_dependency")) {
		    show_bug($bugid, array('add_dep' => $STRING['nobug']));
		    return;
		}

		// Check if the dependency has already been added
		if ($db->getOne('select count(*) from '.TBL_BUG_DEPENDENCY.
		    " where bug_id = $bugid and depends_on = $add_dependency")) {
			show_bug($bugid, array('add_dep' => $STRING['dupe_dependency']));
			return;
		}

		// Add it
		$db->query("insert into ".TBL_BUG_DEPENDENCY.
		    " (bug_id, depends_on) values($bugid, $add_dependency)");
	}

	// Remove dependency if requested
	if (!empty($del_dependency)) {
		$del_dependency = preg_replace('/\D/', '', $del_dependency);
		if (is_numeric($del_dependency)) {
		    $db->query("delete from ".TBL_BUG_DEPENDENCY.
				" where bug_id = $bugid and depends_on = $del_dependency");
		}
	}

	if ($comments) {
		// $comments = strip_tags($comments); -- Uncomment this if you want no <> content in the comments
		$db->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)".
		    " values (".$db->nextId(TBL_COMMENT).", $bugid, ".
		    $db->quote(stripslashes($comments)).", $u, $now)");
	}

	// Allow for removing of some items from the bug page
	$priority = $priority ? $priority : 0;
	$os_id = $os_id ? $os_id : 0;
	$severity_id = $severity_id ? $severity_id : 0;

	if ($status_id == BUG_CLOSED) {
		$closed_query = ", close_date = $now";
	} else {
		$closed_query = '';
	}
	$db->query("update ".TBL_BUG." set title = ".$db->quote(stripslashes($title)).
		', url = '.$db->quote(stripslashes($url)).", severity_id = $severity_id, ".
	    "priority = $priority, status_id = $status_id, ".
	    "database_id = $database_id, to_be_closed_in_version_id = $to_be_closed_in_version_id, ".
	    'closed_in_version_id = '.$closed_in_version_id.', '.
	    'site_id ='.$site_id.', '.
	    "resolution_id = $resolution_id, assigned_to = $assigned_to, ".
	    "project_id = $project_id, version_id = $version_id, ".
	    "component_id = $component_id, os_id = $os_id, last_modified_by = $u, ".
	    "last_modified_date = $now $closed_query where bug_id = $bugid");
	
	// If the project has changed, move any attachments	
	if (!empty($changedfields['project_id'])) {
		move_attachments($bugid, $buginfo['project_id'], $project_id);
	}

	if (count($changedfields) or !empty($comments)) {
		do_changedfields($u, $buginfo, $changedfields, $comments);
	}

	header("Location: bug.php?op=show&bugid=$bugid&pos=$pos");
}

function add_attachment($bugid, $description) {
	global $db, $HTTP_POST_FILES, $now, $u, $STRING, $t, $_pv;

	if (!isset($HTTP_POST_FILES['attachment']) ||
		$HTTP_POST_FILES['attachment']['tmp_name'] == 'none') {
		return;
	}

	// Check the upload size.  If the size was greater than the max in
	// php.ini, the file won't even be set and will fail at the check above
	if ($HTTP_POST_FILES['attachment']['size'] > ATTACHMENT_MAX_SIZE) {
		return;
	}

	$projectid = $db->getOne("select project_id from ".TBL_BUG." where bug_id = $bugid");
	if (!$projectid) {
		show_text($STRING['nobug'], true);
		return;
	}

	// Check for a previously-uploaded attachment with the same name, bug, and project
	$rs = $db->query("select a.bug_id, project_id from ".TBL_ATTACHMENT." a, ".
		TBL_BUG." b where file_name = '{$HTTP_POST_FILES['attachment']['name']}' ".
		"and a.bug_id = b.bug_id");
	while ($rs->fetchInto($ainfo)) {
		if ($bugid == $ainfo['bug_id'] && $projectid == $ainfo['project_id']) {
			return;
		}
	}

	$filepath = ATTACHMENT_PATH;
	$tmpfilename = $HTTP_POST_FILES['attachment']['tmp_name'];
	$filename = "$bugid-{$HTTP_POST_FILES['attachment']['name']}";

	if (!is_dir($filepath)) {
		return;
	}

	if (!is_writeable($filepath)) {
		return;
	}

	if (!is_dir("$filepath/$projectid")) {
		@mkdir("$filepath/$projectid", 0775);
	}

	if (!@move_uploaded_file($HTTP_POST_FILES['attachment']['tmp_name'],
		"$filepath/$projectid/$filename")) {
		return;
	}

	@chmod("$filepath/$projectid/$filename", 0766);
	$db->query("insert into ".TBL_ATTACHMENT." (attachment_id, bug_id, file_name, ".
		"description, file_size, mime_type, created_by, created_date) values (".
		join(', ', array($db->nextId(TBL_ATTACHMENT), $bugid,
			$db->quote($HTTP_POST_FILES['attachment']['name']),
			$db->quote(stripslashes($description)),
			$HTTP_POST_FILES['attachment']['size'],
			$db->quote($HTTP_POST_FILES['attachment']['type']), $u, $now)).")");

	if ($_pv['use_js']) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: bug.php?op=show&bugid=$bugid");
	}
}


///
/// Move attachments from one project directory to another
function move_attachments($bug_id, $old_project, $new_project) {
	global $db;
	
	$filepath = ATTACHMENT_PATH;
	if (!is_dir("$filepath/$new_project")) {
		@mkdir("$filepath/$new_project", 0775);
	}

	$rs = $db->query("select attachment_id, file_name from ".TBL_ATTACHMENT.
		" where bug_id = $bug_id");
	while ($row = $rs->fetchRow()) {
		@rename("$filepath/$old_project/$bug_id-{$row['file_name']}",
			"$filepath/$new_project/$bug_id-{$row['file_name']}");
	}
}
	
	
	

function do_form($bugid = 0) {
	global $db, $me, $u, $_pv, $_gv, $STRING, $now, $HTTP_SERVER_VARS;

	$error = '';
	// Validation
	if (!$_pv['title'] = htmlspecialchars(trim($_pv['title']))) {
		$error = $STRING['givesummary'];
	} elseif (!$_pv['description'] = htmlspecialchars(trim($_pv['description']))) {
		$error = $STRING['givedesc'];
	}
	if ($error) {
		$_gv['project'] = $_pv['project'];
		show_form($bugid, $error);
		return;
	}

	while (list($k,$v) = each($_pv)) {
		$$k = $v;
	}

	if ($url == 'http://') {
		$url = '';
	}

	// Allow for removing of some items from the bug page
	$priority = $priority ? $priority : 0;
	$os = $os ? $os : 0;
	$severity = $severity ? $severity : 0;

	if (!$bugid) {
		$bugid = $db->nextId(TBL_BUG);

		// Check to see if this bug's component has an owner and should be assigned
		if ($owner = $db->getOne("select owner from ".TBL_COMPONENT." c where component_id = $component")) {
		    $status = BUG_ASSIGNED;
		} else {
		    $owner = 0;
			// If we aren't using voting to promote, then auto-promote to New
			$status = PROMOTE_VOTES ? BUG_UNCONFIRMED : BUG_PROMOTED;
		}

		$db->query('insert into '.TBL_BUG.' (bug_id, title, description, url, '.
		    'severity_id, priority, status_id, assigned_to, created_by, created_date, '.
		    'last_modified_by, last_modified_date, project_id, site_id, database_id, version_id, '.
		    'component_id, os_id, browser_string) values ('.$bugid.', '.
		    join(', ', array($db->quote(stripslashes($title)),
		    $db->quote(stripslashes($description)),
		    $db->quote(stripslashes($url)))).', '.
		    $severity.', '.$priority.', '.$status.', '.$owner.', '.$u.', '.$now.', '.$u.', '.$now.', '.$project.', '.
		    $site.', '.$database.', '.$version.', '.$component.', '.$os.', '.
		    $db->quote(stripslashes($HTTP_SERVER_VARS['HTTP_USER_AGENT'])).')');
		$buginfo = $db->getRow('select * from '.TBL_BUG." where bug_id = $bugid");
		do_changedfields($u, $buginfo);
	} else {
		$db->query('update '.TBL_BUG.' set title = '.$db->quote(stripslashes($title)).
		    ", description = ".$db->quote(stripslashes($description)).
		    ", url = ".$db->quote(stripslashes($url)).
		    ", severity_id = '$severity', priority = '$priority', ".
		    "status_id = $status, assigned_to = '$assignedto', ".
		    'database_id = '.$database.', site_id = '.$site.', '.
		    "project_id = $project, version_id = $version, ".
		    "component_id = $component, os_id = '$os', ".
		    "browser_string = '{$GLOBALS['HTTP_USER_AGENT']}' ".
		    "last_modified_by = $u, last_modified_date = $time ".
		    "where bug_id = '$bugid'");
	}

	if (isset($_pv['at_description']))
		add_attachment($bugid, $_pv['at_description']); //attachment (initial)

	if (isset($another)) {
		header("Location: $me?op=add&project=$project");
	} else {
		header("Location: query.php");
	}
}

function show_form($bugid = 0, $error = '') {
	global $db, $me, $t, $_gv, $_pv, $TITLE;

	$projectname = $db->getOne("select project_name from ".TBL_PROJECT." where project_id = {$_gv['project']}");
	if ($bugid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_BUG." where bug_id = '$bugid'"));
	} else {
		$t->assign($_pv);
		$t->assign(array(
			'error' => $error,
			'project' => $_gv['project'],
			'projectname' => $projectname
			));
	}
	$t->wrap('bugform.html', 'enterbug');
}

function show_bug_printable($bugid) {
	global $db, $me, $t, $select, $TITLE, $QUERY, $restricted_projects;

	if (!is_numeric($bugid) or
		!$row = $db->getRow(sprintf($QUERY['bug-printable'], $bugid,
	    $restricted_projects))) {
		show_text($STRING['bugbadnum'],true);
		exit;
	}

	$t->assign($row);
	$t->assign(array(
		'bug_dependencies' => delimit_list(', ', $db->getCol('select '.
			db_concat("'<a href=\"$me?op=show&bugid='", 'depends_on', '\'">#\'',
			'depends_on', '\'</a>\'').' from '.TBL_BUG_DEPENDENCY.
			" where bug_id = $bugid")),
		'rev_bug_dependencies' => delimit_list(', ', $db->getCol('select '.
			db_concat("'<a href=\"$me?op=show&bugid='", 'bug_id', '\'">#\'',
			'bug_id', '\'</a>\'').' from '.TBL_BUG_DEPENDENCY.
			" where depends_on = $bugid"))
		));

	// Show the comments
	$t->assign('comments', $db->getAll('select comment_text, c.created_date, login'.
		' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER.
		" where bug_id = $bugid and c.created_by = user_id order by c.created_date"
		));
	$t->wrap('bugdisplay-printable.html', 'viewbug');
}

///
/// Grab the links for the previous and next bugs in the list
function prev_next_links($bugid, $pos) {
	global $dsn, $_sv, $QUERY, $t;

	// Create a new db connection because of the limit query affecting later queries
	$db = DB::Connect($dsn);
	if (DB::isError($db)) {
		die($db->message.'<br>'.$db->userinfo);
	}
	$db->setOption('optimize', 'portability');
	$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_db_error");

	if (!isset($_sv['queryinfo']['query']) || !$_sv['queryinfo']['query']) {
		return array('', '');
	}

	if ($pos) {
		$offset = $pos - 1;
		$limit = 2;
	} else {
		$offset = 0;
		$limit = 1;
	}
	$rs = $db->limitQuery(sprintf($QUERY['bug-prev-next'],
		$_sv['queryinfo']['query'], $bugid, $_sv['queryinfo']['order'],
		$_sv['queryinfo']['sort']), $offset, $limit);

	list($firstid, $chunks) = $rs->fetchRow();
	list($secondid, $chunks) = $rs->fetchRow();

	if ($pos) {
		if ($firstid) {
			$t->assign(array('prevbug' => $firstid, 'prevpos' => $pos - 1));
		}
		if ($secondid) {
			$t->assign(array('nextbug' => $secondid, 'nextpos' => $pos + 1));
		}
	} else {
		if ($firstid) {
			$t->assign(array('nextbug' => $firstid, 'nextpos' => $pos + 1));
		}
	}
}

function show_bug($bugid = 0, $error = array()) {
	global $db, $me, $t, $STRING, $TITLE, $u, $_gv, $_pv, $QUERY, $restricted_projects;

	if (!ereg('^[0-9]+$',$bugid) or
		!$row = $db->getRow(sprintf($QUERY['bug-show-bug'], $bugid,
	    	$restricted_projects))) {
		show_text($STRING['bugbadnum'],true);
		return;
	}

	prev_next_links($bugid, isset($_gv['pos']) ? $_gv['pos'] : 0);

	$t->assign($row);
	// Override the database values with posted values if there were errors
	if (count($error)) $t->assign($_pv);

	$t->assign(array(
		'error' => $error,
		'already_voted' => $db->getOne("select count(*) from ".TBL_BUG_VOTE.
		    " where bug_id = $bugid and user_id = $u"),
		'num_votes' => $db->getOne("select count(*) from ".TBL_BUG_VOTE.
		    " where bug_id = $bugid"),
		'bug_dependencies' => delimit_list(', ', $db->getCol('select '.
			db_concat("'<a href=\"$me?op=show&bugid='", 'depends_on', '\'">#\'',
			'depends_on', '\'</a>\'').' from '.TBL_BUG_DEPENDENCY.
			" where bug_id = $bugid")),
		'rev_bug_dependencies' => delimit_list(', ', $db->getCol('select '.
			db_concat("'<a href=\"$me?op=show&bugid='", 'bug_id', '\'">#\'',
			'bug_id', '\'</a>\'').' from '.TBL_BUG_DEPENDENCY.
			" where depends_on = $bugid"))
		));

	// Show the attachments
	$attachments = array();
	$rs = $db->query("select * from ".TBL_ATTACHMENT." where bug_id = $bugid");
	if ($rs->numRows()) {
		while ($rs->fetchInto($att)) {
		    if (@is_readable(ATTACHMENT_PATH."/{$row['project_id']}/$bugid-{$att['file_name']}")) {
				$attachments[] = $att;
		    }
		}
	}

	// Show the comments
	$t->assign(array(
		'attachments' => $attachments,
		'comments' => $db->getAll('select comment_text, c.created_date, login'.
		    ' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER.
		    " where bug_id = $bugid and c.created_by = user_id order by c.created_date")
		));

	$t->wrap('bugdisplay.html', 'viewbug');
}

function show_projects() {
	global $db, $t, $STRING, $perm, $restricted_projects, $_gv;

	// Show only active projects with at least one component
	if ($perm->have_perm('Admin')) { // Show admins all projects
		$p_query = '';
	} else { // Filter out projects that can't be seen by this user
		$p_query = " and p.project_id not in ($restricted_projects)";
	}
	$projects = array();
	$projects = $db->getAll('select p.project_id, p.project_name, p.project_desc, p.created_date
		from '.TBL_PROJECT.' p, '.TBL_COMPONENT.
		' c where p.active = 1 and p.project_id = c.project_id'.$p_query.
		' group by p.project_id, p.project_name, p.project_desc, p.created_date'.
		' order by project_name');

	switch (count($projects)) {
		case 0 :
			show_text($STRING['noprojects'], true);
			return;
		case 1 :
			$_gv['project'] = $projects[0]['project_id'];
			show_form();
			break;
		default :
			$t->assign('projects', $projects);
			$t->wrap('projectlist.html', 'enterbug');
	}
}

if ($op) {
	switch($op) {
		case 'history':
			show_history($_gv['bugid']);
			break;
		case 'add':
			$perm->check('Editbug');
			if (isset($_gv['project'])) {
				show_form();
			} else {
				show_projects();
			}
			break;
		case 'show':
			show_bug($_gv['bugid']);
			break;
		case 'update':
			update_bug($_pv['bugid']);
			break;
		case 'do':
			do_form($_pv['bugid']);
			break;
		case 'print':
			show_bug_printable($_gv['bugid']);
			break;
		case 'vote':
			vote_bug($_gv['bugid']);
			break;
		case 'viewvotes':
			vote_view($_gv['bugid']);
			break;
	}
} else {
	header("Location: query.php");
}

?>
