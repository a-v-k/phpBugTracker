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
// $Id: bug.php,v 1.44 2001/09/18 03:43:45 bcurtis Exp $

include 'include.php';

///
/// Show the activity for a bug
function show_history($bugid) {
	global $q, $t, $STRING;
	
	if (!is_numeric($bugid)) {
		show_text($STRING['nobughistory']);
		return;
	}
	
	$q->query('select bh.*, login from '.TBL_BUG_HISTORY.' bh left join '
		.TBL_AUTH_USER.' on bh.created_by = user_id'
		." where bug_id = $bugid");
	if (!$q->num_rows()) {
		show_text($STRING['nobughistory']);
		return;
	}
	
	$t->set_file('content','bughistory.html');
	$t->set_block('content', 'row', 'rows');
	$t->set_var('bugid', $bugid);
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'field' => stripslashes($row['changed_field']),
			'oldvalue' => stripslashes($row['old_value']),
			'newvalue' => stripslashes($row['new_value']),
			'createdby' => stripslashes(maskemail($row['login'])),
			'date' => date(DATEFORMAT.' '.TIMEFORMAT, $row['created_date'])
			));
		$t->parse('rows', 'row', true);
	}
}

///
/// Send the email about changes to the bug and log the changes in the DB
function do_changedfields($userid, $buginfo, $cf, $comments) {
	global $q, $t, $u, $select, $now;
	
	$t->set_file('emailout','bugemail.txt');
	$t->set_block('emailout','commentblock', 'cblock');
	foreach(array('title','url') as $field) {
		if (isset($cf[$field])) {
			$q->query('insert into '.TBL_BUG_HISTORY
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
		$oldvalue = $q->grab_field("select ${field}_name from $table"
			." where ${field}_id = {$buginfo[$field.'_id']}");
		if ($cf[$field.'_id']) {
			$newvalue = $q->grab_field("select ${field}_name from $table"
				." where ${field}_id = {$cf[$field.'_id']}");
			$q->query('insert into '.TBL_BUG_HISTORY
				.' (bug_id, changed_field, old_value, new_value, created_by, created_date)'
				." values ({$buginfo['bug_id']}, '$field', '$oldvalue', '$newvalue', $u, $now)");
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
	$reporter = $q->grab_field('select email from '.TBL_AUTH_USER
		." where user_id = {$buginfo['created_by']}");
	$reporterstat = ' ';
	$assignedto = $q->grab_field('select email from '.TBL_AUTH_USER
  	.' where user_id = '
		.($cf['assigned_to'] ? $cf['assigned_to'] : $buginfo['assigned_to']));
	$assignedtostat = $cf['assigned_to'] ? '!' : ' ';
	
	// If there are new comments grab the comments immediately before the latest
	if ($comments) {
		$q->query('select u.login, c.comment_text, c.created_date'
			.' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER.' u'
			." where bug_id = {$buginfo['bug_id']} and c.created_by = u.user_id"
			.' order by created_date desc limit 2');
		$row = $q->grab();
		$t->set_var(array(
			'newpostedby' => $row['login'],
			'newpostedon' => date(TIMEFORMAT, $row['created_date']).' on '.
				date(DATEFORMAT, $row['created_date']),
			'newcomments' => textwrap('+ '.stripslashes($row['comment_text']),72,"\n+ ")
			));
		// If this comment is the first additional comment after the creation of the
		// bug then we need to grab the bug's description as the previous comment
		if ($q->num_rows() < 2) {
			list($by, $on, $comments) = $q->grab('select u.login, b.created_date, b.description'
      	.' from '.TBL_BUG.' b, '.TBL_AUTH_USER.' u'
      	." where b.created_by = u.user_id and bug_id = {$buginfo['bug_id']}");
			$t->set_var(array(
				'oldpostedby' => $by,
				'oldpostedon' => date(TIMEFORMAT,$on).' on '.date(DATEFORMAT,$on),
				'oldcomments' => textwrap(stripslashes($comments),72)
				));
		} else {
			$row = $q->grab();
			$t->set_var(array(
				'oldpostedby' => $row['login'],
				'oldpostedon' => date(TIMEFORMAT,$row['created_date']).' on '.
					date(DATEFORMAT,$row['created_date']),
				'oldcomments' => textwrap(stripslashes($row['comment_text']),72)
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
	if ($userid != ($cf['assigned_to'] ? $cf['assigned_to'] : $buginfo['assigned_to']))
		$maillist[] = $assignedto;
	// Later add a watcher (such as QA person) check here
	$toemail = delimit_list(', ',$maillist);
		
	$t->set_var(array(
		'bugid' => $buginfo['bug_id'],
		'bugurl' => INSTALLURL."/bug.php?op=show&bugid={$buginfo['bug_id']}",
		'priority' => $select['priority'][($cf['priority'] ? $cf['priority'] : $buginfo['priority'])],
		'priority_stat' => $cf['priority'] ? '!' : ' ',
		'reporter' => $reporter,
		'reporter_stat' => $reporterstat,
		'assignedto' => $assignedto,
		'assignedto_stat' => $assignedtostat
		));
	if ($toemail) {
		mail($toemail,"[Bug {$buginfo['bug_id']}] Changed - ".
			($cf['title'] ? $cf['title'] : $buginfo['title']), $t->parse('main','emailout'),
			sprintf("From: %s\nReply-To: %s\nErrors-To: %s\nContent-Type: text/plain; charset=%s\nContent-Transfer-Encoding: 8bit\n", ADMINEMAIL, ADMINEMAIL,
				ADMINEMAIL, $STRING['lang_charset']));
	}
}

function update_bug($bugid = 0) {
	global $q, $t, $u, $STRING, $perm, $now;
	
	// Pull bug from database to determine changed fields and for user validation
	$buginfo = $q->grab("select * from ".TBL_BUG." where bug_id = $bugid");
	
	if (isset($GLOBALS['HTTP_POST_VARS'])) {
		foreach ($GLOBALS['HTTP_POST_VARS'] as $k => $v) {
			$$k = $v;
			if ($k == 'url') {
				if ($v == 'http://') $v = '';
				elseif ($v and substr($v,0,7) != 'http://') $v = 'http://'.$v;
				$url = $v;
			}
			if (stripslashes($buginfo[$k]) != stripslashes($v)) { 
				$changedfields[$k] = $v; 
			}
		}
	}
	
	if (!($u == $buginfo['assigned_to'] or $u == $buginfo['created_by'] or 
		$perm->have_perm('Manager'))) {
			show_bug($bugid,array('status' => $STRING['bugbadperm']));
			return;
	}
		
	if ($outcome == 'reassign' and 
		(!$assignedto = $q->grab_field("select user_id from ".TBL_AUTH_USER." where login = '$reassignto'"))) {
		show_bug($bugid,array('status' => $STRING['nouser']));
		return;
	}
	
	if ($last_modified_date != $buginfo['last_modified_date']) {
		show_bug($bugid, array('status' => $STRING['datecollision']));
		return;
	}
	
	switch($outcome) {
		case 'unchanged' : break;
		case 'assign' : $assignedto = $u; $statusfield = 'Assigned'; break;
		case 'reassign' : 
			if (!$assignedto = $q->grab_field("select user_id from ".TBL_AUTH_USER." where login = '$reassignto'")) {
				show_bug($bugid,array('status' => $STRING['nouser']));
				return;
			} else {				
				$statusfield = 'Assigned'; 
				$changedfields['assigned_to'] = $assignedto;
				break;
			}
		case 'reassigntocomponent' : 
			$assignedto = $q->grab_field("select owner from ".TBL_COMPONENT." where component_id = $component");
			$statusfield = 'Assigned'; break;
		case 'dupe' : 
			$changeresolution = true;
			if ($dupenum == $bugid) {
				show_bug($bugid,array('status' => $STRING['dupeofself']));
				return;
			} elseif (!$q->grab_field("select bug_id from ".TBL_BUG." where bug_id = $dupenum")) {
				show_bug($bugid,array('status' => $STRING['nobug']));
				return;
			}
			$q->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)"
		                 ." values (".$q->nextid(TBL_COMMENT).", $dupenum, 'Bug #$bugid is a duplicate of this bug', $u, $now)");
			$q->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)"
			         ." values (".$q->nextid(TBL_COMMENT).", $bugid, 'This bug is a duplicate of bug #$dupenum', $u, $now)");
			$statusfield = 'Duplicate'; 
			$resolution_id = $q->grab_field("select resolution_id from ".TBL_RESOLUTION." where resolution_name = 'Duplicate'");
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
	if ($statusfield) {
		$status_id = $q->grab_field("select status_id from ".TBL_STATUS." where status_name = '$statusfield'");
		$changedfields['status_id'] = $status_id; 
	}
	if ($changeresolution) {
		$changedfields['resolution_id'] = $resolution_id;
	}
	if ($comments) {
		$comments = htmlspecialchars($comments);
		$q->query("insert into ".TBL_COMMENT." (comment_id, bug_id, comment_text, created_by, created_date)"
		         ." values (".$q->nextid(TBL_COMMENT).", $bugid, '$comments', $u, $now)");
	}

	$q->query("update ".TBL_BUG." set title = '$title', url = '$url', severity_id = $severity_id, priority = $priority, ".($status_id ? "status_id = $status_id, " : ''). ($changeresolution ? "resolution_id = $resolution_id, " : ''). ($assignedto ? "assigned_to = $assignedto, " : '')." project_id = $project_id, version_id = $version_id, component_id = $component_id, os_id = $os_id, last_modified_by = $u, last_modified_date = $now where bug_id = $bugid");
	
	if ($changedfields or $comments) {
		do_changedfields($u, $buginfo, $changedfields, $comments);
	}
	header("Location: bug.php?op=show&bugid=$bugid");
}

function do_form($bugid = 0) {
	global $q, $me, $title, $u, $another, $STRING;
	
	$pv = $GLOBALS['HTTP_POST_VARS'];
	// Validation
	if (!$pv['title'] = htmlspecialchars(trim($pv['title'])))
		$error = $STRING['givesummary'];
	elseif (!$pv['description'] = htmlspecialchars(trim($pv['description'])))
		$error = $STRING['givedesc'];
	if ($error) { show_form($bugid, $error); return; }
	
	while (list($k,$v) = each($pv)) $$k = $v;
	
	if ($url == 'http://') $url = '';
	$time = time();
	if (!$bugid) {
		$status = $q->grab_field("select status_id from ".TBL_STATUS." where status_name = 'Unconfirmed'");
		$q->query("insert into ".TBL_BUG." (bug_id, title, description, url, severity_id, priority, status_id, created_by, created_date, last_modified_by, last_modified_date, project_id, version_id, component_id, os_id, browser_string) values (".$q->nextid(TBL_BUG).", '$title', '$description', '$url', $severity, $priority, $status, $u, $time, $u, $time, $project, $version, $component, '$os', '{$GLOBALS['HTTP_USER_AGENT']}')");
	} else {
		$q->query("update ".TBL_BUG." set title = '$title', description = '$description', url = '$url', severity_id = '$severity', priority = '$priority', status_id = $status, assigned_to = '$assignedto', project_id = $project, version_id = $version, component_id = $component, os_id = '$os', browser_string = '{$GLOBALS['HTTP_USER_AGENT']}' last_modified_by = $u, last_modified_date = $time where bug_id = '$bugid'");
	}
	if ($another) header("Location: $me?op=add&project=$project");
	else header("Location: query.php");
}	

function show_form($bugid = 0, $error = '') {
	global $q, $me, $t, $project, $TITLE;
	
	if ($GLOBALS['HTTP_POST_VARS']) 
		while (list($k,$v) = each($GLOBALS['HTTP_POST_VARS'])) $$k = $v;
	
	$t->set_file('content','bugform.html');
	$projectname = $q->grab_field("select project_name from ".TBL_PROJECT." where project_id = $project");
	if ($bugid && !$error) {
		$row = $q->grab("select * from ".TBL_BUG." where bug_id = '$bugid'");
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
			'createddate' => date(DATEFORMAT,$row['created_date']),
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
			'title' => stripslashes($title),
			'description' => stripslashes($description),
			'url' => $url ? $url : 'http://',
			'urllabel' => $url ? "<a href='$url'>URL</a>" : 'URL',
			'severity' => build_select('severity',$severity),
			'priority' => build_select('priority',$priority),
			'status' => build_select('status',$status),
			'resolution' => build_select('resolution',$resolution),
			'assignedto' => $assignedto,
			'createdby' => $createdby,
			'createddate' => $createddate,
			'lastmodifieddate' => $lastmodifieddate,
			'project' => $project,
			'projectname' => $projectname,
			'version' => build_select('version',$version,$project),
			'component' => build_select('component',$component,$project),
			'os' => build_select('os',$os)));
	}
}

function show_bug($bugid = 0, $error = '') {
	global $q, $me, $t, $project, $STRING, $u, $perm;
	
	if (!ereg('^[0-9]+$',$bugid) or 
		!$row = $q->grab('select b.*, reporter.login as reporter, owner.login as owner, status_name, resolution_name'
    	.' from '.TBL_BUG.' b left join '.TBL_RESOLUTION.' r using(resolution_id),'
    	.TBL_SEVERITY.' sv, '.TBL_STATUS.' st left join '.TBL_AUTH_USER.' owner on b.assigned_to=owner.user_id'
			.' left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id'
			." where bug_id = '$bugid' and b.severity_id = sv.severity_id and b.status_id = st.status_id")) {
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
	$t->set_var(array(
		'statuserr' => $error['status'] ? $error['status'].'<br><br>' : '',
		'bugid' => $bugid,
		'TITLE' => "{$TITLE['editbug']} #$bugid",
		'title' => stripslashes($row['title']),
		'description' => nl2br(stripslashes($row['description'])),
		'url' => $row['url'],
		'urllabel' => $row['url'] ? "<a href='{$row['url']}'>URL</a>" : 'URL',
		'severity' => build_select('severity',$row['severity_id']),
		'priority' => build_select('priority',$row['priority']),
		'status' => $row['status_name'],
		'resolution' => $row['resolution_name'] ? $row['resolution_name'] : '',
		'owner' => maskemail($row['owner']),
		'reporter' => maskemail($row['reporter']),
		'createddate' => date(DATEFORMAT,$row['created_date']),
		'createdtime' => date(TIMEFORMAT,$row['created_date']),
		'lastmodifieddate' => $row['last_modified_date'],
		'project' => build_select('project',$row['project_id']),
		'projectid' => $row['project_id'],
		'version' => build_select('version',$row['version_id'],$row['project_id']),
		'component' => build_select('component',$row['component_id'],$row['project_id']),
		'os' => build_select('os',$row['os_id']),
		'browserstring' => $row['browser_string'],
		'bugresolution' => build_select('resolution'),
		'submit' => $u == 'nobody' ? $STRING['logintomodify'] : 
			'<input type="submit" value="Submit">'
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
	
	// Show the attachments
	$q->query("select * from ".TBL_ATTACHMENT." where bug_id = $bugid");
	if (!$q->num_rows()) {
		$t->set_var('attrows', '<tr><td colspan="5" align="center">No attachments</td></tr>');
	} else {
		while ($att = $q->grab()) {
			if (is_readable(INSTALLPATH.'/'.ATTACHMENT_PATH."/{$row['project_id']}/$bugid-{$att['file_name']}")) {
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
					'attid' => $att['attachment_id'],
					'attname' => stripslashes($att['file_name']),
					'attdesc' => stripslashes($att['description']),
					'attsize' => $attsize,
					'atttype' => $att['mime_type'],
					'attdate' => date(DATEFORMAT, $att['created_date']),
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
			
	$q->query('select comment_text, c.created_date, login'
		.' from '.TBL_COMMENT.' c, '.TBL_AUTH_USER
		." where bug_id = $bugid and c.created_by = user_id order by c.created_date");
	if (!$q->num_rows()) {
		$t->set_var('rows','');
	} else {
		while ($row = $q->grab()) {
			$t->set_var(array(
				'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
				'rdescription' => eregi_replace('(bug)[[:space:]]*(#?)([0-9]+)',
					"\\1 <a href='$me?op=show&bugid=\\3'>\\2\\3</a>",nl2br($row['comment_text'])),
				'rreporter' => maskemail($row['login']),
				'rcreateddate' => date(TIMEFORMAT,$row['created_date']).' on '.
					date(DATEFORMAT,$row['created_date'])
				));
			$t->parse('rows','row',true);
		}
	}
}

function show_projects() {
	global $me, $q, $t, $project, $STRING;
	
	// Show only active projects with at least one component
	$q->query('select p.* from '.TBL_PROJECT.' p, '.TBL_COMPONENT
		.' c where p.active and p.project_id = c.project_id group by p.project_id 
		order by project_name');
	switch ($q->num_rows()) {
		case 0 :
			$t->set_var('rows',$STRING['noprojects']);
			return;
		case 1 :
			$row = $q->grab();
			$project = $row['project_id'];
			show_form();
			break;
		default :
			$t->set_file('content','projectlist.html');
			$t->set_block('content','row','rows');

			while ($row = $q->grab()) {
			$t->set_var(array(
				'id' => $row['project_id'],
				'name' => $row['project_name'],
				'description' => $row['project_desc'],
				'date' => date(DATEFORMAT,$row['created_date'])
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
			if ($project) show_form(); 
			else show_projects();
			break;
		case 'show' : show_bug($bugid); break;
		case 'update' : update_bug($bugid); break;
		case 'do' : do_form($bugid); break;
	}
} else header("Location: query.php");

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
