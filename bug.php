<?php

// bug.php - All the interactions with a bug
// ------------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
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
// $Id: bug.php,v 1.86 2002/03/20 15:59:24 bcurtis Exp $

include 'include.php';

///
/// View the votes for a bug
function vote_view($bug_id) {
	global $u, $db, $t, $STRING;
	
	$t->set_file('content', 'bugvotes.html');
	$t->set_block('content', 'row', 'rows');
	
	$rs = $db->query('select login, v.created_date from '.TBL_AUTH_USER.' u, '.
		TBL_BUG_VOTE." v where u.user_id = v.user_id and bug_id = $bug_id".
		' order by v.created_date');
	if (!$rs->numRows()) {
		$t->set_var('rows', "<tr><td colspan=\"2\" align=\"center\">{$STRING['no_votes']}</td></tr>");
	} else {
		$i = 0;
		while (list($login, $date) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
			$t->set_var(array(
      	'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
				'trclass' => $i % 2 ? '' : 'alt',
				'login' => $login,
				'date' => date(DATE_FORMAT.' '.TIME_FORMAT, $date)
			));
			$t->parse('rows', 'row', true);
		}
	}
	$t->set_var('bugid', $bug_id);
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
			$status_id = $db->getOne("select status_id from ".TBL_STATUS." where status_name = 'New'");
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
  global $db, $t, $STRING;

  if (!is_numeric($bugid)) {
    show_text($STRING['nobughistory']);
    return;
  }

  $rs = $db->query('select bh.*, login from '.TBL_BUG_HISTORY.' bh left join '.
    TBL_AUTH_USER." on bh.created_by = user_id where bug_id = $bugid");
  if (!$rs->numRows()) {
    show_text($STRING['nobughistory']);
    return;
  }

  $t->set_file('content','bughistory.html');
  $t->set_block('content', 'row', 'rows');
  $t->set_var('bugid', $bugid);
  while ($rs->fetchInto($row)) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'trclass' => $i % 2 ? '' : 'alt',
      'field' => stripslashes($row['changed_field']),
      'oldvalue' => stripslashes($row['old_value']),
      'newvalue' => stripslashes($row['new_value']),
      'createdby' => stripslashes(maskemail($row['login'])),
      'date' => date(DATE_FORMAT.' '.TIME_FORMAT, $row['created_date'])
      ));
    $t->parse('rows', 'row', true);
  }
}

///
/// Send the email about changes to the bug and log the changes in the DB
function do_changedfields($userid, &$buginfo, $cf = array(), $comments = '') {
  global $db, $t, $u, $select, $now, $STRING;

	// It's a new bug if the changedfields array is empty and there are no comments
	$newbug = (!count($cf) and !$comments); 
	
  $t->set_file('emailout', ($newbug ? 'bugemail-newbug.txt' : 'bugemail.txt'));
  $t->set_block('emailout','commentblock', 'cblock');
  foreach(array('title','url') as $field) {
    if (isset($cf[$field])) {
      $db->query('insert into '.TBL_BUG_HISTORY
         .' (bug_id, changed_field, old_value, new_value, created_by, created_date)'
         ." values ({$buginfo['bug_id']}, '$field', '"
         .addslashes($buginfo[$field])."', '".addslashes($cf[$field])
         ."', $u, $now)");
      $t->set_var(array(
        $field => $cf[$field],
        $field.'_stat' => '!'
        ));
    } else {
      $t->set_var(array(
        $field => $buginfo[$field],
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
    'severity' => TBL_SEVERITY,
    'os' => TBL_OS,
    'version' => TBL_VERSION
  );

  foreach($cfgDatabase as $field => $table) {
    $oldvalue = $db->getOne("select ${field}_name from $table"
      ." where ${field}_id = {$buginfo[$field.'_id']}");
    if (!empty($cf[$field.'_id'])) {
      $newvalue = $db->getOne("select ${field}_name from $table"
        ." where ${field}_id = {$cf[$field.'_id']}");
      $db->query('insert into '.TBL_BUG_HISTORY
        .' (bug_id, changed_field, old_value, new_value, created_by, created_date)'
        ." values ({$buginfo['bug_id']}, '$field', '".addslashes($oldvalue).
				"', '".addslashes($newvalue)."', $u, $now)");
      $t->set_var(array(
        $field.'_id' => stripslashes($newvalue),
        $field.'_id_stat' => '!'
        ));
    } else {
      $t->set_var(array(
        $field.'_id' => stripslashes($oldvalue),
        $field.'_id_stat' => ' '
        ));
    }
  }

  // Reporter never changes;
  $reporter = $db->getOne('select email from '.TBL_AUTH_USER
    ." where user_id = {$buginfo['created_by']}");
  $reporterstat = ' ';
  $assignedto = $db->getOne('select email from '.TBL_AUTH_USER
    .' where user_id = '
    .(!empty($cf['assigned_to']) ? $cf['assigned_to'] : $buginfo['assigned_to']));
  $assignedtostat = !empty($cf['assigned_to']) ? '!' : ' ';

  // If there are new comments grab the comments immediately before the latest
  if ($comments or $newbug) {
    $rs = $db->limitQuery('select u.login, c.comment_text, c.created_date'
      .' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER.' u'
      ." where bug_id = {$buginfo['bug_id']} and c.created_by = u.user_id"
      .' order by created_date desc', 0, 2);
    $rs->fetchInto($row);
    $t->set_var(array(
      'newpostedby' => $row['login'],
      'newpostedon' => date(TIME_FORMAT, $row['created_date']).' on '.
        date(DATE_FORMAT, $row['created_date']),
      'newcomments' => textwrap('+ '.format_comments($row['comment_text']),72,"\n+ ")
      ));
    // If this comment is the first additional comment after the creation of the
    // bug then we need to grab the bug's description as the previous comment
    if ($rs->numRows() < 2) {
      list($by, $on, $comments) = $db->getRow('select u.login, b.created_date, b.description'
        .' from '.TBL_BUG.' b, '.TBL_AUTH_USER.' u'
        ." where b.created_by = u.user_id and bug_id = {$buginfo['bug_id']}",
				null, DB_FETCHMODE_ORDERED);
      $t->set_var(array(
        'oldpostedby' => $by,
        'oldpostedon' => date(TIME_FORMAT,$on).' on '.date(DATE_FORMAT,$on),
        'oldcomments' => textwrap(format_comments($comments),72)
        ));
    } else {
      $rs->fetchInto($row);
      $t->set_var(array(
        'oldpostedby' => $row['login'],
        'oldpostedon' => date(TIME_FORMAT,$row['created_date']).' on '.
          date(DATE_FORMAT,$row['created_date']),
        'oldcomments' => textwrap(format_comments($row['comment_text']),72)
        ));
    }
    $t->parse('cblock', 'commentblock', true);
  } else {
    $t->set_var('cblock', '');
  }

  // Don't email the person who just made the changes (later, make this
  // behavior toggable by the user)
  if ($userid != $buginfo['created_by'])
    $maillist[] = $reporter;
  if ($userid != (!empty($cf['assigned_to']) ? $cf['assigned_to'] : $buginfo['assigned_to']))
    $maillist[] = $assignedto;

  // Collect the CCs
  if ($ccs = $db->getCol('select email from '.TBL_BUG_CC.' left join '.
		TBL_AUTH_USER." using(user_id) where bug_id = {$buginfo['bug_id']}")) {
		array_push($maillist, $ccs);
	}

  // Later add a watcher (such as QA person) check here
  $toemail = delimit_list(', ',$maillist);

  $t->set_var(array(
    'bugid' => $buginfo['bug_id'],
    'bugurl' => INSTALL_URL."/bug.php?op=show&bugid={$buginfo['bug_id']}",
    'priority' => $select['priority'][(!empty($cf['priority']) ? $cf['priority'] : $buginfo['priority'])],
    'priority_stat' => !empty($cf['priority']) ? '!' : ' ',
    'reporter' => $reporter,
    'reporter_stat' => $reporterstat,
    'assignedto' => $assignedto,
    'assignedto_stat' => $assignedtostat
    ));
  if ($toemail) {
    mail($toemail,"[Bug {$buginfo['bug_id']}] ".($newbug ? 'New' : 'Changed').
			' - '.(!empty($cf['title']) ? $cf['title'] : $buginfo['title']), 
			$t->parse('main','emailout'),
      sprintf("From: %s\nReply-To: %s\nErrors-To: %s\nContent-Type: text/plain; charset=%s\nContent-Transfer-Encoding: 8bit\n", ADMIN_EMAIL, ADMIN_EMAIL,
        ADMIN_EMAIL, $STRING['lang_charset']));
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
        if ($v == 'http://') $v = '';
        elseif ($v and substr($v,0,7) != 'http://') $v = 'http://'.$v;
        $url = $v;
      }
      if (isset($buginfo[$k]) && stripslashes($buginfo[$k]) != stripslashes($v)
        && $k != 'resolution_id') {
        $changedfields[$k] = $v;
      }
    }
  }

  if (STRICT_UPDATING and !($u == $buginfo['assigned_to'] or 
		$u == $buginfo['created_by'] or $perm->have_perm('Manager'))) {
      show_bug($bugid,array('status' => $STRING['bugbadperm']));
      return;
  }

  if ($outcome == 'reassign') {
		if (is_numeric($reassignto)) { // select box
			$assign_user_query = " where user_id = $reassignto";
		} else { // text box
			$assign_user_query = " where login = '$reassignto'";
		}
    if (!$assignedto = $db->getOne("select user_id from ".TBL_AUTH_USER.
			$assign_user_query)) {
    	show_bug($bugid,array('status' => $STRING['nouser']));
    	return;
		}
  }

  if ($last_modified_date != $buginfo['last_modified_date']) {
    show_bug($bugid, array('status' => $STRING['datecollision']));
    return;
  }

  // Add CC if specified
  if ($add_cc) {
    if (!$cc_uid = $db->getOne("select user_id from ".TBL_AUTH_USER.
      " where login = '$add_cc'")) {
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
  if (isset($remove_cc) and count($remove_cc)) {
    $db->query('delete from '.TBL_BUG_CC." where bug_id = $bugid
      and user_id in (".delimit_list(',', $remove_cc).')');
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
			

  $changeresolution = false;
  switch($outcome) {
    case 'unchanged' : break;
    case 'assign' : $assignedto = $u; $statusfield = 'Assigned'; break;
    case 'reassign' :
      if (!$assignedto = $db->getOne("select user_id from ".TBL_AUTH_USER.
				$assign_user_query)) {
        show_bug($bugid,array('status' => $STRING['nouser']));
        return;
      } else {
        $statusfield = 'Assigned';
        $changedfields['assigned_to'] = $assignedto;
        break;
      }
    case 'reassigntocomponent' :
      $assignedto = $db->getOne("select owner from ".TBL_COMPONENT." where component_id = $component_id");
      $statusfield = 'Assigned'; break;
    case 'dupe' :
      $changeresolution = true;
      if ($dupenum == $bugid) {
        show_bug($bugid,array('status' => $STRING['dupeofself']));
        return;
      } elseif (!$db->getOne("select bug_id from ".TBL_BUG." where bug_id = $dupenum")) {
        show_bug($bugid,array('status' => $STRING['nobug']));
        return;
      }
      $db->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)"
      	." values (".$db->nextId(TBL_COMMENT).", $dupenum, 'Bug #$bugid has been marked a duplicate of this bug', $u, $now)");
      $db->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)"
      	." values (".$db->nextId(TBL_COMMENT).", $bugid, 'This bug is a duplicate of bug #$dupenum', $u, $now)");
      $statusfield = 'Duplicate';
      $resolution_id = $db->getOne("select resolution_id from ".TBL_RESOLUTION." where resolution_name = 'Duplicate'");
      $statusfield = 'Resolved';
      break;
    case 'resolve' :
      $changeresolution = true;
      $statusfield = 'Resolved';
      break;
    case 'reopen' :
      $changeresolution = true;
      $statusfield = 'Reopened';
      $resolution_id = 0;
      break;
    case 'verify' :
      $statusfield = 'Verified';
      break;
    case 'close' :
      $statusfield = 'Closed';
      break;
  }
  if (isset($statusfield)) {
    $status_id = $db->getOne("select status_id from ".TBL_STATUS." where status_name = '$statusfield'");
    $changedfields['status_id'] = $status_id;
  }
  if ($changeresolution) {
    $changedfields['resolution_id'] = $resolution_id;
  }
  if ($comments) {
    //$comments = strip_tags($comments); -- Uncomment this if you want no <> content in the comments
    $db->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)"
    	." values (".$db->nextId(TBL_COMMENT).", $bugid, '$comments', $u, $now)");
  }

	// Allow for removing of some items from the bug page
	$priority = $priority ? $priority : 0;
	$os_id = $os_id ? $os_id : 0;
	$severity_id = $severity_id ? $severity_id : 0;
	
  $db->query("update ".TBL_BUG." set title = '$title', url = '$url', severity_id = $severity_id, priority = $priority, ".(isset($status_id) ? "status_id = $status_id, " : ''). ($changeresolution ? "resolution_id = $resolution_id, " : ''). (isset($assignedto) ? "assigned_to = $assignedto, " : '')." project_id = $project_id, version_id = $version_id, component_id = $component_id, os_id = $os_id, last_modified_by = $u, last_modified_date = $now where bug_id = $bugid");

  if (count($changedfields) or !empty($comments)) {
    do_changedfields($u, $buginfo, $changedfields, $comments);
  }
  header("Location: bug.php?op=show&bugid=$bugid&pos=$pos");
}

function do_form($bugid = 0) {
  global $db, $me, $u, $_pv, $STRING, $now, $HTTP_SERVER_VARS;

	$error = '';
  // Validation
  if (!$_pv['title'] = htmlspecialchars(trim($_pv['title'])))
    $error = $STRING['givesummary'];
  elseif (!$_pv['description'] = htmlspecialchars(trim($_pv['description'])))
    $error = $STRING['givedesc'];
  if ($error) { show_form($bugid, $error); return; }

  while (list($k,$v) = each($_pv)) $$k = $v;

  if ($url == 'http://') $url = '';

	// Allow for removing of some items from the bug page
	$priority = $priority ? $priority : 0;
	$os = $os ? $os : 0;
	$severity = $severity ? $severity : 0;
	
  if (!$bugid) {
		$bugid = $db->nextId(TBL_BUG);

		// Check to see if this bug's component has an owner and should be assigned
		if ($owner = $db->getOne("select owner from ".TBL_COMPONENT.
			" c where component_id = $component")) {
			$status = $db->getOne("select status_id from ".TBL_STATUS." where status_name = 'Assigned'");
		} else {
			$owner = 0;
			// If we aren't using voting to promote, then auto-promote to New
			if (PROMOTE_VOTES) {
				$stat_to_assign = 'Unconfirmed';
			} else {
				$stat_to_assign = 'New';
			}
    	$status = $db->getOne("select status_id from ".TBL_STATUS." where status_name = '$stat_to_assign'");
		}
    $db->query("insert into ".TBL_BUG." (bug_id, title, description, url, 
			severity_id, priority, status_id, assigned_to, created_by, created_date, 
			last_modified_by, last_modified_date, project_id, version_id, 
			component_id, os_id, browser_string) values ($bugid, '$title', 
			'$description', '$url', $severity, $priority, $status, $owner, $u, 
			$now, $u, $now, $project, $version, $component, '$os', 
			'{$HTTP_SERVER_VARS['HTTP_USER_AGENT']}')");
		$buginfo = $db->getRow('select * from '.TBL_BUG." where bug_id = $bugid");
		do_changedfields($u, $buginfo);
  } else {
    $db->query("update ".TBL_BUG." set title = '$title', description = '$description', url = '$url', severity_id = '$severity', priority = '$priority', status_id = $status, assigned_to = '$assignedto', project_id = $project, version_id = $version, component_id = $component, os_id = '$os', browser_string = '{$GLOBALS['HTTP_USER_AGENT']}' last_modified_by = $u, last_modified_date = $time where bug_id = '$bugid'");
  }
  if (isset($another)) header("Location: $me?op=add&project=$project");
  else header("Location: query.php");
}

function show_form($bugid = 0, $error = '') {
  global $db, $me, $t, $_gv, $_pv, $TITLE;

	if (isset($_gv['project'])) {
		$project = $_gv['project'];
	}
	
  if (isset($_pv)) {
    foreach ($_pv as $k => $v) $$k = $v;
	}

  $t->set_file('content','bugform.html');
  $projectname = $db->getOne("select project_name from ".TBL_PROJECT." where project_id = $project");
  if ($bugid && !$error) {
    $row = $db->getRow("select * from ".TBL_BUG." where bug_id = '$bugid'");
    $t->set_var(array(
      'bugid' => $bugid,
      'TITLE' => $TITLE['editbug'],
      'title' => stripslashes($row['title']),
      'description' => stripslashes($row['description']),
      'url' => $row['url'],
      'urllabel' => $row['url'] ? "<a href='{$row['url']}'>URL</a>" : 'URL',
      'severity' => build_select('severity',$row['severity_id']),
      'priority' => build_select('priority',$row['priority']),
      'status' => build_select('status',$row['status_id']),
      'resolution' => build_select('resolution',$row['resolution_id']),
      'assignedto' => $row['assigned_to'],
      'createdby' => $row['created_by'],
      'createddate' => date(DATE_FORMAT,$row['created_date']),
      'lastmodifieddate' => $row['last_modified_date'],
      'project' => $row['project_id'],
      'projectname' => $projectname,
      'version' => build_select('version',$row['version_id'],$row['project_id']),
      'component' => build_select('component',$row['component_id'],$row['project_id']),
      'os' => build_select('os',$row['os_id']),
      'browserstring' => $row['browser_string']));
  } else {
    $t->set_var(array(
      'TITLE' => $TITLE['enterbug'],
      'error' => $error,
      'bugid' => $bugid,
      'title' => isset($title) ? stripslashes($title) : '',
      'description' => isset($description) ? stripslashes($description) : '',
      'url' => isset($url) ? $url : 'http://',
      'urllabel' => isset($url) ? "<a href='$url'>URL</a>" : 'URL',
      'severity' => build_select('severity',(isset($severity) ? $severity : 0)),
      'priority' => build_select('priority',(isset($priority) ? $priority : 0)),
      'status' => build_select('status',(isset($status) ? $status : 0)),
      'resolution' => build_select('resolution',
				(isset($resolution) ? $resolution : 0)),
      'assignedto' => isset($assignedto) ? $assignedto : '',
      'createdby' => isset($createdby),
      'createddate' => isset($createddate),
      'lastmodifieddate' => isset($lastmodifieddate),
      'project' => $project,
      'projectname' => $projectname,
      'version' => build_select('version',
				(isset($version) ? $version : 0),$project),
      'component' => build_select('component',
				(isset($component) ? $component : 0),$project),
      'os' => build_select('os',(isset($os) ? $os : 0))));
  }
}

function show_bug_printable($bugid) {
	global $db, $me, $t, $select, $TITLE;
	
	if (!is_numeric($bugid) or
    !$row = $db->getRow('select b.*, reporter.login as reporter, 
			owner.login as owner, project_name, component_name, version_name, 
			severity_name, os_name, status_name, resolution_name  
      from '.TBL_BUG.' b 
			left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id 
      left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id 
			left join '.TBL_RESOLUTION.' r on b.resolution_id = r.resolution_id,'.
			TBL_SEVERITY.' sv, '.TBL_STATUS.' st, '.TBL_OS.' os, '.
			TBL_VERSION.' v, '.TBL_COMPONENT.' c, '.TBL_PROJECT." p 
      where bug_id = '$bugid' and b.severity_id = sv.severity_id 
			and b.os_id = os.os_id and b.version_id = v.version_id 
			and b.component_id = c.component_id and b.project_id = p.project_id 
			and b.status_id = st.status_id")) {
		show_text($STRING['bugbadnum'],true);
		exit;
	}
	
	$t->set_file('content', 'bugdisplay-printable.html');
  $t->set_block('content','row','rows');
  $t->set_var(array(
    'TITLE' => "{$TITLE['editbug']} #$bugid",
    'bugid' => $bugid,
    'title' => stripslashes($row['title']),
    'description' => nl2br(stripslashes($row['description'])),
    'url' => $row['url'] ? "<a href='{$row['url']}'>{$row['url']}</a>" : '',
    'severity' => $row['severity_name'],
    'priority' => $select['priority'][$row['priority']],
    'status' => $row['status_name'],
    'resolution' => !empty($row['resolution_name']) ? $row['resolution_name'] : '',
    'owner' => maskemail($row['owner']),
    'reporter' => maskemail($row['reporter']),
    'createddate' => date(DATE_FORMAT,$row['created_date']),
    'createdtime' => date(TIME_FORMAT,$row['created_date']),
    'lastmodifieddate' => $row['last_modified_date'],
    'project' => $row['project_name'],
    'version' => $row['version_name'],
    'component' => $row['component_name'],
    'os' => $row['os_name'],
    'browserstring' => $row['browser_string'],
		'bug_dependencies' => delimit_list(', ', $db->getCol('select '.
			db_concat("'<a href=\"$me?op=show&bugid='", 'depends_on', '\'">#\'', 
				'depends_on', '\'</a>\'').' from '.TBL_BUG_DEPENDENCY.
				" where bug_id = $bugid"))
    ));

	// Show the comments
  $rs = $db->query('select comment_text, c.created_date, login'
    .' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER
    ." where bug_id = $bugid and c.created_by = user_id order by c.created_date");
  if (!$rs->numRows()) {
    $t->set_var('rows','');
  } else {
    while ($rs->fetchInto($row)) {
      $t->set_var(array(
        'rdescription' => nl2br(format_comments(
					htmlspecialchars($row['comment_text']))),
        'rreporter' => maskemail($row['login']),
        'rcreateddate' => date(TIME_FORMAT,$row['created_date']).' on '.
          date(DATE_FORMAT,$row['created_date'])
        ));
      $t->parse('rows','row',true);
    }
  }
}

///
/// Grab the links for the previous and next bugs in the list
function prev_next_links($bugid, $pos) {
	global $db, $_sv, $STRING;
	
	if (!isset($_sv['queryinfo']['query']) || !$_sv['queryinfo']['query']) {
		return array('', '');
	}
	
	$prevlink = $nextlink = '';
	if ($pos) {
		$offset = $pos - 1;
		$limit = 2;
	} else {
		$offset = $pos;
		$limit = 1;
	}
	$rs = $db->limitQuery('select bug_id, reporter.login as reporter, owner.login as owner 
		from '.TBL_BUG.' b
		left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id
		left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id 
		left join '.TBL_AUTH_USER.' lastmodifier on b.last_modified_by = lastmodifier.user_id 
		left join '.TBL_RESOLUTION.' resolution on b.resolution_id = resolution.resolution_id, '.
		TBL_SEVERITY.' severity, '.TBL_STATUS.' status, '.TBL_OS.' os, '.
		TBL_VERSION.' version, '.TBL_COMPONENT.' component, '.TBL_PROJECT.' project 
		where b.severity_id = severity.severity_id and b.status_id = status.status_id 
		and b.os_id = os.os_id and b.version_id = version.version_id 
		and b.component_id = component.component_id and b.project_id = project.project_id '.
		"and {$_sv['queryinfo']['query']} and bug_id <> $bugid 
		order by {$_sv['queryinfo']['order']} {$_sv['queryinfo']['sort']}, bug_id asc", $offset, $limit);
		
	$firstid = $db->getOne();
	$secondid = $db->getOne();
	
	if ($pos) {
		if ($firstid) {
			$prevlink = "<a href='bug.php?op=show&bugid=$firstid&pos=".($pos - 1).
				'\'>'.$STRING['previous_bug'].'</a>';
		}
		if ($secondid) {
			$nextlink = "<a href='bug.php?op=show&bugid=$secondid&pos=".($pos + 1).
				'\'>'.$STRING['next_bug'].'</a>';
		}
	} else {
		if ($firstid) {
			$nextlink = "<a href='bug.php?op=show&bugid=$firstid&pos=".($pos + 1).
				'\'>'.$STRING['next_bug'].'</a>';
		}
	}
	
	return array($prevlink, $nextlink);
}

function show_bug($bugid = 0, $error = array()) {
  global $db, $me, $t, $STRING, $TITLE, $u, $perm, $_gv;

  if (!ereg('^[0-9]+$',$bugid) or
    !$row = $db->getRow('select b.*, reporter.login as reporter, owner.login as owner, status_name, resolution_name 
      from '.TBL_BUG.' b 
			left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id 
      left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id 
			left join '.TBL_RESOLUTION.' r on b.resolution_id = r.resolution_id,'.
			TBL_SEVERITY.' sv, '.TBL_STATUS." st 
      where bug_id = '$bugid' and b.severity_id = sv.severity_id 
			and b.status_id = st.status_id")) {
    show_text($STRING['bugbadnum'],true);
    return;
  }
  $t->set_file('content','bugdisplay.html');
  $t->set_block('content','row','rows');
  $t->set_block('content','arow','assignrow');
  $t->set_block('content','rrow','resolverow');
  $t->set_block('content','rerow','reopenrow');
  $t->set_block('content','vrow','verifyrow');
  $t->set_block('content','crow','closerow');
  $t->set_block('content','attrow','attrows');
  $t->set_unknowns('remove');
	
	list($prevlink, $nextlink) = prev_next_links($bugid, 
		isset($_gv['pos']) ? $_gv['pos'] : 0);
  $t->set_var(array(
    'statuserr' => isset($error['status']) ? $error['status'].'<br><br>' : '',
		'vote_error' => isset($error['vote']) ? "<div class=\"error\">{$error['vote']}</div>" : '',
    'bugid' => $bugid,
    'TITLE' => "{$TITLE['editbug']} #$bugid",
    'title' => stripslashes($row['title']),
    'description' => nl2br(stripslashes($row['description'])),
    'url' => $row['url'],
    'urllabel' => $row['url'] ? "<a href='{$row['url']}'>URL</a>" : 'URL',
    'severity' => build_select('severity',$row['severity_id']),
    'priority' => build_select('priority',$row['priority']),
    'status' => $row['status_name'],
    'resolution' => !empty($row['resolution_name']) ? $row['resolution_name'] : '',
    'owner' => isset($row['owner']) ? maskemail($row['owner']) : '',
    'reporter' => maskemail($row['reporter']),
    'createddate' => date(DATE_FORMAT,$row['created_date']),
    'createdtime' => date(TIME_FORMAT,$row['created_date']),
    'lastmodifieddate' => $row['last_modified_date'],
    'project' => build_select('project',$row['project_id']),
    'projectid' => $row['project_id'],
    'version' => build_select('version',$row['version_id'],$row['project_id']),
    'component' => build_select('component',$row['component_id'],$row['project_id']),
    'os' => build_select('os',$row['os_id']),
    'browserstring' => $row['browser_string'],
    'bugresolution' => build_select('resolution'),
    'cclist' => build_select('bug_cc', $bugid),
    'submit' => $u == 'nobody' ? $STRING['logintomodify'] :
      '<input type="submit" value="Submit">',
		'developer_list' => build_select('owner'),
		'prevlink' => $prevlink,
		'nextlink' => $nextlink,
		'prevnextsep' => $prevlink && $nextlink ? ' | ' : '',
		'pos' => isset($_gv['pos']) ? $_gv['pos'] : 0,
		'already_voted' => $db->getOne("select count(*) from ".TBL_BUG_VOTE.
			" where bug_id = $bugid and user_id = $u"),
		'already_voted_string' => $STRING['already_voted'],
		'num_votes' => $db->getOne("select count(*) from ".TBL_BUG_VOTE.
			" where bug_id = $bugid"),
		'bug_dependencies' => delimit_list(', ', $db->getCol('select '.
			db_concat("'<a href=\"$me?op=show&bugid='", 'depends_on', '\'">#\'', 
				'depends_on', '\'</a>\'').' from '.TBL_BUG_DEPENDENCY.
				" where bug_id = $bugid")),
		'dependency_error' => isset($error['add_dep']) 
			? '<div class="error">'.$error['add_dep'].'</div>'
			: ''
		));
  switch($row['status_name']) {
    case 'Unconfirmed' :
    case 'New' :
    case 'Reopened' :
      $t->parse('assignrow','arow',true);
      $t->parse('resolverow','rrow',true);
      break;
    case 'Assigned' :
      $t->parse('resolverow','rrow',true);
      break;
    case 'Resolved' :
      $t->parse('reopenrow','rerow',true);
      $t->parse('verifyrow','vrow',true);
      $t->parse('closerow','crow',true);
      break;
    case 'Verified' :
      $t->parse('reopenrow','rerow',true);
      $t->parse('closerow','crow',true);
      break;
    case 'Closed' :
      $t->parse('reopenrow','rerow',true);
      break;
  }
	$t->set_var('js', build_project_js());

  // Show the attachments
  $rs = $db->query("select * from ".TBL_ATTACHMENT." where bug_id = $bugid");
  if (!$rs->numRows()) {
    $t->set_var('attrows', '<tr><td colspan="5" align="center">No attachments</td></tr>');
  } else {
		$j = 0;
    while ($rs->fetchInto($att)) {
      if (@is_readable(INSTALL_PATH.'/'.ATTACHMENT_PATH."/{$row['project_id']}/$bugid-{$att['file_name']}")) {
        $action = "<a href='attachment.php?attachid={$att['attachment_id']}'>View</a>";
        if ($perm->have_perm('Administrator')) {
          $action .= " | <a href='attachment.php?del={$att['attachment_id']}' onClick=\"return confirm('Are you sure you want to delete this attachment?');\">Delete</a>";
        }
        if ($att['FileSize'] > 1024) {
          $attsize = number_format((round($att['file_size']) / 1024 * 100) / 100).'k';
        } else {
          $attsize = number_format($att['file_size']).'b';
        }
        $t->set_var(array(
          'bgcolor' => (++$j % 2 == 0) ? '#dddddd' : '#ffffff',
					'trclass' => $j % 2 ? '' : 'alt',
          'attid' => $att['attachment_id'],
          'attname' => stripslashes($att['file_name']),
          'attdesc' => stripslashes($att['description']),
          'attsize' => $attsize,
          'atttype' => $att['mime_type'],
          'attdate' => date(DATE_FORMAT, $att['created_date']),
          'attaction' => $action
          ));
        $t->parse('attrows', 'attrow', true);
      }
    }
    // If there were attachments in the db but not on disk...
    if (!$j) {
      $t->set_var('attrows', '<tr><td colspan="5" align="center">No attachments</td></tr>');
    }
  }

	// Show the comments
  $rs = $db->query('select comment_text, c.created_date, login'
    .' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER
    ." where bug_id = $bugid and c.created_by = user_id order by c.created_date");
  if (!$rs->numRows()) {
    $t->set_var('rows','');
  } else {
		$i = 1;
    while ($rs->fetchInto($row)) {
      $t->set_var(array(
        'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
				'trclass' => $i % 2 ? '' : 'alt',
        'rdescription' => nl2br(format_comments(
					htmlspecialchars($row['comment_text']))),
        'rreporter' => maskemail($row['login']),
        'rcreateddate' => date(TIME_FORMAT,$row['created_date']).' on '.
          date(DATE_FORMAT,$row['created_date'])
        ));
      $t->parse('rows','row',true);
    }
  }
}

function show_projects() {
  global $me, $db, $t, $STRING, $TITLE, $perm, $auth, $restricted_projects, $_gv;

  // Show only active projects with at least one component
	if ($perm->have_perm('Admin')) { // Show admins all projects
		$p_query = '';
	} else { // Filter out projects that can't be seen by this user
		$p_query = " and p.project_id not in ($restricted_projects)";
	}
	$rs = $db->query('select p.project_id, p.project_name, p.project_desc, p.created_date 
		from '.TBL_PROJECT.' p, '.TBL_COMPONENT.
		' c where p.active = 1 and p.project_id = c.project_id'.$p_query.
		' group by p.project_id, p.project_name, p.project_desc, p.created_date'.
		' order by project_name');
	
  switch ($rs->numRows()) {
    case 0 :
      $t->set_var('content',"<div class=\"error\">{$STRING['noprojects']}</div>");
      return;
    case 1 :
      $rs->fetchInto($row);
      $_gv['project'] = $row['project_id'];
      show_form();
      break;
    default :
      $t->set_file('content','projectlist.html');
      $t->set_block('content','row','rows');

      while ($rs->fetchInto($row)) {
      $t->set_var(array(
        'id' => $row['project_id'],
        'name' => $row['project_name'],
        'description' => $row['project_desc'],
        'date' => date(DATE_FORMAT,$row['created_date'])
        ));
      $t->parse('rows','row',true);
    }
    $t->set_var('TITLE', $TITLE['enterbug']);
  }
}

$t->set_file('wrap','wrap.html');

if ($op) {
  switch($op) {
    case 'history' : show_history($bugid); break;
    case 'add' :
      $perm->check('Editbug');
      if (isset($_gv['project'])) show_form();
      else show_projects();
      break;
    case 'show' : show_bug($_gv['bugid']); break;
    case 'update' : update_bug($_pv['bugid']); break;
    case 'do' : do_form($_pv['bugid']); break;
    case 'print' : show_bug_printable($_gv['bugid']); break;
    case 'vote' : vote_bug($_gv['bugid']); break;
    case 'viewvotes' : vote_view($_gv['bugid']); break;
  }
} else header("Location: query.php");

$t->pparse('main',array('content','wrap','main'));

?>
